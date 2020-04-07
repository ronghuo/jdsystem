<?php

namespace app\common\model;

use think\Model;
use Carbon\Carbon;

class SystemMessageRead extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'SYSTEM_MESSAGE_READ';

    public static function saveRead($data){

        $smr = self::where('MSGID',$data['MSGID'])
            ->where('CLIENT_TAG',$data['CLIENT_TAG'])
            ->where('CLIENT_UID',$data['CLIENT_UID'])
            ->find();
        if($smr){
            $smr->ISREAD = 1;
            $smr->READ_TIME = Carbon::now()->toDateTimeString();
            return $smr->save();
        }

        $data['ISREAD'] = 1;
        $data['READ_TIME'] = Carbon::now()->toDateTimeString();
        return (new self())->insert($data);
    }
}
