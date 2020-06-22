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

    const ACTION_ID_U_LOGIN = 10000000;
    const ACTION_ID_M_LOGIN = 20000000;
    const ACTION_ID_M_URINE_QUERY = 20100101;
    const ACTION_ID_M_URINE_ADD = 20100202;
    const ACTION_ID_M_URINE_EDIT = 20100303;
    const ACTION_ID_M_URINE_DELETE_PICTURE = 20100404;
    const ACTION_ID_M_AGREEMENT_QUERY = 20200101;
    const ACTION_ID_M_AGREEMENT_ADD = 20200202;
    const ACTION_ID_M_DECISION_QUERY = 20300101;
    const ACTION_ID_M_DECISION_ADD = 20300202;
    const ACTION_ID_M_HELP_DIARY_QUERY = 20400101;
    const ACTION_ID_M_HELP_DIARY_ADD = 20400202;
    const ACTION_ID_M_HELP_DIARY_DELETE = 20400304;
    const ACTION_ID_M_RECOVERY_PLAN_QUERY = 20500101;
    const ACTION_ID_M_RECOVERY_PLAN_ADD = 20500202;
    const ACTION_ID_M_SEARCH_BY_AREA = 20600101;
    const ACTION_ID_UNKNOWN = 90000000;

    const TARGET_TYPE_USER = "USER";
    const TARGET_TYPE_MANAGER = "MANAGER";

    const ACTION_LIST = [
        10000000 => '登录',
        20000000 => '登录',
        20100101 => '尿检记录-查询列表',
        20100202 => '尿检记录-新增',
        20100303 => '尿检记录-修改',
        20100404 => '尿检记录-删除尿检图片',
        20200101 => '社戒社康协议-查询列表',
        20200202 => '社戒社康协议-新增',
        20300101 => '决定书-查询列表',
        20300202 => '决定书-新增',
        20400101 => '帮扶日记-查询列表',
        20400202 => '帮扶日记-新增',
        20400304 => '帮扶日记-删除',
        20500101 => '工作计划-查询列表',
        20500202 => '工作计划-新增',
        20600101 => '康复人员-查询',
        90000000 => '未知操作'
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
            'LOG_ACTION_CONTENT' => empty($actionContent) ? "" : json_encode($actionContent),
            'LOG_ACTION_URL' => $request->url(),
            'TARGET_TYPE' => $targetType,
            'TARGET_ID' => $targetId
        ]);
    }

    protected static function save(Request $request, Model $class, $options) {

        $data = array_merge([
            'LOG_IP' => $request->ip(),
            'GPS_LAT' => $request->header('Gps-lat','0'),
            'GPS_LONG' => $request->header('Gps-long','0'),
            'DEVICE_SYSTEM' => $request->header('Device-System','android'),
            'APP_VERSION' => $request->header('App-Version','')
        ], $options);

        return $class->insert($data);
    }

}