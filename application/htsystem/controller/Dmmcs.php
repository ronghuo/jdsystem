<?php

namespace app\htsystem\controller;

use app\common\library\JqueryCateData;
use app\common\library\StaticCache;
use app\common\model\NbAuthDept;
use think\Request;

class Dmmcs extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $area1 = input('get.area1',10074);
        $area2 = input('get.area2','');
        $area3 = input('get.area3','');
        $area4 = input('get.area4','');

        if($area4){
            $pid = $area4;
        }elseif($area3){
            $pid = $area3;
        }elseif($area2){
            $pid = $area2;
        }
        elseif($area1){
            $pid = $area1;
        }


        $all = NbAuthDept::field('ID, PARENTDEPTID as PID, DEPTCODE, DEPTNAME as NAME,DEPTDESC,FLAG')
            ->all();
        $trees = create_level_tree($all,$pid);

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'dmmcs_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('trees', $trees);
        $this->assign('area1', $area1);
        $this->assign('area2', $area2);
        $this->assign('area3', $area3);
        $this->assign('area4', $area4);
        $this->assign('is_so', false);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
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

        $info = NbAuthDept::find($id);
        if(!$info){
            $this->error('该机构不存在');
        }

        if($request->isPost()){
            $this->update($request, $info);
        }

        $js = $this->loadJsCss(array( 'dmmcs_edit'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        return $this->fetch('create');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  NbAuthDept  $info
     * @return \think\Response
     */
    protected function update(Request $request, NbAuthDept $info)
    {
        $name = $request->post('DEPTNAME', '');
        $desc = $request->post('DEPTDESC', '');
        $flag = $request->post('FLAG', 0);

        if(!$name){
            $this->error('请填写机构名称');
        }


        $info->DEPTNAME = $name;
        $info->DEPTDESC = $desc;
        $info->FLAG = $flag;
        $info->save();

        //更新前端插件用的json文件
        JqueryCateData::createDmmcJson();

        //更新js缓存
        $file_path = config('cache_version_file');
        StaticCache::refresh($file_path);

        $this->jsalert('编辑成功',3);
        return ;

    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

}
