<?php
namespace app\htsystem\controller;

use app\common\model\DecisionImgs;
use app\common\model\UserRecoveryPlan as UserRecoveryPlanModel;
use app\common\model\UserUsers as UserUsersModel;
use Carbon\Carbon;
use think\Request;

class UserRecoveryPlan extends Common {

    protected $admin_log_target_type = 'UserRecoveryPlan';

    const USER_LIVING_STATUSES = [
        'SCHOOL' => '就学',
        'HOSPITAL' => '就医',
        'EMPLOYMENT' => '就业'
    ];

    const WHETHER = [
        '0' => '否',
        '1' => '是'
    ];

    public function index($id = 0) {
        if (!$id) {
            $this->error('访问错误');
        }
        $user = UserUsersModel::find($id);

        if (!$user || $user->ISDEL == 1) {
            $this->error('该用户不存在或已删除');
        }

        if (!$this->checkUUid($user->ID)) {
            $this->error('权限不足');
        }

        $list = UserRecoveryPlanModel::where('UUID', $user->ID)->order('CREATE_TIME DESC')->select()
        ->map(function ($item) {
            $item->CURRENT_STATUS = empty(self::USER_LIVING_STATUSES[$item->CURRENT_STATUS]) ?
                $item->CURRENT_STATUS : self::USER_LIVING_STATUSES[$item->CURRENT_STATUS];
            $item->WHETHER_MEDICHINE_ENCOURAGED = isset(self::WHETHER[$item->WHETHER_MEDICHINE_ENCOURAGED]) ?
                self::WHETHER[$item->WHETHER_MEDICHINE_ENCOURAGED] : $item->WHETHER_MEDICHINE_ENCOURAGED;
            $item->WHETHER_DETOXIFICATION_REQUIRED = isset(self::WHETHER[$item->WHETHER_DETOXIFICATION_REQUIRED]) ?
                self::WHETHER[$item->WHETHER_DETOXIFICATION_REQUIRED] : $item->WHETHER_DETOXIFICATION_REQUIRED;
            return $item;
        });
        $this->assign('user', $user);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function create(Request $request,$id = 0){
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
            return $this->saveDecision($request, $user);
        }
        $info = [];

        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'decisions_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('user', $user);
        $this->assign('info', $info);
        return $this->fetch();
    }

    protected function saveDecision(Request $request, UserUsersModel $user) {
        if (!$request->post('CONTENT')) {
            $this->error('请填写决定书内容');
        }

        $decision = new UserDecisionsModel();
        $decision->UUID = $user->ID;
        $decision->CONTENT = $request->post('CONTENT');
        $decision->UPDATE_TIME = Carbon::now()->toDateTimeString();

        $decision->save();

        // 过滤出当前协议内容中包含的图片路径信息
        $images = [];
        getImagesFromUEditor($decision->CONTENT, $images);
        if (!empty($images)) {
            (new DecisionImgs())->saveData($decision->ID, $images);
        }

        $log_content = '决定书内容：' . $decision->CONTENT;
        $this->addAdminLog(self::OPER_TYPE_CREATE,'新增决定书', $log_content, $user->ID);

        $this->success('保存成功', url('UserDecisions/index', ['id' => $user->ID]));
    }

    public function delete($uuid = 0, $decisionId = 0) {
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

        $decision = UserDecisionsModel::find($decisionId);
        if (empty($decision)) {
            $this->error('该决定书已删除');
        }
        $decision->delete();

        DecisionImgs::where('USER_DECISIONS_ID', $decisionId)->delete();

        $this->addAdminLog(self::OPER_TYPE_DELETE, '删除决定书', '决定书删除成功', $uuid);

        $this->success('删除成功');
    }
}