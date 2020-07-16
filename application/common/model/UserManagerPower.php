<?php

namespace app\common\model;

use think\Model;
use Carbon\Carbon;
use think\model\Collection;

class UserManagerPower extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_MANAGER_POWER';



    public function savePowerSettings($data){

        if(!$data['AREA_IDS']){
            $this->where('UMID','=',$data['UMID'])->where('LEVEL','=',$data['LEVEL'])->delete();
        }else{
            $data['UPDATE_TIME'] = Carbon::now()->toDateTimeString();
            if($this->where('UMID','=',$data['UMID'])->where('LEVEL','=',$data['LEVEL'])->count()){
                $this->where('UMID','=',$data['UMID'])->where('LEVEL','=',$data['LEVEL'])->update($data);
            }else{
                $this->insert($data);
            }
        }

    }

    public function getPowerSettings($umid) {

        $list = $this->where('UMID',$umid)->select();
        $areas = [];
        foreach($list as $v){
            if ($v['LEVEL'] == 1) {
                $areas['CITY_IDS'] = $v['AREA_IDS'];
            }
            else if ($v['LEVEL'] == 2) {
                $areas['COUNTY_IDS'] = $v['AREA_IDS'];
            }
            else if ($v['LEVEL'] == 3) {
                $areas['STREET_IDS'] = $v['AREA_IDS'];
            }
            else if ($v['LEVEL'] == 4) {
                $areas['COMMUNITY_IDS'] = $v['AREA_IDS'];
            }
        }
        return $areas;
    }


}
