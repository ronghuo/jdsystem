<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/13
 */
namespace app\htsystem\controller;


use think\Request;
use Carbon\Carbon;
use app\common\model\SystemMessages as SystemMessageModel;
use app\htsystem\validate\SystemMessageVer;
use app\common\library\Shorturl;

class SystemMessages extends Common{

    //
    public function index(Request $request){

        $list = SystemMessageModel::order('ID','desc')->paginate(self::PAGE_SIZE, false, [
            'query'=>request()->param(),
        ]);

        $js = $this->loadJsCss(array('systemmessages_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('page', $list->render());

        return $this->fetch();
    }


    //
    public function read($id=0){
        if(!$id){
            $this->error('访问错误');
        }
        $info = SystemMessageModel::where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该消息不存在或已删除');
        }

        $this->assign('info',$info);
        return $this->fetch();
    }


    //
    public function create(Request $request,$id=0){

        if($request->isPost()){
            return $this->save($request);
        }

        $info = [];
        if($id>0){
            $info = SystemMessageModel::where('ID',$id)
                ->where('ISDEL',0)->find();
            if(!$info){
                $this->error('该消息不存在或已删除');
            }
        }

        $js = $this->loadJsCss(array('systemmessages_create'), 'js', 'admin');
        $this->assign('footjs', $js);

        $this->assign('client_tags',[
            1=>'康复端',
            2=>'管理端',
            3=>'康复端 & 管理端',
        ]);
        $this->assign('info',$info);
        return $this->fetch('create');
    }


    //
    public function edit(Request $request,$id=0){
        if(!$id){
            $this->error('访问错误');
        }

        return $this->create($request,$id);
    }

    //
    public function delete($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }

        $info = SystemMessageModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该消息不存在或已删除');
        }
        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();
        //todo 还需要将 Read表中也处理

        $this->success('删除成功');
    }


    public function push(Request $request){
        $id = $request->post('id',0,'int');
        $to = $request->post('to',0,'int');

        if(!$id || !$to){
            return ['err'=>1,'msg'=>'参数有误'];
        }

        $info = SystemMessageModel::find($id);
        if(!$info){
            return ['err'=>1,'msg'=>'该消息不存在或已删除'];
        }
        try {
            $url = get_host() . url('h5/AppPages/info', ['uid' => 0, 'tag' => $to, 'type' => 2, 'id' => $info->ID]);
            //Shorturl::sina_create($url);

            $data = [
                'user_id' => 'all',
                'message' => $info->TITLE,
                'metas' => ['url' => Shorturl::sina_create($url)]
            ];

            if (in_array($to, [1, 3])) {
                $data['type'] = 'u';
                \think\Queue::later(2, '\app\common\job\Jpush', $data);
            }

            if (in_array($to, [2, 3])) {
                $data['type'] = 'm';
                \think\Queue::later(2, '\app\common\job\Jpush', $data);
            }


            return ['err' => 0, 'msg' => '已发送'];
        }catch (\Exception $e){
            return ['err'=>1,'msg'=>$e->getMessage(),'error'=>[
                $e->getFile(),
                $e->getLine()
            ]];
        }
    }


    //
    protected function save(Request $request){

        $id = $request->post('ID',0,'int');

        $ref = $request->post('ref') ? : url('SystemMessages/index');

        $data = [
            'POSTER_UID'=>session('user_id'),
            'POSTER_NAME'=>session('name'),
            'CLIENT_TAG'=>$request->post('CLIENT_TAG','','trim'),
            'TITLE'=>$request->post('TITLE','','trim'),
            'CONTENT'=>$request->post('CONTENT','','trim')
        ];

        $v = new SystemMessageVer();
        if(!$v->check($data)){
            $this->error($v->getError());
        }

        if($id>0){
            SystemMessageModel::where('ID',$id)->update($data);
        }else{
            $id = (new SystemMessageModel())->insertGetId($data);
        }

        if($id>0){
            $this->success('发送成功',$ref);
        }

        $this->error('发送失败');
    }


}
