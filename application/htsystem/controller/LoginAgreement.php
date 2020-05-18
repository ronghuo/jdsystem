<?php
namespace app\htsystem\controller;

use app\common\model\Agreement;
use app\common\model\AgreementImgs;
use app\common\model\LoginAgreement as LoginAgreementModel;
use app\common\model\UserUsers as UserUsersModel;
use Carbon\Carbon;
use think\Request;

class LoginAgreement extends Common {

    protected $MODULE = 'LoginAgreement';

    public function index() {
        $list = LoginAgreementModel::all()->toArray();
        if (!empty($list)) {
            $info = $list[0];
        }
        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'login_agreement_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', empty($info) ? null : $info);
        return $this->fetch();
    }

    public function create(Request $request, $id = 0) {
        if ($request->isPost()) {
            return $this->saveAgreement($request);
        }
        if (empty($id)) {
            $list = LoginAgreementModel::all()->toArray();
            if (!empty($list)) {
                $info = $list[0];
            }
        } else {
            $info = LoginAgreementModel::find($id);
        }

        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'login_agreement_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', empty($info) ? null : $info);
        return $this->fetch();
    }

    protected function saveAgreement(Request $request) {

        if (!$request->post('CONTENT')) {
            $this->error('请填写协议内容');
        }

        $id = $request->post('ID', 0, 'int');

        $admin = session('info');
        $data = [
            'TITLE' => $request->post('TITLE', '', 'trim'),
            'CONTENT' => $request->post('CONTENT', '', 'trim'),
            'UPDATE_USER_ID' => $admin['USER_ID'],
            'UPDATE_USER_NAME' => $admin['NAME'],
            'UPDATE_TIME' => Carbon::now()->toDateTimeString()
        ];

        if (empty($id)) {
            $data['CREATE_USER_ID'] = $admin['USER_ID'];
            $data['CREATE_USER_NAME'] = $admin['NAME'];
            $agreement = new LoginAgreementModel();
        } else {
            $agreement = LoginAgreementModel::find($id);
        }
        $agreement->save($data);

        $this->success('保存成功', url('LoginAgreement/index'));
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