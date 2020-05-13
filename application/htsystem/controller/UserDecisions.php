<?php
namespace app\htsystem\controller;

use app\common\model\DecisionImgs;
use app\common\model\UserDecisions as UserDecisionsModel;
use app\common\model\UserUsers as UserUsersModel;
use app\common\model\NbAuthDept;
use Carbon\Carbon;
use think\Request;

class UserDecisions extends Common {

    protected $MODULE = 'UserUser';

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

        $list = UserDecisionsModel::where('UUID', $user->ID)->order('ADD_TIME DESC')->select()->map(function ($item) {
            $item->imgs->map(function ($image) {
                return $image;
            });
            return $item;
        });
        $js = $this->loadJsCss(array('p:viewer/viewer.min', 'decisions_index'), 'js', 'admin');
        $css = $this->loadJsCss(array('p:viewer/viewer.min'), 'css');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
        $this->assign('user', $user);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function create(Request $request, $id = 0){
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
        $admin = session('info');
        if (!empty($admin['DMMCIDS'])) {
            $dmmcids = explode(',', $admin['DMMCIDS']);
            $dmmc = NbAuthDept::find(end($dmmcids));
        }
        $decision->UUID = $user->ID;
        $decision->CONTENT = $request->post('CONTENT');
        $decision->ADD_USER_MOBILE = $admin['MOBILE'];
        $decision->ADD_USER_NAME = $admin['NAME'];
        $decision->ADD_DEPT_CODE = empty($dmmc) ? '' : $dmmc->DEPTCODE;
        $decision->ADD_DEPT_NAME = empty($dmmc) ? '' : $dmmc->DEPTNAME;
        $decision->ADD_TERMINAL = TERMINAL_WEB;
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