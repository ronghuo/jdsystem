<?php

namespace app\common\model;

class TroubleshootingPerson extends BaseModel
{

    protected $pk = 'ID';

    public $table = 'troubleshoot_person';

    const EXECUTE_STATUS_LIST = [
        'UNHANDLED' => '未排查',
        'HANDLED' => '已排查'
    ];

    const EXECUTE_STATUS_UNHANDLED = 'UNHANDLED';
    const EXECUTE_STATUS_HANDLED = 'HANDLED';

}