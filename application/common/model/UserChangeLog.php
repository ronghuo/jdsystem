<?php

namespace app\common\model;

class UserChangeLog extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_CHANGE_LOGS';

    public static function addRow($data){
        return self::create($data);
    }
}
