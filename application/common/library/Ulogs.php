<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\common\library;

use think\Model;
use think\Request;

class Ulogs {

    const UUSER_LOGIN = 10;
    const MUSER_LOGIN = 20;

    public static function uUser(Request $request,$uid,$actid){
        $class = new \app\common\model\UserUserLogs();
        return self::save($request,$class,[
            'UUID'=>$uid,
            'LOG_ACTION_ID'=>$actid,
            'LOG_ACTION'=>self::logActions($actid)
        ]);
    }
    public static function mUser(Request $request,$uid,$actid){
        $class = new \app\common\model\UserManagerLogs();
        return self::save($request,$class,[
            'UMID'=>$uid,
            'LOG_ACTION_ID'=>$actid,
            'LOG_ACTION'=>self::logActions($actid)
        ]);
    }

    protected static function save(Request $request,Model $class,$options){

        $data = array_merge([
            'LOG_IP'=>$request->ip(),
            'GPS_LAT'=>$request->header('Gps-lat','0'),
            'GPS_LONG'=>$request->header('Gps-long','0'),
            'DEVICE_SYSTEM'=>$request->header('Device-System',''),
            'APP_VERSION'=>$request->header('App-Version',''),
        ],$options);

        return $class->insert($data);
    }

    protected static function logActions($actid){
        $maps = [
            //康复端
            self::UUSER_LOGIN=>'康复端登录',


            //管理端
            self::MUSER_LOGIN=>'管理端登录'

        ];

        return isset($maps[$actid]) ? $maps[$actid] : '未知操作';
    }
}