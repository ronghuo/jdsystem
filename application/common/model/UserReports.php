<?php

namespace app\common\model;

use think\Model;

class UserReports extends BaseModel
{
    public $NAME;
    protected $pk = 'ID';
    public $table = 'USER_REPORTS';

    public function IMGS(){
        return $this->hasMany('UserReportImgs','UR_ID');
    }
}
