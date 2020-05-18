<?php
namespace app\htsystem\controller;

use app\common\model\Agreement;
use app\common\model\AgreementImgs;
use app\common\model\NbAuthDept;
use app\common\model\UserUsers as UserUsersModel;
use Carbon\Carbon;
use think\Request;

class UserAgreements extends Common {

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

        $list = Agreement::where('UUID', $user->ID)->order('ADD_TIME DESC')->select()->map(function ($item) {
            $item->images->map(function ($image) {
                return $image;
            });
            return $item;
        });
        $js = $this->loadJsCss(array('p:viewer/viewer.min', 'agreements_index'), 'js', 'admin');
        $css = $this->loadJsCss(array('p:viewer/viewer.min'), 'css');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
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
            return $this->saveAgreement($request, $user);
        }
        $info = [];

        $title = '';
        if ($user->USER_STATUS_NAME == STATUS_COMMUNITY_DETOXIFICATION) {
            $title = '社区戒毒协议 ' . $user->JD_START_TIME . ' 至 ' . $user->JD_END_TIME;
        } else if ($user->USER_STATUS_NAME == STATUS_COMMUNITY_RECOVERING) {
            $title = '社区康复协议 ' . $user->JD_START_TIME . ' 至 ' . $user->JD_END_TIME;
        }
        $info['TITLE'] = $title;

        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'agreements_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('user', $user);
        $this->assign('info', $info);
        return $this->fetch();
    }

    protected function saveAgreement(Request $request, UserUsersModel $user) {

        if (!$request->post('CONTENT')) {
            $this->error('请填写协议内容');
        }

        $agreement = new Agreement();
        $admin = session('info');
        if (!empty($admin['DMMCIDS'])) {
            $dmmcids = explode(',', $admin['DMMCIDS']);
            $dmmc = NbAuthDept::find(end($dmmcids));
        }
        $agreement->UUID = $user->ID;
        $agreement->TITLE = $request->post('TITLE', '', 'trim');
        $agreement->CONTENT = $request->post('CONTENT', '', 'trim');
        $agreement->ADD_USER_MOBILE = $admin['MOBILE'];
        $agreement->ADD_USER_NAME = $admin['NAME'];
        $agreement->ADD_DEPT_CODE = empty($dmmc) ? '' : $dmmc->DEPTCODE;
        $agreement->ADD_DEPT_NAME = empty($dmmc) ? '' : $dmmc->DEPTNAME;
        $agreement->ADD_TERMINAL = TERMINAL_WEB;
        $agreement->UPDATE_TIME = Carbon::now()->toDateTimeString();

        $agreement->save();

        // 过滤出当前协议内容中包含的图片路径信息
        $images = [];
        getImagesFromUEditor($agreement->CONTENT, $images);
        if (!empty($images)) {
            (new AgreementImgs())->saveData($agreement->ID, $images);
        }

        $log_content = '协议标题：' . $agreement->TITLE . '<br/>' . '协议内容：' . $agreement->CONTENT;
        $this->addAdminLog(self::OPER_TYPE_CREATE, '新增社戒社康协议', $log_content, $user->ID);

        $this->success('保存成功',url('UserAgreements/index', ['id' => $user->ID]));
    }

    public function delete($uuid = 0, $agreementId = 0) {
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

        $agreement = Agreement::find($agreementId);
        if (empty($agreement)) {
            $this->error('该协议已删除');
        }
        $agreement->delete();

        AgreementImgs::where('AGREEMENT_ID', $agreementId)->delete();

        $this->addAdminLog(self::OPER_TYPE_DELETE, '删除社戒社康协议', '社戒社康协议删除成功', $uuid);

        $this->success('删除成功');
    }
}