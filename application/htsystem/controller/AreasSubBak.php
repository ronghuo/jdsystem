<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/29
 */
namespace app\htsystem\controller;

use think\Request;
use app\common\model\AreasSubs,
    app\common\model\Areas;
use app\common\model\Upareatable,
    app\common\model\Subareas;

class AreasSub extends Common{


    public function index(Request $request){
        $city_id = $request->param('city_id',431200,'int');
        $county_id = $request->param('county_id',0,'int');
        $street_id = $request->param('street_id','','trim');
        $show_delete = false;
        $show_add = false;
        if($street_id){
//            $info = AreasSubs::find($street_id);
            $info = Subareas::where('CODE12', $street_id)->find();
            $list = Subareas::where('PID','=',$street_id)
                //->where('ACTIVE','=',1)
                ->select()
                ->map(function($t) use($city_id,$county_id){
                $t->query = false;
                return $t;
            })->chunk(4);
            $show_add = false;
        }else if($county_id>0){
//            $info = Areas::find($county_id);
            $info = Upareatable::where('UPAREAID', $county_id)->find();
//            $list = AreasSubs::where('COUNTY_ID','=',$county_id)
            $list = Subareas::where('COUNTYID','=',$county_id)
                //->where('PID','=',0)
                //->where('ACTIVE','=',1)
                ->select()->map(function($t) use($city_id,$county_id){
                $t->query = ['city_id'=>$city_id,'county_id'=>$county_id,'street_id'=>$t->CODE12];
                return $t;
            })->chunk(4);
            $show_add = true;
        }else{
            $show_delete = false;
            $info = Upareatable::where('UPAREAID', $city_id)->find();
//            $info = Areas::find($city_id);
//            $list = Areas::where('PID','=',$city_id)
            $list = Upareatable::where('PID','=',$city_id)

                ->select()->map(function($t) use($city_id){
                $t->query = ['city_id'=>$city_id,'county_id'=>$t->UPAREAID,'street_id'=>0];
                return $t;
            })->chunk(4);

            $show_add = true;
        }

        $addquery = false;
        if($county_id>0){
            $addquery = ['city_id'=>$city_id,'county_id'=>$county_id,'street_id'=>$street_id];
        }

        $js = $this->loadJsCss(array('areassub_index'), 'js', 'admin');
        $this->assign('footjs', $js);

        $this->assign('addquery',$addquery);
        $this->assign('show_delete',$show_delete);
        $this->assign('show_add',false);

        $this->assign('info',$info->toArray());
        $this->assign('list',$list->toArray());

        return $this->fetch();
    }

    public function create(Request $request){

        if($request->isPost()){
            return $this->save($request);
        }

        $city_id = $request->param('city_id',4312,'int');
        $county_id = $request->param('county_id',0,'int');
        $street_id = $request->param('street_id',0,'int');

        $area = [];
        if($city_id>0){
            $area[] = Areas::find($city_id)->NAME;
        }
        if($county_id>0){
            $area[] = Areas::find($county_id)->NAME;
        }
        if($street_id>0){
            $area[] = AreasSubs::find($street_id)->NAME;
        }

        $js = $this->loadJsCss(array('areassub_create'), 'js', 'admin');
        $this->assign('footjs', $js);


        $this->assign('city_id',$city_id);
        $this->assign('county_id',$county_id);
        $this->assign('street_id',$street_id);
        $this->assign('area',implode('-',$area));
        return $this->fetch();
    }

    public function delete(Request $request){
        if(!$request->isAjax()){
            $this->error('访问异常');
        }

        $id = $request->param('id',0,'int');
        if(!$id || $id<=0){
            return ['err'=>1,'msg'=>'参数错误'];
        }

        AreasSubs::where('ID','=',$id)->update(['ACTIVE'=>0]);

        return ['err'=>0,'msg'=>'ok'];
    }


    protected function save(Request $request){

        $city_id = $request->param('city_id',4312,'int');
        $county_id = $request->param('county_id',0,'int');
        $street_id = $request->param('street_id',0,'int');
        $arealist = $request->param('arealist','','trim');

        $city = Areas::find($city_id);


        $areas = explode(PHP_EOL,$arealist);
        if(empty($areas)){
            return $this->jsalert('地区信息缺失');
        }

        //print_r($areas);
        $inserts = [];
        foreach($areas as $area){
            $inserts[] = [
                'NAME'=>$area,
                'PID'=>$street_id,
                'PROVINCE_ID'=>$city->PID,
                'CITY_ID'=>$city->ID,
                'COUNTY_ID'=>$county_id
            ];
        }
        (new AreasSubs())->insertAll($inserts);
        //todo 生成社区json数据 放队列
        return $this->jsalert('添加成功',3);
    }
}
