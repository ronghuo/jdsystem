<?php
namespace app\htsystem\controller;

use think\Request;
use app\common\model\UserEstimates as EstimateModel;
use app\common\model\BaseUserDangerLevel;
use app\common\model\UserUsers as UserUsersModel;
use app\common\model\UserEstimates as UserEstimatesModel;
use Carbon\Carbon;

class UserEstimates extends Common {

    protected $MODULE = 'UserUser';

    public function index($id=0){
        if(!$id){
            $this->error('访问错误');
        }
        $user = UserUsersModel::where(function ($query){
            $ids = $this->getManageUUids();
            if($ids != 'all'){
                $query->whereIn('ID', $ids);
            }
        })->find($id);

        if(!$user || $user->ISDEL==1){
            $this->error('该用户不存在或已删除');
        }
        $dangeLevels = create_kv(BaseUserDangerLevel::all()->toArray(), 'ID', 'NAME');

        $list = EstimateModel::where('UUID',$user->ID)->order('ADD_TIME', 'desc')
            ->select()->map(function($item) use ($dangeLevels){
            $item->danger_level = $dangeLevels[$item->DANGER_LEVEL_ID] ?? '';
            return $item;
        });


        $this->assign('user', $user);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function create(Request $request,$id=0, $esid=0){
        if (!$id) {
            $this->error('访问错误');
        }

        $user = UserUsersModel::find($id);

        if(!$user || $user->ISDEL==1) {
            $this->error('该用户不存在或已删除');
        }

        if(!$this->checkUUid($user->ID)){
            $this->error('权限不足');
        }

        if($request->isPost()){
            return $this->saveEstimate($request,$user);
        }
        $info = [];

        $js = $this->loadJsCss(array('p:ueditor/ueditor','userusers_estimate'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('user', $user);
        $this->assign('info', $info);
        $this->assign('danger_level',BaseUserDangerLevel::all());
        return $this->fetch();
    }



    protected function saveEstimate(Request $request,UserUsersModel $user){
        $id = $request->post('ID');
        $content = $request->post('CONTENT');
        if(empty($content)){
            $this->error('请填写内容');
        }
        if (empty(isImgInUeditor($content))) {
            $this->error('内容中必须包含图片');
        }

        if ($id > 0) {
            $isNew = false;
            $estimate = EstimateModel::where('ID', $id)->where('UUID',$user->ID)->find();
            if(!$this->checkUUid($estimate->UUID)){
                $this->error('权限不足');
            }
        } else {
            $isNew = true;
            $estimate = new EstimateModel();
            $estimate->CREATE_USER_ID = session('user_id');
            $estimate->CREATE_USER_NAME = session('name');
        }
        $add_time = $request->post('ADD_TIME');
        $estimate->UUID = $user->ID;
        $estimate->DANGER_LEVEL_ID = $request->post('DANGER_LEVEL_ID');
        $estimate->TITLE = $user->NAME.'风险评估';
        $estimate->CONTENT = $content;
        $estimate->ADD_TIME = !empty($add_time) ? date('Y-m-d H:i:s', strtotime($add_time)) : Carbon::now()->toDateTimeString();
        $estimate->UPDATE_TIME = Carbon::now()->toDateTimeString();

        $estimate->save();

        //reset user DANGER_LEVEL_ID
        $last = EstimateModel::where('UUID',$user->ID)->order('ADD_TIME', 'DESC')->find();
        if ($last && $last->DANGER_LEVEL_ID != $user->DANGER_LEVEL_ID) {
            $user->DANGER_LEVEL_ID = $last->DANGER_LEVEL_ID;
            $user->save();
        }

        $log_content = '风险评估详情如下：<br/>' . '标题：' . $estimate->TITLE . '<br/>风险级别：' . $estimate->DANGER_LEVEL_ID . '<br/>内容：' . $estimate->CONTENT;
        $this->logAdmin(
            $isNew ? self::OPER_TYPE_CREATE : self::OPER_TYPE_UPDATE,
            $isNew ? '新增风险评估' : '修改风险评估',
            $log_content,
            $user->ID
        );

        $this->success('保存成功',url('UserEstimates/index', ['id'=>$user->ID]));
    }

    public function delete($uuid = 0, $esid = 0) {
        if (empty($uuid)) {
            $this->error('访问错误');
        }
        $user = UserUsersModel::find($uuid);

        if (empty($user) || $user->ISDEL === 1) {
            $this->error('该用户不存在或已删除');
        }
        if(!$this->checkUUid($uuid)){
            $this->error('权限不足');
        }

        $estimate = UserEstimatesModel::find($esid);
        if (empty($estimate)) {
            $this->error('该评估已删除');
        }
        $estimate->delete();

        $this->logAdmin(self::OPER_TYPE_DELETE, '删除风险评估', '风险评估删除成功', $uuid);

        $this->success('删除成功');
    }
}