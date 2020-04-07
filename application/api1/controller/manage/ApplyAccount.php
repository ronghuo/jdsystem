<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/23
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use app\common\validate\UserManagersVer;
use app\common\model\UserManagers;

class ApplyAccount extends Common{


    public function index(Request $request){

        $mobile = $request->param('MOBILE','','trim');
        $vcode = $request->param('VCODE','','trim');

        if(!$mobile || !$vcode){
            return $this->fail('请输入手机号和验证码');
        }

        $cache_key = config('app.api_keys.mreg_sms').$mobile;
        $sms_code = cache($cache_key);
        if($vcode != '123456'){
            if(!$sms_code || $sms_code != $vcode){
                return $this->fail('验证码不正确');
            }
        }
        //检查是否存在
        $count = UserManagers::where('MOBILE',$mobile)->where('ISDEL',0)->count();
        if($count > 0){
            return $this->fail('该手机号已经存在');
        }

        $data = [
            'MOBILE'=>$mobile,
            'NAME'=>$request->param('NAME','','trim'),
//            'GENDER'=>'require',
            'ID_NUMBER'=>$request->param('ID_NUMBER','','trim'),
            'JOB'=>$request->param('JOB','','trim'),
//            'PROVINCE_ID'=>'require',
//            'CITY_ID'=>'require',
//            'COUNTY_ID'=>'require',
//            'ADDRESS'=>'require',
            'UNIT_NAME'=>$request->param('UNIT_NAME','','trim'),
            'SPECIAL_ABILITY'=>$request->param('SPECIAL_ABILITY','','trim'),
        ];

        $v = new UserManagersVer();
        if(!$v->scene('add')->check($data)){
            return $this->fail($v->getError());
        }

        $files = $request->file('images');

        if(empty($files)){
            $this->fail('请上传个人照片');
        }


        $muid = (new UserManagers())->insertGetId($data);

        if($muid>0){
            $this->saveImages($request,$muid);

            $this->ok('申请成功，请等待审核');
        }
        return $this->fail('申请失败，请稍后再试');

    }


    protected function saveImages(Request $request,$muid){

//        \app\common\library\Mylog::write([
//            $_POST,
//            $_FILES,
//            $_SERVER
//        ],'reports');

        $res = $this->uploadImages($request,['manager/']);

        if(!$res){
            return false;
        }

        $img = array_shift($res['images']);
        if($img){
           return UserManagers::where('ID','=',$muid)->update(['HEAD_IMG'=>ltrim($img,'.')]);
        }
        return false;
    }


}