<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/23
 */
namespace app\common\model;
use think\Collection;

class DrugMessageReports extends BaseModel{

    protected $pk = 'ID';
    public $table = 'DRUG_MESSAGE_REPORTS';

    public function IMGS(){
        return $this->hasMany('DrugMessageReportImgs','DMR_ID','ID');
    }
    public function muser(){
        return $this->belongsTo('UserManagers','UMID','ID');
    }

    public function getHappenedAttr($value,$data){
        $maps = [0=>'未发生',1=>'已发生'];
        return isset($maps[$data['DOES_HAPPENED']]) ? $maps[$data['DOES_HAPPENED']] : '';
    }

    public function getClueStatusAttr($value,$data){
        return Options::getClueStatus($data['CLUE_STATUS_ID']);
    }

    public function getClueTypeAttr($value,$data){
        return Options::getClueType($data['CLUE_TYPE_ID']);
    }


    public function getEmeyLevelAttr($value,$data){
        return Options::getEmeyLevel($data['EMEY_LEVEL_ID']);
    }

    public function getReportTypeAttr($value,$data){
        return Options::getReportType($data['REPORT_TYPE_ID']);
    }

    public function getGatherTypeAttr($value,$data){
        return Options::getGatherType($data['GATHER_TYPE_ID']);
    }


    public function createNewDMRCode($COUNTY_ID){
        $code = 0;
        $last = $this->field('ID,DMR_CODE')->where('COUNTY_ID','=',$COUNTY_ID)->order('ID','DESC')->find();
        $prefix = 'DR'.$COUNTY_ID;
        if($last){
            $code =  (int) str_replace($prefix,'',$last['DMR_CODE']);
        }
//        echo $code;
        $code = sprintf("%05d", ($code+1));
        return $prefix.$code;

    }
}