<?php

namespace app\common\model;

use think\Model;

class UserApplies extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_APPLIES';


    public function getStatusTextAttr($value,$data)
    {
        //0待审批，1已通过，2未通过
        $status = [0=>'待审批',1=>'已通过',2=>'未通过'];
        return $status[$data['STATUS']];
    }
    public function getStatusSpanAttr($value,$data)
    {
        //0待审批，1已通过，2未通过
        $status = [0=>'<span class="label">待审批</span>',1=>'<span class="label label-success">已通过</span>',2=>'<span class="label label-important">未通过</span>'];
        return $status[$data['STATUS']];
    }

    public function IMGS(){
        return $this->hasMany('UserApplyImgs','UA_ID');
    }

}
