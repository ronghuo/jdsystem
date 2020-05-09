<?php
namespace app\htsystem\controller;

use app\common\library\Uedit;
use app\common\model\Agreement;
use app\common\model\AgreementImgs;
use app\common\model\UserEstimates as UserEstimatesModel;
use app\common\model\UserUsers as UserUsersModel;
use Carbon\Carbon;
use think\facade\Cache;
use think\Request;

class UserAgreements extends Common {

    protected $admin_log_target_type = 'UserAgreements';

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

        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'userusers_agreement'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('user', $user);
        $this->assign('info', $info);
        return $this->fetch();
    }

    protected function saveAgreement(Request $request, UserUsersModel $user){

        if (!$request->post('CONTENT')) {
            $this->error('请填写康复内容');
        }

        $agreement = new Agreement();
        $agreement->UUID = $user->ID;
        $agreement->TITLE = $request->post('TITLE', '', 'trim');
        $agreement->CONTENT = $request->post('CONTENT', '', 'trim');
        $agreement->UPDATE_TIME = Carbon::now()->toDateTimeString();

        $agreement->save();

        // 从Redis中取出UEditor编辑器上传的图片路径集合，并过滤出当前协议内容中包含的图片路径信息
        if (Cache::has(Uedit::CACHE_UEDITOR_IMAGE)) {
            $images = json_decode(Cache::get(Uedit::CACHE_UEDITOR_IMAGE));
            $filteredImages = [];
            foreach ($images as $image) {
                if (!strpos($agreement->CONTENT, $image)) {
                    continue;
                }
                array_push($filteredImages, $image);
            }
            (new AgreementImgs())->saveData($agreement->ID, $filteredImages);
        }

        $log_content = '协议标题：' . $agreement->TITLE . '<br/>' . '协议内容：' . $agreement->CONTENT;
        $this->addAdminLog(self::OPER_TYPE_CREATE, '新增社戒社康协议', $log_content, $user->ID);

        $this->success('保存成功',url('UserUsers/index'));
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