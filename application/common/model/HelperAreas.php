<?php

namespace app\common\model;

use think\Model;
use Carbon\Carbon;
use think\model\Collection;

class HelperAreas extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'HELPER_AREAS';



    public function saveSettings($data){


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

    public function getSettings($umid){
        //echo $umid;exit;

        $list = $this->where('UMID',$umid)->select();
        $areas = [];
        foreach($list as $v){
            if($v['LEVEL']==1){
                $areas['CITY_IDS'] = $v['AREA_IDS'];
            }
            if($v['LEVEL']==2){
                $areas['COUNTY_IDS'] = $v['AREA_IDS'];
            }
            if($v['LEVEL']==3){
                $areas['STREET_IDS'] = $v['AREA_IDS'];
            }
            if($v['LEVEL']==4){
                $areas['COMMUNITY_IDS'] = $v['AREA_IDS'];
            }
        }
        return $areas;
    }

    public function getUserIdsInAreas($umid){

        $cache_key = 'helper:'.$umid;

        $ids = cache($cache_key);
        if($ids){
            return $ids;
        }

        $areas = $this->getSettings($umid);

        if(!$areas){
            return [];
        }

        $ids = (new UserUsers())->getUserIdsByAreas($areas);
        cache($cache_key,$ids,60);

        return $ids;
    }

    public function isInMyAreas($uuid,$umid){
        $my_uuids = $this->getUserIdsInAreas($umid);
        return in_array($uuid,$my_uuids);
    }

}
