<?php

namespace app\common\model;

use think\Model;
use Carbon\Carbon;
use think\model\Collection;
use think\Db;

class HelperDiarys extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'HELPER_DIARYS';

    public function IMGS(){
        return $this->hasMany('HelperDiaryImgs','HD_ID');
    }

    public function getCountsGroupByUUID($muid,$year,$month){
        $sql = 'select UUID,count(*) as tt from HELPER_DIARYS where UMID=:muid and ADD_YEAR=:iyear and ADD_MONTH=:imonth group by UUID';
        return Db::query($sql,['muid'=>$muid,'iyear'=>$year,'imonth'=>$month]);
    }
}
