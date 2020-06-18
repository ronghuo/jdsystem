<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\common\library;

use app\common\model\UserManagerLogs;
use app\common\model\UserUserLogs;
use think\Model;
use think\Request;

class AppLogHelper {

    const ACTION_ID_U_LOGIN = 10;
    const ACTION_ID_M_LOGIN = 20;
    const ACTION_ID_M_URINE_QUERY = 201001;
    const ACTION_ID_M_URINE_ADD = 201002;
    const ACTION_ID_M_URINE_EDIT = 201003;
    const ACTION_ID_M_URINE_DELETE_PICTURE = 201004;
    const ACTION_ID_M_AGREEMENT_QUERY = 202001;
    const ACTION_ID_M_AGREEMENT_ADD = 202002;
    const ACTION_ID_UNKNOWN = 90;

    const TARGET_TYPE_USER = "USER";
    const TARGET_TYPE_MANAGER = "MANAGER";

    const ACTION_LIST = [
        10 => '登录',
        20 => '登录',
        201001 => '尿检记录-查询列表',
        201002 => '尿检记录-新增',
        201003 => '尿检记录-修改',
        201004 => '尿检记录-删除尿检图片',
        202001 => '社戒社康协议-查询列表',
        202002 => '社戒社康协议-新增',
        90 => '未知操作'
    ];

    public static function uUser(Request $request, $uid, $actid) {
        $class = new UserUserLogs();
        return self::save($request, $class, [
            'UUID' => $uid,
            'LOG_ACTION_ID' => $actid,
            'LOG_ACTION' => self::ACTION_LIST[$actid]
        ]);
    }

    public static function logManager(Request $request, $actionId, $targetId = "", $actionContent = "", $targetType = self::TARGET_TYPE_USER){
        $class = new UserManagerLogs();
        $manager = $request->User;
        if (!empty($actionContent) && !is_object($actionContent) && !is_array($actionContent)) {
            return false;
        }
        return self::save($request, $class, [
            'UMID' => $manager->ID,
            'UM_NAME' => $manager->NAME,
            'LOG_ACTION_ID' => $actionId,
            'LOG_ACTION' => self::ACTION_LIST[$actionId],
            'LOG_ACTION_CONTENT' => json_encode($actionContent),
            'LOG_ACTION_URL' => $request->url(),
            'TARGET_TYPE' => $targetType,
            'TARGET_ID' => $targetId
        ]);
    }

    protected static function save(Request $request, Model $class, $options) {

        $data = array_merge([
            'LOG_IP'=>$request->ip(),
            'GPS_LAT'=>$request->header('Gps-lat','0'),
            'GPS_LONG'=>$request->header('Gps-long','0'),
            'DEVICE_SYSTEM'=>$request->header('Device-System','android'),
            'APP_VERSION'=>$request->header('App-Version',''),
        ], $options);

        return $class->insert($data);
    }

}