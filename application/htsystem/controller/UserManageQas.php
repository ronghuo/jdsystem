<?php

namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Controller;
use think\Request;
use app\common\model\MessageBoardAsks;
use app\common\model\MessageBoardAnswers;

class UserManageQas extends Common
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
        $list = $sop['st']
            ->order('ADD_TIME','DESC')
            ->paginate(self::PAGE_SIZE, false, [
                'query'=>request()->param(),
            ])->each(function($item,$key){

                $item->count_answer = count($item->ANSWERS);

            });


        $js = $this->loadJsCss(array('usermanageqas_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('fselect',$this->filterSelect($sop['p']['sok'], [
            'question'=>'问题',
            'asker_name'=>'留言人',
            'asker_uid'=>'留言人ID',
//            'up_name'=>'上报人',
//            'dmr_code'=>'编号'
        ]));
        $this->assign('is_so', $sop['is_so']);
        $this->assign('param', $sop['p']);
        return $this->fetch();
    }

    protected function doSearch(){
        $st = null;
        $p = [];
        $soks = ['asker_uid', 'question','asker_name'];
        $p['sok'] = input('get.sok','');
        $p['sov'] = input('get.sov','');
        $is_so = false;
        $st = MessageBoardAsks::where('ISDEL',0);


        if($p['sov'] && $p['sok'] && in_array($p['sok'],$soks)){


            if($p['sok']=='asker_uid') {
                $st->where(strtoupper($p['sok']), '=', $p['sov']);
            }else{
                $st->where(strtoupper($p['sok']), 'like', '%'.$p['sov'].'%');
            }
//            }elseif($p['sok']=='area'){
//                $st->where('ADDRESS', 'like', '%'.$p['sov'].'%');
//            }elseif($p['sok']=='title'){
//                $st->where(strtoupper($p['sok']), 'like', '%'.$p['sov'].'%');
//            }

            $is_so = true;
        }

//        $p['a1'] = input('area1', '');
//        $p['a2'] = input('area2', '');
//        $p['a3'] = input('area3', '');
//
//        if($p['a1'] > 0){
//            $st->where('COUNTY_ID', $p['a1']);
//            $is_so = true;
//        }
//        if($p['a2'] > 0){
//            $st->where('STREET_ID', $p['a2']);
//            $is_so = true;
//        }
//        if($p['a3'] > 0){
//            $st->where('COMMUNITY_ID', $p['a3']);
//            $is_so = true;
//        }


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
        $info = MessageBoardAsks::where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该留言不存在或已删除');
        }

//        if(!$this->checkUUid($info->ASKER_UID)){
//            $this->error('权限不足');
//        }

        $js = $this->loadJsCss(array('usermanageqas_read'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        return $this->fetch();
    }

    public function reply(Request $request)
    {

        if (!$request->isPost()) {
            $this->error('访问错误');
        }
        //todo 加上权限范围条件

        $askid = $request->param('askid', 0, 'int');

        $info = MessageBoardAsks::find($askid);
        if(!$info || $info->ISDEL==1){
            $this->error('该报告事项不存在或已删除');
        }
        if(!$this->checkUUid($info->ASKER_UID)){
            $this->error('权限不足');
        }

        $content = $request->param('content', '', 'trim');
        if (!$content) {
            $this->error('请填写回复内容');
        }
        if (!$askid) {
            $this->error('缺少问题信息');
        }

        $data = [
            'ASKID' => $askid,
            'ASKER_UID'=>$info->ASKER_UID,
            'ANSWERER_UID' => session('user_id'),
            'ANSWERER_NAME' => session('name'),
            'CONTENT' => $content,
        ];

        $res = (new MessageBoardAnswers())->insert($data);
        if ($res) {
            $this->jsalert('回复成功', 3);
        }
        $this->error('提交回复失败');
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
        $info = MessageBoardAsks::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该报告事项不存在或已删除');
        }
        if(!$this->checkUUid($info->ASKER_UID)){
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
