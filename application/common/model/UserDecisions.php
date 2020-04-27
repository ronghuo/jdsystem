<?php

namespace app\common\model;

class UserDecisions extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_DECISIONS';

    public function imgs() {
        return $this->hasMany('DecisionImgs','USER_DECISIONS_ID');
    }

}
