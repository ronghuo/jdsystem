<?php

namespace app\common\model;

use think\Model;

class UserManagerAreas extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_MANAGER_AREAS';



    public function areaIds($umid){
        $list = $this->where('UMID','=',$umid)->select();
        $return = [
            'PROVINCE_ID'=>[],
            'CITY_ID'=>[],
            'COUNTY_ID'=>[],
            'STREET_ID'=>[],
            'COMMUNITY_ID'=>[],
        ];
        if(empty($list)){
            return $return;
        }

        foreach($list as $v){
            if($v->PROVINCE_ID>0){
                $return['PROVINCE_ID'][] =  $v->PROVINCE_ID;
            }
            if($v->CITY_ID>0){
                $return['CITY_ID'][] =  $v->CITY_ID;
            }
            if($v->COUNTY_ID>0){
                $return['COUNTY_ID'][] =  $v->COUNTY_ID;
            }
            if($v->STREET_ID>0){
                $return['STREET_ID'][] =  $v->STREET_ID;
            }
            if($v->COMMUNITY_ID>0){
                $return['COMMUNITY_ID'][] =  $v->COMMUNITY_ID;
            }
        }
        $return['PROVINCE_ID'] = array_unique($return['PROVINCE_ID']);
        $return['CITY_ID'] = array_unique($return['CITY_ID']);
        $return['COUNTY_ID'] = array_unique($return['COUNTY_ID']);
        $return['STREET_ID'] = array_unique($return['STREET_ID']);
        $return['COMMUNITY_ID'] = array_unique($return['COMMUNITY_ID']);

        return $return;
    }


}
