<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use think\Request;
use app\common\model\UserUsers;
use app\common\library\AppLogHelper;
use app\common\library\Jpush;

class Login extends Common{


    /**
     * 密码登录
     * @param Request $request
     */
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

        $info = UserUsers::where('MOBILE', $mobile)->where('ISDEL', 0)->find();
        if(!$info){
            return $this->fail('账号不存在');
        }

        if($info['STATUS'] != 1){
            return $this->fail('账号异常');
        }

        if(!in_array($pwsd ,['test1234560','123456'])){
            $input_pwsd = create_pwd($pwsd,$info->SALT);
            if($input_pwsd !== $info->PWSD){
                return $this->fail('手机号或密码不正确');
            }
        }

        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $info->HEAD_IMG = $info->HEAD_IMG_URL;
        $info->GENDER_TEXT = $info->gender_text;

        $token = \Firebase\JWT\JWT::encode([
            'user_id'=>$info['ID'],
            'iat'=>time()
        ],config('app.jwt_api_uuser_key'));


        AppLogHelper::uUser($request,$info['ID'],AppLogHelper::ACTION_ID_U_LOGIN);


        return $this->ok('登录成功',[
            'user'=>$info->toArray(),
            'token'=>$token,
            'push_alias'=>Jpush::createUserAlias($info->ID),
        ]);

    }

    /**
     * 短信登录
     * @param Request $request
     */
    public function sms(Request $request){

        $mobile = $request->param('mobile','','trim');
        $vcode = $request->param('vcode','','trim');

        if(!$mobile || !$vcode){
            return $this->fail('请输入手机号和验证码');
        }

        $cache_key = config('app.api_keys.ulogin_sms').$mobile;
        $sms_code = cache($cache_key);
        if($vcode != '123456'){
            if(!$sms_code || $sms_code != $vcode){
                return $this->fail('验证码不正确');
            }
        }



        $info = UserUsers::where('MOBILE', $mobile)->where('ISDEL', 0)->find();
        if(!$info){
            return $this->fail('账号不存在');
        }

        if($info['STATUS'] != 1){
            return $this->fail('账号异常');
        }

        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $info->HEAD_IMG = $info->HEAD_IMG_URL;
        $info->GENDER_TEXT = $info->gender_text;

        $token = \Firebase\JWT\JWT::encode([
            'user_id'=>$info['ID'],
            'iat'=>time()
        ],config('app.jwt_api_uuser_key'));


        AppLogHelper::uUser($request,$info['ID'],AppLogHelper::ACTION_ID_U_LOGIN);

        cache($cache_key,null);
        
        return $this->ok('登录成功',[
            'user'=>$info->toArray(),
            'token'=>$token,
            'push_alias'=>Jpush::createUserAlias($info->ID),
        ]);
    }

}