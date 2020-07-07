<?php

namespace app\common\model;

class TroubleshootingAssignment extends BaseModel
{

    protected $pk = 'ID';

    public $table = 'troubleshoot_assignment';

    const ACTION_ASSIGN = 'ASSIGN';
    const ACTION_RETURN = 'RETURN';

}