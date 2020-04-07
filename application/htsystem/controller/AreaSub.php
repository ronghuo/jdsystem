<?php
namespace app\htsystem\controller;

use think\Request;
use app\common\model\Subareas;
use app\common\model\UserUsers,
    app\common\model\UserManagers;
use app\common\library\JqueryCateData;
use Carbon\Carbon;



class AreaSub extends Common{


    public function index(){

        $area1 = input('get.area1','');
        $area2 = input('get.area2','');
        $area3 = input('get.area3','');

        $pid = 0;
        $show_merge = false;
        $show_add = true;

        if($area3 >0){
            $pid = $area3;
            $show_add = false;
        }elseif($area2 > 0){
            $pid = $area2;
        }elseif($area1 > 0){
            $pid = $area1;
            $show_merge = true;
        }
        $areas = [];
        $areaInfo = [];
        $isSo = false;
        if($pid > 0){
            $isSo = true;
            $areaInfo = Subareas::where('CODE12', $pid)->find();
            $areas = Subareas::where('PID', $pid)->select();
        }
//        print_r($areaInfo);

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'area_sub_index'), 'js', 'admin');
        $this->assign('footjs', $js);
//        return json_encode($trees);
//        $this->assign('trees', $trees);
        $this->assign('area1', $area1);
        $this->assign('area2', $area2);
        $this->assign('area3', $area3);
        $this->assign('areas', $areas);
        $this->assign('areaInfo', $areaInfo);
        $this->assign('is_so', $isSo);
        $this->assign('show_merge', $show_merge);
        $this->assign('show_add', $show_add);
        return $this->fetch();
    }

    public function create(Request $request, $pid=0){

        if($request->isPost()){
            return $this->save($request);
        }

//        $pid = $request->get('pid', 0);
        if(!$pid || $pid<=0){
            $this->error('访问错误');
        }

        $pinfo = Subareas::find($pid);
        if(!$pinfo){
            $this->error('该地区不存在');
        }


        $js = $this->loadJsCss(array( 'area_sub_edit'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('pinfo', $pinfo);
        return $this->fetch();
    }

    public function edit(Request $request,$id=0){
        if(!$id){
            $this->error('访问错误');
        }

        $info = Subareas::find($id);
        if(!$info){
            $this->error('该地区不存在');
        }
        if($request->isPost()){
            $this->update($request, $info);
        }

        $js = $this->loadJsCss(array( 'area_sub_edit'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        return $this->fetch('edit');
    }

    public function merge(Request $request, $pid=0){


        if($request->isPost()){
            return $this->saveMerge($request);
        }

        if(!$pid || $pid<=0){
            $this->error('访问错误');
        }

        $pinfo = Subareas::find($pid);
        if(!$pinfo){
            $this->error('该地区不存在');
        }

        $childs = Subareas::where('PID', $pinfo->CODE12)->where('ACTIVE', 1)->select();

        $this->assign('pinfo', $pinfo);
        $this->assign('childs', $childs);
        return $this->fetch();
    }

    /**
     * 处理合并
     * @param Request $request
     */
    protected function saveMerge(Request $request){

        $selected = $request->post('selected');
        $mergeToId = $request->post('mergeTo');

        if(empty($selected) || !$mergeToId){
            $this->error('请选择合并的地区');
        }

        $mergeTo = Subareas::find($mergeToId);
        foreach($selected as $sid){

            if($sid == $mergeTo->ID){
                continue;
            }

            $self = Subareas::find($sid);
            $self->MERGE_TO = $mergeTo->CODE12;
            $self->ACTIVE = 0;
            $self->save();

            Subareas::where('PID', $self->CODE12)->update([
                'PID'=>$mergeTo->CODE12,
                'OLD_PID'=>$self->CODE12
            ]);

            UserUsers::where('STREET_ID', $self->CODE12)->update([
                'STREET_ID'=>$mergeTo->CODE12
            ]);

            UserManagers::where('STREET_ID', $self->CODE12)->update([
                'STREET_ID'=>$mergeTo->CODE12
            ]);
        }


        $this->updateCache();
        $this->jsalert('合并成功',3);

    }

    /**
     * 保存新增数据
     * @param Request $request
     */
    protected function save(Request $request){

        $pid = $request->post('PID', '');
        $name = $request->post('NAME', '');
        if(!$name){
            $this->error('请填写地区名称');
        }

        $pinfo = Subareas::find($pid);
        if(!$pinfo){
            $this->error('该地区不存在');
        }

        //计算CODE12
        $code = Subareas::getNewCode12($pinfo->CODE12);

        if(!$code){
            $this->error('该地区不存在');
        }

        $lastChild = Subareas::where('PID', $pinfo->CODE12)->order('CODE12', 'DESC')->find();

        $data = [
            'CODE12'=>$code,
            'COUNTRY_CODE'=>$lastChild ? $lastChild->COUNTRY_CODE : 39,
            'CITY_COUNTRY_CODE'=>$lastChild ? $lastChild->CITY_COUNTRY_CODE : 220,
            'NAME'=>trim($name),
            'PROVICEID'=>$pinfo->PROVICEID,
            'CITYID'=>$pinfo->CITYID,
            'COUNTYID'=>$pinfo->COUNTYID,
            'PID'=>$pinfo->CODE12,
            'ACTIVE'=>1,
            'UPDATE_TIME'=>Carbon::now()->toDateTimeString()
        ];

//        print_r([
//            $pinfo->toArray(),
//            $maxCode,
//            $code,
//            $lastChild,
//            $data
//        ]);
//        exit;
        $area = Subareas::create($data);
        if($area){
            $this->updateCache();

            $this->jsalert('保存成功',3);
        }

        $this->error('添加失败');
    }


    /**
     * 保存编辑数据
     * @param Request $request
     * @param Subareas $info
     */
    protected function update(Request $request, Subareas $info){
        $name = $request->post('NAME', '');
        $active = $request->post('ACTIVE', 1);

        if(!$name){
            $this->error('请填写地区名称');
        }


        $info->NAME = $name;
        $info->ACTIVE = $active;
        $info->save();


        $this->updateCache();
        $this->jsalert('编辑成功',3);
        return ;
    }


    protected function updateCache(){
        //更新前端插件用的json文件
        JqueryCateData::createHHLevelAreasJson();

        //更新js缓存
        $file_path = config('cache_version_file');
        \app\common\library\StaticCache::refresh($file_path);
    }
}