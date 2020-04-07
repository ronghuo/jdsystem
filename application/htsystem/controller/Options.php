<?php

namespace app\htsystem\controller;

use think\Controller;
use think\Request;
use app\common\model\Options as OptModel;

class Options extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = OptModel::all();
        $trees = create_level_tree($list);
        //print_r($trees);

        $js = $this->loadJsCss(array('options_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('trees',$trees);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request)
    {
        return $this->save($request);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request)
    {
        return $this->save($request);
    }



    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request)
    {
        if(!$request->isAjax()){

            return ['err'=>1,'msg'=>'访问错误'];
        }

        $id = $request->post('id',0,'int');

        if(!$id){
            return ['err'=>1,'msg'=>'数据有误'];
        }

        OptModel::where('ID',$id)->delete();

        return ['err'=>0,'msg'=>'ok'];
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    protected function save(Request $request)
    {
        if(!$request->isAjax()){

            return ['err'=>1,'msg'=>'访问错误'];
        }

        $id = $request->post('id',0,'int');
        $pid = $request->post('pid',0,'int');
        $name = $request->post('name','','trim');

        if(!$id && !$pid){
            return ['err'=>1,'msg'=>'数据有误'];
        }

        if($id){
            $opt = OptModel::find($id);
            $opt->NAME = $name;
            $opt->save();
            return ['err'=>0,'msg'=>'ok'];
        }

        $count = OptModel::where('PID',$pid)->count();
        $data = [
            'PID'=>$pid,
            'NAME'=>$name,
            'ORIDEID'=>$count
        ];


        $id = (new OptModel())->insertGetId($data);
        if($id>0){
            return ['err'=>0,'msg'=>'ok','id'=>$id];
        }
        return ['err'=>1,'msg'=>'保存数据失败'];
    }

}
