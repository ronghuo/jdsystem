<?php

namespace app\common\model;

use think\Model;

class UserHolidayApplyLists extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_HOLIDAY_APPLY_LISTS';


    public function getStatusTextAttr($value,$data){
        //0待审批1同意2被拒3续假4销假报道5超期未报道
        $status = [0=>'审核中',1=>'同意',2=>'拒绝',3=>'续假',4=>'销假审核中',5=>'同意',6=>'同意销假报道',7=>'拒绝销假报道'];
        return $status[$data['STATUS']];
    }
    public function getStatusSpanAttr($value,$data){
        //0待审批1同意2被拒3续假4销假报道5超期未报道
        $status = [
            -1=>'<span class="label">已撤消</span>',
            0=>'<span class="label">审核中</span>',
            1=>'<span class="label label-success">同意</span>',
            2=>'<span class="label label-important">拒绝</span>',
            3=>'<span class="label  label-info">续假</span>',
            4=>'<span class="label">销假审核中</span>',
            5=>'<span class="label label-success">同意</span>',
            6=>'<span class="label label-success">同意销假报道</span>',
            7=>'<span class="label label-important">拒绝销假报道</span>'
        ];
        return $status[$data['STATUS']];
    }

    public function IMGS(){
        return $this->hasMany('UserHolidayApplyListImgs','UHAL_ID','ID');
    }

    public static function setTimeOut($id,$uha_id){
        self::where('ID','=',$id)->update(['STATUS'=>5]);
        UserHolidayApplies::where('ID','=',$uha_id)->update(['STATUS'=>5]);
    }
}
