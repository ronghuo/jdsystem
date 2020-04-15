<?php

namespace app\common\model;

use think\helper\Str;
use think\Model;

class UserManagers extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_MANAGERS';


    public function dmmc(){
        return $this->belongsTo('NbAuthDept','DMM_ID','ID');
//        return $this->belongsTo('Dmmcs','DMM_ID','ID');
    }

    public function getGenderTextAttr($value,$data){
        $map = [1=>'未知的性别',2=>'男',3=>'女', 4=>'未说明的性别'];
        return isset($map[$data['GENDER']]) ? $map[$data['GENDER']] : $map[1];
    }

    public function getStatusTextAttr($value,$data){
        $map = [0=>'<span class="badge">待审批</span>',1=>'<span class="badge label-success">审批通过</span>',2=>'<span class="badge label-important">审批不通过</span>'];
        return isset($map[$data['STATUS']]) ? $map[$data['STATUS']] : $map[0];
    }
    // rewrite this 20190822
    public function createNewUCode($DMM_ID){

        $code = Str::substr(str_shuffle(str_repeat('1234567890', 6)), 0, 6);

        $ucode = Str::substr($this->COUNTY_ID_12, 0, 6).$code;

        if($this->where('UCODE', $ucode)->count()){
            return $this->createNewUCode($DMM_ID);
        }else{
            return $ucode;
        }
//
//        $dmm = NbAuthDept::find($DMM_ID);
//
//        $street_id = substr($dmm->AREACODE, 0, 9) . '000';
//        //4312 22105 000
//        $partCode = substr($dmm->AREACODE, 4, 5);
////        echo $partCode.'|';
//
//        $last = $this->field('ID,UCODE')->where('STREET_ID', $street_id)->order('ADD_TIME','DESC')->find();
//        if(!$last){
//            $code = $partCode.'001';
//        }else{
//            $first = substr($last->UCODE, 0, 1);
//            $end = substr($last->UCODE, 1);
//
//            $code = $first . (((int) $end ) + 1);
//        }
//
//
//        return $code;
    }

}
