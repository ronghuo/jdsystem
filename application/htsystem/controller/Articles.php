<?php

namespace app\htsystem\controller;

use app\common\validate\ArticlesVer;
use Carbon\Carbon;
use think\Request;
use app\common\model\Articles as ArticlesModel,
    app\common\model\ArticleCate;
use app\common\validate\ArticleCateVer;
use app\common\library\Shorturl;

class Articles extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = ArticlesModel::where('ISDEL','=',0)->paginate(self::PAGE_SIZE, false, [
            'query'=>request()->param(),
        ])->each(function($t,$k){
            $t->COVER_IMG_URL = build_http_img_url($t->COVER_IMG);
            $t->cate;
        });
//        print_r($list);
        $js = $this->loadJsCss(array('articles_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request,$id=0)
    {
        if($request->isPost()){
            return $this->save($request);
        }

        $info = [];
        if($id>0){

            $info = ArticlesModel::where('ISDEL','=',0)->find($id);
            if(!$info){
                $this->error('该资讯不存在或已删除');
            }
            $info->COVER_IMG_URL = build_http_img_url($info->COVER_IMG);
        }

        $cates = ArticleCate::order('ORDERID','asc')->select();


        $js = $this->loadJsCss(array('p:ueditor/ueditor','articles_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        $this->assign('cates',$cates);
        return $this->fetch('create');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    protected function save(Request $request)
    {
//        $post = $request->post();
//        dump($post);
        $ref = $request->post('ref') ? : url('Articles/index');
        $id = $request->param('ID',0,'int');

        list($cate_id,$client_tag) = explode('-',$request->param('cate'));

        $data = [
            'STATUS'=>1,
            'CLIENT_TAG'=>$client_tag,
            'CATE_ID'=>$cate_id,
            'POSTER_UID'=>session('user_id'),
            'TITLE'=>$request->param('TITLE','','trim'),
            'CONTENT'=>$request->param('CONTENT','','trim'),
        ];

//        dump($data);

        $v = new ArticlesVer();
        if(!$v->check($data)){
            $this->error($v->getError());
        }
        if($id>0){
            ArticlesModel::where('ID','=',$id)->update($data);
        }else{
            $id = (new ArticlesModel())->insertGetId($data);
        }
        if(!$id){
            $this->error('保存资讯失败');
        }

        $img = $this->uploadImage($request,['articles/']);
        if(isset($img['images'])){
            ArticlesModel::where('ID','=',$id)->update(['COVER_IMG'=>$img['images'][0]]);
        }

        $this->success('保存资讯成功',$ref);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        $info = ArticlesModel::where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该资讯不存在或已删除');
        }
        $info->COVER_IMG_URL = build_http_img_url($info->COVER_IMG);

        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request,$id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }

        $info = ArticlesModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该资讯不存在或已删除');
        }
        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        $this->success('删除成功');
    }



    public function push(Request $request){
        $id = $request->post('id',0,'int');
        $to = $request->post('to',0,'int');

        if(!$id || !$to){
            return ['err'=>1,'msg'=>'参数有误'];
        }

        $info = ArticlesModel::where('ISDEL','=',0)->find($id);
        if(!$info){
            return ['err'=>1,'msg'=>'该资讯不存在或已删除'];
        }
        try{
            $url = get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>$to,'type'=>1,'id'=>$info->ID]);
            //Shorturl::sina_create($url);

            $data = [
                'user_id'=>'all',
                'message'=>$info->TITLE,
                'metas'=>['url'=>Shorturl::sina_create($url)]
            ];

            if(in_array($to,[1,3])){
                $data['type'] = 'u';
                \think\Queue::later(2,'\app\common\job\Jpush',$data);
            }

            if(in_array($to,[2,3])){
                $data['type'] = 'm';
                \think\Queue::later(2,'\app\common\job\Jpush',$data);
            }


            return ['err'=>0,'msg'=>'已发送'];
        }catch (\Exception $e){
            return ['err'=>1,'msg'=>$e->getMessage(),'error'=>[
                $e->getFile(),
                $e->getLine()
            ]];
        }

    }

    //=======================================================================

    public function cateIndex(){
        $list = ArticleCate::order('ORDERID','asc')->select();


        $js = $this->loadJsCss(array('articles_cate_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        return $this->fetch();
    }
    public function cateRead(){}
    public function cateCreate(Request $request,$id=0){
        if($request->isPost()){
            return $this->saveCate($request);
        }
        $info = [];
        if($id>0){
            $info = ArticleCate::find($id);
        }

        $js = $this->loadJsCss(array('articles_cate_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        $this->assign('clienttags',$this->clientTags());
        return $this->fetch('catecreate');
    }
    public function cateEdit(Request $request,$id=0){

        if(!$id){
            $this->error('访问错误');
        }
        return $this->cateCreate($request,$id);

    }
    public function cateDelete($id=0){
        if(!$id){
            $this->error('访问错误');
        }

        ArticleCate::where('ID','=',$id)->delete();

        $this->success('删除成功');
    }


    protected function saveCate(Request $request){

        $id = $request->param('ID',0,'int');

        $data = [
            'CLIENT_TAG'=>$request->param('CLIENT_TAG','','trim'),
            'NAME'=>$request->param('NAME','','trim')
        ];

        $v = new ArticleCateVer();
        if(!$v->check($data)){
            $this->error($v->getError());
        }

        if($id>0){
            ArticleCate::where('ID','=',$id)->update($data);
        }else{
            (new ArticleCate())->insert($data);
        }

        $this->jsalert('保存成功',3);
    }

    protected function clientTags(){
        return [
            1=>'康复端',
            2=>'管理端',
            3=>'康复端&管理端',
        ];
    }
}
