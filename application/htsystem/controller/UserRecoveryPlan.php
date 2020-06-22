<?php
namespace app\htsystem\controller;

use app\common\model\BaseSexType;
use app\common\model\NbAuthDept;
use app\common\model\UserRecoveryPlan as UserRecoveryPlanModel;
use app\common\model\UserUsers as UserUsersModel;
use Carbon\Carbon;
use think\Request;

class UserRecoveryPlan extends Common {

    protected $MODULE = 'UserUser';

    const USER_LIVING_STATUSES = [
        'SCHOOL' => '就学',
        'HOSPITAL' => '就医',
        'EMPLOYMENT' => '就业'
    ];

    const WHETHER = [
        '0' => '否',
        '1' => '是'
    ];

    /**
     * 康复计划字段名称-注解映射器
     */
    const RECOVERY_PLAN_FIELD_NAME_DESC_MAPPER = [
        'NAME' => '姓名',
        'GENDER' => '性别',
        'ID_NUMBER' => '证件号码',
        'MOBILE' => '手机号码',
        'DOMICILE_PLACE' => '户籍所在地',
        'LIVE_PLACE' => '现居住地址',
        'BEGIN_DATE' => '社区戒毒(康复)时间(起)',
        'END_DATE' => '社区戒毒(康复)时间(止)',
        'FAMILY_MEMBERS' => '家庭成员',
        'DRUG_HISTORY_AND_TREATMENT' => '吸毒史及治疗情况',
        'CURRENT_STATUS' => '当前情况',
        'CURE_MEASURES' => '应采取的戒毒治疗措施',
        'WHETHER_MEDICHINE_ENCOURAGED' => '是否动员参加药物维持治疗情况',
        'WHETHER_DETOXIFICATION_REQUIRED' => '是否需要戒毒治疗',
        'PSYCHOLOGICAL_CONSULTING_PLAN' => '心理咨询疏导计划',
        'ASSISTANCE_MEASURES' => '拟采取帮扶救助措施',
        'COMMUNITY_NAME' => '社区名称',
        'SIGN_DATE' => '落款日期'
    ];

    public function index($uuid = 0) {
        if (!$uuid) {
            $this->error('访问错误');
        }
        $user = UserUsersModel::find($uuid);

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

    public function create(Request $request, $uuid = 0){
        if (!$uuid) {
            $this->error('访问错误');
        }

        $user = UserUsersModel::find($uuid);

        if(!$user || $user->ISDEL==1) {
            $this->error('该用户不存在或已删除');
        }

        if(!$this->checkUUid($user->ID)){
            $this->error('权限不足');
        }

        if($request->isPost()){
            return $this->saveRecoveryPlan($request);
        }
        $plan = new UserRecoveryPlanModel();
        $plan->UUID = $uuid;
        $plan->NAME = $user->NAME;
        $plan->GENDER = $user->GENDER;
        $plan->ID_NUMBER = $user->ID_NUMBER;
        $plan->MOBILE = $user->MOBILE;
        $plan->DOMICILE_PLACE = $user->DOMICILE_PLACE . ' ' . $user->DOMICILE_ADDRESS;
        $plan->LIVE_PLACE = $user->LIVE_PLACE . ' ' . $user->LIVE_ADDRESS;
        $plan->BEGIN_DATE = $user->JD_START_TIME;
        $plan->END_DATE = $user->JD_END_TIME;

        $this->assign('plan', $plan);
        $css = $this->loadJsCss(array('recoveryplan_create'), 'css', 'admin');
        $this->assign('headercss', $css);
        $this->assign('genders', BaseSexType::all());
        $this->assign('statuses', self::USER_LIVING_STATUSES);
        $this->assign('whether', self::WHETHER);
        $this->assign('title', '新增');
        $this->assign('action', 'create');
        return $this->fetch();
    }

    public function edit(Request $request, $uuid = 0, $planId = 0){
        if (!$uuid) {
            $this->error('访问错误');
        }

        $user = UserUsersModel::find($uuid);

        if(!$user || $user->ISDEL==1) {
            $this->error('该用户不存在或已删除');
        }

        if(!$this->checkUUid($user->ID)){
            $this->error('权限不足');
        }

        if ($request->isPost()) {
            return $this->saveRecoveryPlan($request);
        }
        if (!$planId) {
            $this->error('请求参数错误');
        }
        $plan = UserRecoveryPlanModel::find($planId);

        $this->assign('plan', $plan);
        $css = $this->loadJsCss(array('recoveryplan_create'), 'css', 'admin');
        $this->assign('headercss', $css);
        $this->assign('genders', BaseSexType::all());
        $this->assign('statuses', self::USER_LIVING_STATUSES);
        $this->assign('whether', self::WHETHER);
        $this->assign('title', '修改');
        $this->assign('action', 'edit');
        return $this->fetch('create');
    }

    protected function saveRecoveryPlan(Request $request) {
        $uuid = $request->post('UUID');
        $data = [
            'UUID' => $uuid,
            'NAME' => $request->post('NAME'),
            'GENDER' => ifEmptyThenNull($request->post('GENDER')),
            'ID_NUMBER' => $request->post('ID_NUMBER'),
            'MOBILE' => $request->post('MOBILE'),
            'DOMICILE_PLACE' => $request->post('DOMICILE_PLACE'),
            'LIVE_PLACE' => $request->post('LIVE_PLACE'),
            'BEGIN_DATE' => ifEmptyThenNull($request->post('BEGIN_DATE')),
            'END_DATE' => ifEmptyThenNull($request->post('END_DATE')),
            'FAMILY_MEMBERS' => $request->post('FAMILY_MEMBERS'),
            'DRUG_HISTORY_AND_TREATMENT' => $request->post('DRUG_HISTORY_AND_TREATMENT'),
            'CURRENT_STATUS' => $request->post('CURRENT_STATUS'),
            'CURE_MEASURES' => $request->post('CURE_MEASURES'),
            'WHETHER_MEDICHINE_ENCOURAGED' => ifEmptyThenNull($request->post('WHETHER_MEDICHINE_ENCOURAGED')),
            'WHETHER_DETOXIFICATION_REQUIRED' => ifEmptyThenNull($request->post('WHETHER_DETOXIFICATION_REQUIRED')),
            'PSYCHOLOGICAL_CONSULTING_PLAN' => $request->post('PSYCHOLOGICAL_CONSULTING_PLAN'),
            'ASSISTANCE_MEASURES' => $request->post('ASSISTANCE_MEASURES'),
            'COMMUNITY_NAME' => $request->post('COMMUNITY_NAME'),
            'SIGN_DATE' => ifEmptyThenNull($request->post('SIGN_DATE')),

            'UPDATE_TIME' => Carbon::now()->toDateTimeString()
        ];
        $id = $request->post('ID');
        if (!empty($id)) {
            $isNew = false;
            $plan = UserRecoveryPlanModel::find($id);
        } else {
            $isNew = true;
            $plan = new UserRecoveryPlanModel();
            $admin = session('info');
            if (!empty($admin['DMMCIDS'])) {
                $dmmcids = explode(',', $admin['DMMCIDS']);
                $dmmc = NbAuthDept::find(end($dmmcids));
            }
            $data['CREATE_USER_MOBILE'] = $admin['MOBILE'];
            $data['CREATE_USER_NAME'] = $admin['NAME'];
            $data['CREATE_TIME'] = Carbon::now()->toDateTimeString();
            $data['CREATE_DEPT_CODE'] = empty($dmmc) ? '' : $dmmc->DEPTCODE;
            $data['CREATE_DEPT_NAME'] = empty($dmmc) ? '' : $dmmc->DEPTNAME;
            $data['CREATE_TERMINAL'] = TERMINAL_WEB;
        }
        $plan->save($data);

        if ($isNew) {
            $log_oper_Name = '新增康复计划';
            $log_content = '新增康复计划，计划信息如下：' . self::LOG_CONTENT_BREAK;
            $log_oper_type = self::OPER_TYPE_CREATE;
        } else {
            $log_oper_Name = '修改康复计划';
            $log_content = '修改康复计划，计划信息如下：' . self::LOG_CONTENT_BREAK;
            $log_oper_type = self::OPER_TYPE_UPDATE;
        }
        foreach ($data as $name => $value) {
            if (!isset(self::RECOVERY_PLAN_FIELD_NAME_DESC_MAPPER[$name])) {
                continue;
            }
            $log_content .= self::RECOVERY_PLAN_FIELD_NAME_DESC_MAPPER[$name] . '：' . $value . self::LOG_CONTENT_BREAK;
        }
        $this->logAdmin($log_oper_type, $log_oper_Name, $log_content, $uuid);


        $this->success('保存成功.', url('UserRecoveryPlan/index', ['uuid' => $uuid]));
    }

    public function _print($planId = 0) {
        $plan = UserRecoveryPlanModel::find($planId);
        if (empty($plan)) {
            $this->error('康复计划不存在.');
        }
        $uuid = $plan->UUID;
        if (!$this->checkUUid($uuid)) {
            return $this->error('权限不足.');
        }
        $genders = BaseSexType::all();
        foreach ($genders as $gender) {
            if ($gender->ID == $plan->GENDER) {
                $plan->GENDER = $gender->NAME;
                break;
            }
        }
        if (!empty($plan->BEGIN_DATE)) {
            $plan->BEGIN_DATE = date_parse($plan->BEGIN_DATE);
        }
        if (!empty($plan->END_DATE)) {
            $plan->END_DATE = date_parse($plan->END_DATE);
        }
        if (!empty($plan->CURRENT_STATUS)) {
            $plan->CURRENT_STATUS = self::USER_LIVING_STATUSES[$plan->CURRENT_STATUS];
        }
        if (!is_null($plan->WHETHER_MEDICHINE_ENCOURAGED)) {
            $plan->WHETHER_MEDICHINE_ENCOURAGED = self::WHETHER[$plan->WHETHER_MEDICHINE_ENCOURAGED];
        }
        if (!is_null($plan->WHETHER_DETOXIFICATION_REQUIRED)) {
            $plan->WHETHER_DETOXIFICATION_REQUIRED = self::WHETHER[$plan->WHETHER_DETOXIFICATION_REQUIRED];
        }
        if (!empty($plan->SIGN_DATE)) {
            $plan->SIGN_DATE = date_parse($plan->SIGN_DATE);
        }
        $this->assign('plan', $plan);

        $this->logAdmin(self::OPER_TYPE_QUERY, '打印康复计划', '康复计划打印成功', $uuid);

        return $this->fetch('print');
    }

    public function delete($uuid = 0, $planId = 0) {
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

        $recoveryPlan = UserRecoveryPlanModel::find($planId);
        if (empty($recoveryPlan)) {
            $this->error('该康复计划已删除');
        }
        $recoveryPlan->delete();

        $this->logAdmin(self::OPER_TYPE_DELETE, '删除康复计划', '康复计划删除成功', $uuid);

        $this->success('删除成功');
    }
}