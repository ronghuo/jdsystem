<?php

namespace app\common\model;

use think\Model;

class Urans extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'URANS';

    public function dmmc(){
//        return $this->belongsTo('Dmmcs','DMM_ID','ID');
        return $this->belongsTo('NbAuthDept','DMM_ID','ID');
    }
    public function uuser(){
        return $this->belongsTo('UserUsers','UUID','ID');
    }

    public function muser(){
        return $this->belongsTo('UserManagers','UMID','ID');
    }

    public function imgs(){
        return $this->hasMany('UranImgs','URAN_ID');
    }
    //TODO rewrite this
    public function createNewUUCode(){

        $code = \think\helper\Str::substr(str_shuffle(str_repeat('1234567890', 6)), 0, 6);

        $ucode = 'UR4312'.date('ymd').$code;

        if($this->where('URAN_CODE', $ucode)->count()){
            return $this->createNewUUCode();
        }else{
            return $ucode;
        }


//        $code = 1;
//        $last = $this->field('ID,URAN_CODE')->order('ID','DESC')->find();
//        $prefix = 'UR431';
//        if($last){
//            $code =  (int) str_replace($prefix,'',$last['URAN_CODE']);
//        }
////        echo $code;
//        $code = sprintf("%06d", ($code+1));
//        return $prefix.$code;

    }


}
