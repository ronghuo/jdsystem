<?php

namespace app\api1\controller;


use app\common\model\UserManagerPower;
use think\Request;
use app\common\model\Options as Opts;
use //app\common\model\Nations,
    app\common\model\Areas,
    app\common\model\AreasSubs,
    app\common\model\Dmmcs;
use app\common\model\BaseCertificateType,
    app\common\model\BaseWorkStatus,
    app\common\model\BaseWorkinfoType,
    app\common\model\BaseCultureType,
    app\common\model\BaseMarryType,
    app\common\model\BaseNationalityType,
    app\common\model\BaseNationType,
    app\common\model\BaseSexType;
use app\common\model\Upareatable;
use app\common\model\Subareas;
use app\common\model\NbAuthDept;

class Options extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {

        $data = Opts::getTreeAll();
        return $this->ok('ok', [
//            'tree'=>$opt_trees,
//            'NATIONS'=>Nations::all(),
            'NATIONS'=>BaseNationType::all()->toArray(),
//            'EDUS'=>$data['edus'],
            'EDUS'=>BaseCultureType::all()->toArray(),
//            'JOB_STATUS'=>$data['job_status'],
            'JOB_STATUS'=>BaseWorkStatus::all()->toArray(),
            'JOB_INFOS'=>BaseWorkinfoType::all()->toArray(),
//            'MARITAL_STATUS'=>$data['marital_status'],
            'MARITAL_STATUS'=>BaseMarryType::all()->toArray(),
            'GENDERS'=>BaseSexType::all()->toArray(),
            'CARD_TYPES'=>BaseCertificateType::all()->toArray(),
            'DRUG_TYPES'=>$data['drug_types'],
            'NARCOTICS_TYPES'=>$data['narcotics_types'],
            'CLUE_TYPES'=>$data['clue_types'],
            'CLUE_STATUS'=>$data['clue_status'],
            'EMEY_LEVELS'=>$data['emey_levels'],
            'REPORT_TYPES'=>$data['report_types'],
            'GATHER_TYPES'=>$data['gather_types'],
            'NATIONALITY'=>BaseNationalityType::all()->toArray()
//            'AREAS'=>$this->areas(true),
//            'DMMCS'=>$this->dmmcs($request,true),
        ]);
    }
    //省-市-区
    public function areas(Request $request,$return_array=false){
        //$hnid = 0;
        $prove_id = $request->param('PROVINCE_ID',430000,'int');
        if($prove_id == 43){
            $prove_id = 430000;
        }
        $cache_key = config('app.api_keys.areas').$prove_id;
        $trees = cache($cache_key);
        if(!$trees){
            $list = Upareatable::field('UPAREAID as ID,NAME,PID')
                ->where('UPAREAID', '<>', '010000')
                ->where('FLAG', 0)->all()->toArray();

            $trees = create_level_tree($list,$prove_id,'ID','PID');

            cache($cache_key,$trees,3600);
        }
        if($return_array){
            return $trees;
        }
        return $this->ok('ok', [
            'areas'=>$trees
        ]);

    }
    // 街道-社区信息表
    public function subAreas(Request $request, $return_array = false) {

//        $province_id = $request->param('PROVINCE_ID',DEFAULT_PROVINCE_ID,'int');
//        $city_id = $request->param('CITY_ID',DEFAULT_CITY_ID,'int');
//        $county_id = $request->param('COUNTY_ID',0,'int');
//
//        if ($province_id == 43) {
//            $province_id = DEFAULT_PROVINCE_ID;
//        }
//
//        if ($city_id == 4312) {
//            $city_id = DEFAULT_CITY_ID;
//        }
//
//        $cache_key = config('app.api_keys.subareas').implode('-',[
//                $province_id,
//                $city_id,
//                $county_id
//            ]);

        $muid = $request->param('muid', '', 'int');
        if (empty($muid)) {
            $this->fail('参数不正确');
        }
        $powers = UserManagerPower::where('UMID', $muid)->select()->toArray();
        if (empty($powers)) {
            $this->ok('ok', [
                'sub_areas' => []
            ]);
        }
        $power = $powers[0];

        $cache_key = config('app.api_keys.subareas') . $power['AREA_IDS'];

//        $trees = cache($cache_key);
        $trees = [];

        if (!$trees) {
            $list = Subareas::field('CODE12 as ID,NAME,PID')
                ->where(function($query) use ($power) {
                    $level = $power['LEVEL'];
                    if ($level == POWER_LEVEL_CITY) {
                        return;
                    }
                    $area = $power['AREA_IDS'];
                    if ($level == POWER_LEVEL_COUNTY) {
                        $query->where('COUNTY_ID', substr($area, 0, 6));
                    }
                    else if ($level == POWER_LEVEL_STREET) {
                        $query->where('CODE12', 'in', [$power['COUNTY_ID'], $power['STREET_ID']]);
                        $query->whereOr('PID', $power['STREET_ID']);
                    }
                    else if ($level == POWER_LEVEL_COMMUNITY) {
                        $query->where('CODE12', 'in', [$power['COUNTY_ID'], $power['STREET_ID'], $power['COMMUNITY_ID']]);
                        $query->whereOr('PID', $power->COMMUNITY_ID);
                    }
                })
                ->order('CODE12 ASC')
                ->select()->toArray();

            if (empty($list)) {
                $pid = 0;
            } else {
                $pid = $list[0]['PID'];
            }

            $trees = create_level_tree($list, $pid,'ID','PID');

            cache($cache_key,$trees,3600);
        }
        if ($return_array) {
            return $trees;
        }

        return $this->ok('ok', [
            'sub_areas' => $trees
        ]);
    }

    public function depts($return_array=false){

        $cache_key = config('app.api_keys.dmmcs').implode('-',[
                'v1'
            ]);

        $trees = cache($cache_key);

        if(!$trees){
            $all = NbAuthDept::field('ID, PARENTDEPTID as PID, DEPTCODE, DEPTNAME as NAME')
                ->where('FLAG', 0)
                ->all();
            $last = NbAuthDept::find(10470);
            $trees = create_level_tree($all->toArray(),10040);
            $trees[] = [
                'ID'=>$last['ID'],
                'PID'=>$last['PARENTDEPTID'],
                'DEPTCODE'=>$last['DEPTCODE'],
                'NAME'=>$last['DEPTNAME']
            ];

//            cache($cache_key,$trees,3600);
        }


        if($return_array){
            return $trees;
        }

        return $this->ok('ok', [
            'depts'=>$trees
        ]);

    }

    //单位信息
    public function dmmcs(Request $request,$return_array=false){

        $prove_id = $request->param('PROVINCE_ID',43,'int');
        $city_id = $request->param('CITY_ID',4312,'int');
        $county_id = $request->param('COUNTY_ID',0,'int');


        $cache_key = config('app.api_keys.dmmcs').implode('-',[
                $prove_id,
                $city_id,
                $county_id,
                'v1'
            ]);

        $trees = cache($cache_key);
        if(!$trees){
            $list = Dmmcs::field(['ID','DM','DMMC','PDM'])
                ->where('PROVINCE_ID','=',$prove_id)
                ->where('CITY_ID','=',$city_id)
                ->where(function($t) use ($county_id){
                    if($county_id>0){
                        return $t->where('COUNTY_ID','=',$county_id);
                    }
                })
                ->select();
            $pid = $county_id? '431200000000': 0;
            $trees = create_level_tree($list->toArray(),$pid,'DM','PDM');
//            print_r($trees);
            if($pid==0){
                $trees = $trees[0]['SUB'];
            }


            cache($cache_key,$trees,3600);
        }

        if($return_array){
            return $trees;
        }

        return $this->ok('ok', [
            'dmmcs'=>$trees
        ]);
    }

    //怀化市，区县-街道-社区 关联数据

    public function hhlevelareas(Request $request){

        $trees = $this->subAreas($request, true);
        return $this->ok('ok', [
            'levelareas'=>$trees
        ]);
        $cache_key = config('app.api_keys.levelareas').implode('-',['v1']);

        $trees = cache($cache_key);
        if(!$trees){
            $trees = Areas::where('PID','=',4312)->select()->map(function($t){
                $subs = AreasSubs::where('COUNTY_ID','=',$t->ID)
                    ->where('ACTIVE',1)->select();
                $t->SUB = create_level_tree($subs);
                return $t;
            });

            cache($cache_key,$trees,120);
        }


        return $this->ok('ok', [
            'levelareas'=>$trees
        ]);
    }



    //省-市-区 20190821 停用
    public function areasbak(Request $request,$return_array=false){
        //$hnid = 0;
        $prove_id = $request->param('PROVINCE_ID',43,'int');

        $cache_key = config('app.api_keys.areas').$prove_id;
        $trees = cache($cache_key);
        if(!$trees){
            $list = Areas::all();
            $trees = create_level_tree($list,$prove_id,'ID','PID');

            cache($cache_key,$trees,3600);
        }
        if($return_array){
            return $trees;
        }
        return $this->ok('ok', [
            'areas'=>$trees
        ]);

    }
    // 街道-社区信息表 20190821 停用
    public function subAreasbak(Request $request){

        $prove_id = $request->param('PROVINCE_ID',43,'int');
        $city_id = $request->param('CITY_ID',4312,'int');
        $county_id = $request->param('COUNTY_ID',0,'int');

        $cache_key = config('app.api_keys.subareas').implode('-',[
                $prove_id,
                $city_id,
                $county_id
            ]);

        $trees = cache($cache_key);

        if(!$trees){
            $list = AreasSubs::where('PROVINCE_ID','=',$prove_id)
                ->where('CITY_ID','=',$city_id)
                ->where('ACTIVE',1)
                ->where(function($t) use ($county_id){
                    if($county_id>0){
                        return $t->where('COUNTY_ID','=',$county_id);
                    }
                })
                ->select();
            $trees = create_level_tree($list,0,'ID','PID');

            cache($cache_key,$trees,3600);
        }



        return $this->ok('ok', [
            'sub_areas'=>$trees
        ]);
    }

}
