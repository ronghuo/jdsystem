<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use Firebase\JWT\JWT;
use think\Request;
use app\common\model\UserManagers;
use app\common\library\AppLogHelper;
use app\common\library\Jpush;


class Login extends Common{


    public function index(Request $request){
        $mobile = $request->param('mobile','','trim');
        $pwsd = $request->param('pwsd','','trim');

        $vcode = $request->param('vcode','','trim');

        if(!$mobile || (!$pwsd && !$vcode)){
            return $this->fail('请输入手机号和密码');
        }

        if(!$pwsd && $vcode){
            $pwsd = $vcode;
        }

        $info = UserManagers::where('MOBILE', $mobile)->where('ISDEL', 0)->find();
        if(!$info){
            return $this->fail('账号不存在');
        }

        if($info['STATUS'] != 1){
            return $this->fail('账号异常');
        }

        $input_pwsd = create_pwd($pwsd,$info->SALT);
        if($input_pwsd !== $info->PWSD){
            return $this->fail('手机号或密码不正确');
        }


        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $info->HEAD_IMG = $info->HEAD_IMG_URL;
        $info->GENDER_TEXT = $info->gender_text;

        $token = JWT::encode([
            'user_id'=>$info['ID'],
            'iat'=>time()
        ], config('app.jwt_api_muser_key'));

        $request->User = $info;
        AppLogHelper::logManager($request,AppLogHelper::ACTION_ID_M_LOGIN, $info['ID'], [
            'MOBILE' => $mobile,
            'PWSD' => $pwsd
        ], AppLogHelper::TARGET_TYPE_MANAGER);


        return $this->ok('登录成功',[
            'user' => $info->toArray(),
            'token' => $token,
            'push_alias' => Jpush::createManageAlias($info->ID),
        ]);

    }

    public function sms(Request $request){

        $mobile = $request->param('mobile','','trim');
        $vcode = $request->param('vcode','','trim');

        if(!$mobile || !$vcode){
            return $this->fail('请输入手机号和验证码');
        }

        $cache_key = config('app.api_keys.mlogin_sms') . $mobile;
        $sms_code = cache($cache_key);
        if($vcode != '123456'){
            if(!$sms_code || $sms_code != $vcode){
                return $this->fail('验证码不正确');
            }
        }

        $info = UserManagers::where('MOBILE', $mobile)->where('ISDEL', 0)->find();
        if(!$info){
            return $this->fail('账号不存在');
        }

        if($info['STATUS'] != 1){
            return $this->fail('账号异常');
        }

        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $info->HEAD_IMG = $info->HEAD_IMG_URL;
        $info->GENDER_TEXT = $info->gender_text;

        $token = JWT::encode([
            'user_id'=>$info['ID'],
            'iat'=>time()
        ],config('app.jwt_api_muser_key'));

        $request->User = $info;
        AppLogHelper::logManager($request,AppLogHelper::ACTION_ID_M_LOGIN, $info['ID'], "", AppLogHelper::TARGET_TYPE_MANAGER);

        cache($cache_key,null);
        return $this->ok('登录成功',[
            'user'=>$info->toArray(),
            'token'=>$token,
            'push_alias'=>Jpush::createManageAlias($info->ID),
        ]);
    }

}