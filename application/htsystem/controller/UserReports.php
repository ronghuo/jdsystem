<?php

namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Request;
use app\common\model\UserUsers;
use app\common\model\UserReports as UserReportsModel;


class UserReports extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $sop = $this->doSearch();
        //todo 加上权限范围条件
        $list = $sop['st']->field('ID,UUID,UNAME,TITLE,ADD_TIME')

            ->paginate(self::PAGE_SIZE, false, [
                'query'=>request()->param(),
            ]);


        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userreports_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('fselect',$this->filterSelect($sop['p']['sok'], [
            'title'=>'标题',
            'uname'=>'报告人',
//            'up_name'=>'上报人',
            'uuid'=>'报告人ID',
//            'dmr_code'=>'编号'
        ]));
        $this->assign('is_so', $sop['is_so']);
        $this->assign('param', $sop['p']);
        return $this->fetch();
    }

    protected function doSearch(){
        $st = null;
        $p = [];
        $soks = ['uuid', 'uname','title'];
        $p['sok'] = input('get.sok','');
        $p['sov'] = input('get.sov','');
        $is_so = false;
        $st = UserReportsModel::where('ISDEL',0);


        if($p['sov'] && $p['sok'] && in_array($p['sok'],$soks)){

            if(in_array($p['sok'] ,['uuid'])){
                $st->where(strtoupper($p['sok']), '=', $p['sov']);
            }else{
                $st->where(strtoupper($p['sok']), 'like', '%'.$p['sov'].'%');
            }

            $is_so = true;
        }



        $p['a1'] = input('area1', '');
        $p['a2'] = input('area2', '');
        $p['a3'] = input('area3', '');

        if($p['a1'] > 0 || $p['a2'] > 0 || $p['a3'] > 0){
            $is_so = true;

            $soids = UserUsers::field('ID')->where('ISDEL', 0)
                ->where(function ($q) use($p){
                    //县
                    if($p['a1'] >0){
                        $q->where('COUNTY_ID_12', $p['a1']);
                    }
                    //乡
                    if($p['a2']>0){
                        $q->where('STREET_ID', $p['a2']);
                    }
                    //村级
                    if($p['a3']>0){
                        $q->where('COMMUNITY_ID', $p['a3']);
                    }
                })->select()->column('ID');

//        var_dump($soids);
            $st->where(function ($query)use($soids, $is_so){
                if($is_so && $soids){
                    $query->whereIn('UUID', $soids);
                }elseif($is_so && !$soids){
                    $query->where('UUID', 0);
                }
            else{
                $muids = $this->getManageUUids();
                if($muids != 'all'){
                    $query->whereIn('UUID', $muids);
                }
            }

            });
        }



        return ['st'=>$st,'p'=>$p,'is_so'=>$is_so];
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
        //todo 加上权限范围条件
        $info = UserReportsModel::where('ISDEL','=',0)

            ->find($id);
        if(!$info){
            $this->error('该报告事项不存在或已删除');
        }

        if(!$this->checkUUid($info->UUID)){
            $this->error('权限不足');
        }
        $info->IMGS->map(function($t){
            $t->SRC_PATH_URL = build_http_img_url($t->SRC_PATH);
        });

        $this->assign('info',$info);
        return $this->fetch();
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
        //todo 加上权限范围条件

        $info = UserReportsModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该报告事项不存在或已删除');
        }
        if(!$this->checkUUid($info->UUID)){
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        $this->success('删除成功');
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
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}
