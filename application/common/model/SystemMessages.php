<?php

namespace app\common\model;

use think\Model;
use think\Db;
class SystemMessages extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'SYSTEM_MESSAGES';

    public function getClientTagTextAttr($value,$data){
        $map = [
            0=>'单个用户',
            1=>'康复端',
            2=>'管理端',
            3=>'康复端 & 管理端',
        ];

        return isset($map[$data['CLIENT_TAG']]) ? $map[$data['CLIENT_TAG']] : '-';
    }


    public static function getClientMessage($uid,$client_tag,$client_tags){
        return Db::table('SYSTEM_MESSAGES')
            ->alias('sm')
            ->join(
                'SYSTEM_MESSAGE_READ smr',
                'smr.MSGID=sm.ID and smr.CLIENT_TAG='.$client_tag.' and smr.CLIENT_UID='.$uid,
                'LEFT'
            )->where('sm.CLIENT_TAG','in',$client_tags)
            ->whereOr(function($t) use($uid){
                $t->where('sm.CLIENT_TAG',0);
                $t->where('sm.CLIENT_UID',$uid);
            });
    }


}
