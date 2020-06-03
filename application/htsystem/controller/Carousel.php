<?php
namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Request;
use app\common\model\Carousels as CarouselModel;
use app\htsystem\validate\CarouselVer;
use app\common\library\Carousel as CarouselLib;
use app\common\model\WaitDeleteFiles;

class Carousel extends Common{


    public function index(){

        $list = CarouselModel::where('ISDEL',0)
            ->paginate(self::PAGE_SIZE, false, [
                'query'=>request()->param(),
            ])
            ->each(function($item,$key){

            $item = CarouselLib::parseRow($item);

            return $item;
        });

//        print_r($list);
//
        $js = $this->loadJsCss(array('carousel_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('client_tags',$this->clientTags());
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function read(){

    }

    public function create(Request $request,$id=0){

        if($request->isPost()){
            return $this->save($request);
        }
        $stable = $request->param('stable','');
        $sid = $request->param('sid','');
        $sinfo = [];
        $info = [];
        if($id>0){
            $info = CarouselModel::where('ID',$id)->where('ISDEL',0)->find();
            if(!$info){
                $this->error('该轮播不存在或已删除');
            }
            //$info->IMG_URL = build_http_img_url($info->IMG);

            $info = CarouselLib::parseRow($info);
        }elseif($stable && $sid){
            $sinfo = CarouselLib::getStableInfo($stable,$sid);
            //print_r($sinfo->toArray());
        }

        $js = $this->loadJsCss(array('carousel_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('client_tags',$this->clientTags());
        $this->assign('stable',$stable);
        $this->assign('sid',$sid);
        $this->assign('sinfo',$sinfo);
        $this->assign('info',$info);
        return $this->fetch('create');
    }

    public function edit(Request $request,$id=0){
        if(!$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id);
    }

    public function delete($id=0){
        if(!$id){
            $this->error('访问错误');
        }
        $info = CarouselModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该轮播不存在或已删除');
        }
        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        // stable
        if($info->STABLE && $info->SID){
            CarouselLib::updateStable($info->STABLE,$info->SID,0);
        }

        // 还需要将 其中的图片也删除
        if($info->IMG){
            WaitDeleteFiles::addOne([
                'table'=>'carousels',
                'id'=>$info->ID,
                'path'=>$info->IMG
            ]);
        }

        $this->success('删除成功');

    }

    //
    protected function save(Request $request){

//        $post = $request->post();
//        print_r($post);

        $id = $request->post('ID',0,'int');

        $ref = $request->post('ref') ? : url('Carousel/index');

        $data = [
            'POS'=>1,
            'TYPE'=>2,
            'CLIENT_TAG'=>$request->post('CLIENT_TAG',''),
            'JUMP_LINK'=>$request->post('JUMP_LINK',''),
            'STABLE'=>$request->post('STABLE',''),
            'SID'=>$request->post('SID',''),
        ];
        $scene = 'un_stable';
        if($data['STABLE']){
            $scene = 'stable';
        }

        $v = new CarouselVer();
        if(!$v->scene($scene)->check($data)){
            $this->error($v->getError());
        }
        //$carousel = null;
        if($id>0){
            $carousel = CarouselModel::find($id);
            //CarouselModel::where('ID',$id)->update($data);
            $carousel->save($data);
        }else{
            //$carousel = new CarouselModel();
            $carousel = CarouselModel::create($data);
            $carousel->IMG = '';
            //$id = $carousel->insertGetId($data);
        }

        if($carousel->STABLE && $carousel->SID){
            CarouselLib::updateStable($carousel->STABLE,$carousel->SID,$carousel->ID);
        }

        $img = $this->uploadImage($request,['carousels/']);

        if(!empty($img['images'])){
            // 如果存在老的图片，刚将其删除
            if($carousel->IMG){
                WaitDeleteFiles::addOne([
                    'table'=>'carousels',
                    'id'=>$carousel->ID,
                    'path'=>$carousel->IMG
                ]);
            }
            $carousel->save(['IMG'=>$img['images'][0]]);
            //CarouselModel::where('ID','=',$id)->update();
        }
        $this->jsalert('保存轮播成功',3);
        //$this->success('保存轮播成功',$ref);
    }

}