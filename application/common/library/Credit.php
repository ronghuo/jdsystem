<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/10
 */
namespace app\common\library;

use think\Db;
use app\common\model\UserManagerCreditLogs,
    app\common\model\UserManagers;

class Credit{

    const POST_DRUG_MESSAGE = 1;
    const CANCEL_DRUG_MESSAGE = 2;


    public static function updateManagerCredit($uid,$action_id){


        $action = self::actions($action_id);
        //print_r($action);exit;
        UserManagers::where('ID',$uid)->update(['CREDITS'=>Db::raw("CREDITS+({$action[1]})")]);

        (new UserManagerCreditLogs())->insert([
            'UMID'=>$uid,
            'ACTION_ID'=>$action_id,
            'ACTION'=>$action[0],
            'CHANGE_VALUE'=>$action[1]
        ]);

    }

    protected static function actions($action_id){
        $maps = [
            self::POST_DRUG_MESSAGE=>[
                '提交毒情报告',
                1
            ],
            self::CANCEL_DRUG_MESSAGE=>[
                '撤销毒情报告',
                -1
            ]

        ];

        return isset($maps[$action_id]) ? $maps[$action_id] : ['未知操作',0];
    }
}