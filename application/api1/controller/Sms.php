<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\api1\controller;


use think\Request;
use app\common\model\UserUsers,
    app\common\model\UserManagers;

class Sms extends Common{




    //登录短信
    public function loginSms(Request $request){

        $ru = $request->routeInfo();
        $client_tag = $ru['option']['client_tag'];

        $mobile = $request->param('mobile','','trim');

        if(!$mobile){
            return $this->fail('手机号有误') ;
        }
        // 检查是否存在该手机用户
        $exist = 0;
        if($client_tag==1){
            $exist = UserUsers::where(['MOBILE'=>$mobile,'STATUS'=>1])->count();
        }elseif($client_tag==2){
            $exist = UserManagers::where(['MOBILE'=>$mobile,'STATUS'=>1])->count();
        }

        if(!$exist){
            return $this->fail('用户不存在') ;
        }
        $cache_key = '';
        if($client_tag == 1){
            $cache_key = config('app.api_keys.ulogin_sms').$mobile;
        }elseif($client_tag==2){
            $cache_key = config('app.api_keys.mlogin_sms').$mobile;
        }


        $sms_code = sms_code();

        cache($cache_key,$sms_code,900);
        return $this->ok('',[
            'code'=>$sms_code
        ]);
    }


    //注册短信
    public function regSms(Request $request){
        $ru = $request->routeInfo();
        $client_tag = $ru['option']['client_tag'];

        $mobile = $request->param('mobile','','trim');

        if(!$mobile){
            return $this->fail('手机号有误') ;
        }
        // 检查是否存在该手机用户
        $exist = 0;
        if($client_tag==1){
            $exist = UserUsers::where(['MOBILE'=>$mobile])->count();//,'STATUS'=>1
        }elseif($client_tag==2){
            $exist = UserManagers::where(['MOBILE'=>$mobile])->count();//,'STATUS'=>1
        }

        if($exist){
            return $this->fail('该号码已存在') ;
        }

        $cache_key = '';
        if($client_tag == 1){
            $cache_key = config('app.api_keys.ureg_sms').$mobile;
        }elseif($client_tag==2){
            $cache_key = config('app.api_keys.mreg_sms').$mobile;
        }


        $sms_code = sms_code();

        cache($cache_key,$sms_code,900);
        return $this->ok('',[
            'code'=>$sms_code
        ]);

    }
}