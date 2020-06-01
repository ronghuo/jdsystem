<?php

namespace app\htsystem\controller;

use app\common\model\BaseCertificateType;
use app\common\model\BaseNationalityType;
use app\common\model\BaseNationType;
use app\common\model\BaseUserDangerLevel;
use app\common\model\BaseUserStatus;
use app\common\model\NbAuthDept;
use app\common\model\Options as Opts;
use app\common\model\Subareas;
use app\common\model\Upareatable;
use app\common\model\UserChangeLog;
use app\common\model\UserPhoneDataAddress;
use app\common\model\UserPhoneDataCalls;
use app\common\model\UserPhoneDataSms;
use app\common\model\UserUsers as UserUsersModel;
use app\common\model\WaitDeleteFiles;
use app\common\validate\UserUsersVer;
use app\htsystem\model\AdminLogs;
use Carbon\Carbon;
use think\helper\Str;
use think\paginator\driver\Bootstrap;
use think\Request;

/**
 * 康复端人员管理
 * Class UserUsers
 * @package app\htsystem\controller
 */
class UserUsers extends Common
{

    /**
     * 人员状态开始时间对应属性名称
     */
    const STATUS_START_TIME_NAME = 'JD_START_TIME';
    /**
     * 人员状态截止时间对应属性名称
     */
    const STATUS_END_TIME_NAME = 'JD_END_TIME';
    /**
     * 人员二级状态起始时间对应属性名称
     */
    const SUB_STATUS_START_TIME_NAME = 'SUB_STATUS_START_TIME';
    /**
     * 人员二级状态截止时间对应属性名称
     */
    const SUB_STATUS_END_TIME_NAME = 'SUB_STATUS_END_TIME';

    /**
     * 人员状态关联信息
     */
    const STATUS_RELATIONS = [
        '社区戒毒中' => [self::STATUS_START_TIME_NAME => '戒毒开始时间'],
        '社区康复中' => [self::STATUS_START_TIME_NAME => '康复开始时间'],
        '自愿戒毒中' => [self::STATUS_START_TIME_NAME => '自愿戒毒开始时间', 'executePlace' => '自愿戒毒执行地点'],
        '强制戒毒中' => [self::STATUS_START_TIME_NAME => '强制戒毒起始时间', self::STATUS_END_TIME_NAME => '强制戒毒截止时间', 'executePlace' => '强制戒毒执行地点'],
        '未报到未移交' => [],
        '未报到已移交' => [self::STATUS_START_TIME_NAME => '移交时间', 'wbdyyjGJS' => '告诫书', 'wbdyyjYQWBDZM' => '逾期未报到证明', 'wbdyyjYJHZ' => '移交回执'],
        '违反协议未移交' => [],
        '违反协议已移交' => [self::STATUS_START_TIME_NAME => '移交时间', 'wfxyyyjGJS' => '告诫书', 'wfxyyyjXDJCTZS' => '吸毒检测通知书', 'wfxyyyjYZWFXYZM' => '严重违反协议证明', 'wfxyyyjYJHZ' => '移交回执'],
        '社会面' => [],
        '戒断三年未复吸' => [],
        '出国中' => [self::STATUS_START_TIME_NAME => '出国时间', 'country' => '国家名称'],
        '社戒社康终止' => [self::STATUS_START_TIME_NAME => '终止时间'],
        '羁押拘留中' => [self::STATUS_START_TIME_NAME => '拘留起始时间', self::STATUS_END_TIME_NAME => '拘留截止时间', 'detainPlace' => '拘留地点'],
        '已死亡' => [self::STATUS_START_TIME_NAME => '死亡时间']
    ];

    /**
     * 人员二级状态关联信息
     */
    const SUB_STATUS_RELATIONS = [
        '请假中' => [self::SUB_STATUS_START_TIME_NAME => '请假起始时间', self::SUB_STATUS_END_TIME_NAME => '请假截止时间'],
        '中止' => [self::SUB_STATUS_START_TIME_NAME => '中止起始时间', self::SUB_STATUS_END_TIME_NAME => '中止截止时间', 'suspendZZCXSM' => '中止程序说明', 'suspendReason' => '终止原因'],
        '双向管控中' => [self::SUB_STATUS_START_TIME_NAME => '双向管控开始时间', 'SXGKH' => '双向管控函', 'sxgkReason' => '双向管控原因'],
        '已解除社区戒毒' => [self::SUB_STATUS_START_TIME_NAME => '解除时间', 'JCS' => '解除书'],
        '已解除社区康复' => [self::SUB_STATUS_START_TIME_NAME => '解除时间', 'JCS' => '解除书']
    ];

    /**
     * 人员状态文件类型的关联信息
     */
    const STATUS_FILE_RELATIONS = [
        'wbdyyjGJS',
        'wbdyyjYQWBDZM',
        'wbdyyjYJHZ',
        'wfxyyyjGJS',
        'wfxyyyjXDJCTZS',
        'wfxyyyjYZWFXYZM',
        'wfxyyyjYJHZ',
        'suspendZZCXSM',
        'SXGKH',
        'JCS'
    ];

    /**
     * 人员相关信息变化日志类型
     */
    const CHANGE_LOG_TYPE_STATUS = 1;   // 状态
    const CHANGE_LOG_TYPE_URINE = 2;    // 尿检
    const CHANGE_LOG_TYPE_ASSIGN = 3;   // 指派

    const LOG_PAGE_SIZE = 20;

    /**
     * 用于人员图片信息作回显标识
     */
    const URI_FLAG = '_uri';

    const ASSIGN_TYPE_ASSIGN = 1;   // 指派
    const ASSIGN_TYPE_ARCHIVE = 2;  // 建档
    const ASSIGN_TYPE_RELIEVE = 3;  // 解除

    /**
     * 康复人员字段名称-注解映射
     */
    const USER_FIELD_NAME_DESC_MAPPER = [
        'NAME' => '姓名',
        'ALIAS_NAME' => '绰号',
        'ID_NUMBER' => '证件号码',
        'ID_NUMBER_TYPE' => '证件类型',
        'MOBILE' => '手机号码',
        'GENDER' => '性别',
        'JD_START_TIME' => '社戒(社康)起始时间',
        'JD_END_TIME' => '社戒(社康)截止时间',
        'USER_STATUS_NAME' => '人员状态',
        'USER_SUB_STATUS_NAME' => '人员二级状态',
        'DANGER_LEVEL_ID' => '风险级别',
        'NATIONALITY' => '国籍',
        'NATION' => '民族',
        'HEIGHT' => '身高',
        'EDUCATION' => '文化程度',
        'JOB_STATUS' => '就业状态',
        'JOB_UNIT' => '工作单位',
        'MARITAL_STATUS' => '婚姻状况',
        'DOMICILE_ADDRESS' => '户籍地详细地址',
        'DOMICILE_POLICE_STATION' => '户籍地派出所名称',
        'DOMICILE_POLICE_STATION_CODE' => '户籍地派出所代码',
        'LIVE_ADDRESS' => '居住地详细地址',
        'LIVE_POLICE_STATION' => '居住地派出所名称',
        'LIVE_POLICE_STATION_CODE' => '居住地派出所代码',
        'DRUG_TYPE' => '吸毒方式',
        'NARCOTICS_TYPE' => '毒品种类',
        'PROVINCE_ID' => '管辖省份',
        'CITY_ID' => '管辖市',
        'COUNTY_ID_12' => '管辖县(区)',
        'STREET_ID' => '管辖乡镇(街道)',
        'COMMUNITY_ID' => '管辖村(社区)',
        'DOMICILE_PLACE' => '户籍地',
        'LIVE_PLACE' => '居住地',
        'MANAGE_POLICE_AREA_CODE' => '管辖警务区代码',
        'MANAGE_POLICE_AREA_NAME' => '管辖警务区名称',
        'MANAGE_COMMUNITY' => '管辖社区',
        'POLICE_LIABLE_CODE' => '责任民警警号',
        'POLICE_LIABLE_NAME' => '责任民警姓名',
        'POLICE_LIABLE_MOBILE' => '责任民警联系电话',
        'JD_ZHUANGAN' => '负责专干',
        'JD_ZHUANGAN_MOBILE' => '负责专干电话',
        'JD_REMARKS' => '备注'
    ];

    const STATISTICS_EXCEL_TITLE_LIST = [
        '县市区',
        '乡镇街道',
        '村级社区'
    ];

    /**
     * 默认县市区（市辖区），市辖区代表市级，且非“怀化市”区域均归为“市辖区”
     */
    const DEFAULT_COUNTY_ID = '431201000000';

    const YEAR_NUMBER = "第%s年";

    protected $MODULE = 'UserUser';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request, $zhipai = '')
    {
        $result = $this->doSearch($zhipai);
        $is_so = $result['is_so'];
        $title = $result['title'];
        $rows = $result['rows'];
        $params = $result['params'];
        $statusList = create_kv(BaseUserStatus::all(), 'ID', 'NAME');
        $statusList[UserUsersModel::STATUS_ID_OTHER] = UserUsersModel::STATUS_NAME_OTHER;

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userusers_index'), 'js', 'admin');
        $css = $this->loadJsCss(array('userusers_index'), 'css', 'admin');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
        $this->assign('list', $rows);
        $this->assign('page', $rows->render());
        $this->assign('total', $rows->total());
        $this->assign('keywords', $params['keywords']);
        $this->assign('is_so', $is_so);
        $this->assign('title', $title);
        $this->assign('area1', $params['area1']);
        $this->assign('area2', $params['area2']);
        $this->assign('area3', $params['area3']);
        $powerLevel = $this->getPowerLevel();
        // 只有市级及县市区级有删除吸毒人员的权限
        $this->assign('remove_allowed', in_array($powerLevel, [self::POWER_LEVEL_CITY, self::POWER_LEVEL_COUNTY]));
        $this->assign('powerLevel', $powerLevel);
        $this->assign('userStatus', $params['userStatus']);
        $this->assign('user_status_list', $statusList);
        $this->assign('estimate', $params['estimate']);
        $this->assign('estimate_list', UserUsersModel::ESTIMATE_DANGER_LEVEL_LIST);

        $this->addAdminLog(self::OPER_TYPE_QUERY, '康复人员列表');

        return $this->fetch('index');
    }

    protected function doSearch($zhipai = '') {

        $is_so = false;

        $query = UserUsersModel::where('ISDEL',0)->where(function($query) {
            $query->where('COUNTY_ID_12', 0)->whereOr('COUNTY_ID_12', 'in', function($query) {
                $query->table('subareas')->field('CODE12');
            });
        })->alias('A');

        $fields = ['ID', 'UUCODE', 'NAME', 'MOBILE', 'ID_NUMBER', 'LIVE_PLACE', 'LIVE_ADDRESS', 'DOMICILE_PLACE', 'DOMICILE_ADDRESS'];
        $keywords = input('get.keywords','');
        if (!empty($keywords)) {
            $query->where(implode('|', $fields), 'like', "%$keywords%");
            $is_so = true;
        }

        $userStatus = input('get.userStatus', '');
        if ($userStatus != '') {
            $statusIdList = array_column(BaseUserStatus::all()->toArray(), 'ID');
            if ($userStatus == UserUsersModel::STATUS_ID_OTHER) {
                $query->whereNotIn('USER_STATUS_ID', $statusIdList);
            }
            else if (in_array($userStatus, $statusIdList)) {
                $query->where('USER_STATUS_ID', $userStatus);
            }
            $is_so = true;
        }

        $powerLevel = $this->getPowerLevel();
        $admin = session('info');
        if (self::POWER_LEVEL_COUNTY == $powerLevel) {
            $area1 = $admin['POWER_COUNTY_ID_12'];
            $area2 = input('area2', '');
            $area3 = input('area3', '');
        }
        elseif (self::POWER_LEVEL_STREET == $powerLevel) {
            $area1 = $admin['POWER_COUNTY_ID_12'];
            $area2 = $admin['POWER_STREET_ID'];
            $area3 = input('area3', '');
        }
        elseif (self::POWER_LEVEL_COMMUNITY == $powerLevel) {
            $area1 = $admin['POWER_COUNTY_ID_12'];
            $area2 = $admin['POWER_STREET_ID'];
            $area3 = $admin['POWER_COMMUNITY_ID'];
        } else {
            $area1 = input('area1', '');
            $area2 = input('area2', '');
            $area3 = input('area3', '');
        }

        if ($area1 > 0) {
            $query->where('COUNTY_ID_12', $area1);
            $is_so = true;
        }
        if ($area2 > 0) {
            $query->where('STREET_ID', $area2);
            $is_so = true;
        }
        if ($area3 > 0) {
            $query->where('COMMUNITY_ID', $area3);
            $is_so = true;
        }

        $title = '康复人员';

        switch($zhipai) {
            //未指派的社戒社康⼈人员
            case 'unzhipai':
                $query->where('COMMUNITY_ID', 0);
                $title = '未指派的社戒社康人员';
                break;
            //未完全指派的社戒社康⼈人员
            case 'unzhipai2':
                $query->where('COMMUNITY_ID', '>', 0)->where('JD_ZHI_PAI_ID', 0);
                $title = '未完全指派的社戒社康人员';
                break;
            //已完全指派的社戒社康⼈人员
            case 'zhipai':
                $query->where('COMMUNITY_ID', '>', 0)->where('JD_ZHI_PAI_ID', 1);
                $title = '已完全指派的社戒社康人员';
                break;
            //解除社戒社康⼈人员
            case 'jiechu':
                $query->where('JD_ZHI_PAI_ID', 2);
                $title = '解除社戒社康人员';
                break;
        }

        $estimate = input('estimate', '');
        if ($estimate != '') {
            $subQuery = db()->table('user_estimates')->order('add_time desc')->buildSql();
            $subQuery = db()->table("$subQuery C")->group('UUID')->buildSql();
            $query->leftJoin("$subQuery B", 'A.ID = B.UUID');
            if ($estimate == 0) {
                $query->where(function ($query) {
                    $dangerLevelIds = array_keys(UserUsersModel::ESTIMATE_DANGER_LEVEL_LIST);
                    array_shift($dangerLevelIds);
                    $query->whereNotIn('B.DANGER_LEVEL_ID', $dangerLevelIds);
                    $query->whereNull('B.DANGER_LEVEL_ID', 'or');
                });
            }
            else {
                $query->where('B.DANGER_LEVEL_ID', $estimate);
            }
            $is_so = true;
        }

        $userStatusList = create_kv(BaseUserStatus::all()->toArray(), 'ID', 'NAME');

        $rows = $query->where(function ($query) {
            $where = $this->getManageWhere();
            if (!empty($where)) {
                foreach ($where as $fd => $wh) {
                    $query->where($fd, $wh);
                }
            }
        })->paginate(self::PAGE_SIZE, false, [
            'query' => request()->param()
        ])->each(function($item) use($userStatusList) {
            $item->user_status = isset($userStatusList[$item->USER_STATUS_ID]) ? $userStatusList[$item->USER_STATUS_ID] : '';
            $item->HEAD_IMG_URL= build_http_img_url($item->HEAD_IMG);
            $areas = Upareatable::where('UPAREAID','in',$item->LIVE_IDS)->order('UPAREAID','ASC')->column('NAME');
            $item->LIVE_ADDRESS = implode(' ',$areas).' '.$item->LIVE_ADDRESS;
            $item->MANAGED_BY_COMMUNITY = in_array($item->USER_STATUS_NAME, [STATUS_COMMUNITY_DETOXIFICATION, STATUS_COMMUNITY_RECOVERING]);
            return $item;
        });

        return [
            'rows' => $rows,
            'params' => [
                'keywords' => $keywords,
                'userStatus' => $userStatus,
                'estimate' => $estimate,
                'area1' => $area1,
                'area2' => $area2,
                'area3' => $area3
            ],
            'is_so' => $is_so,
            'title' => $title
        ];
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request, $id = 0)
    {

        if($request->isPost()){
            return $this->save($request);
        }

        $info = [];

        if ($id > 0) {
            $info = UserUsersModel::find($id);
            if(!$info){
                $this->error('该用户不存在');
            }

            if(!$this->checkUUid($info->ID)){
                $this->error('权限不足');
            }

            $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
            //12位
            $info->COUNTY_ID = $info->COUNTY_ID.'000000';
            $liveids = explode(',',$info->LIVE_IDS);
            $modids = explode(',',$info->DOMICILE_IDS);
            $dmids = explode(',',$info->DMMC_IDS);
            $info->LIVE_IDS = fillArrayToLen($liveids,3);
            $info->DOMICILE_IDS = fillArrayToLen($modids,3);
            $info->DMMC_IDS = fillArrayToLen($dmids,4);

            $statusRelation = $info->USER_STATUS_RELATION;
            if (!empty($statusRelation)) {
                foreach (json_decode($statusRelation) as $name => $value) {
                    if (in_array($name, self::STATUS_FILE_RELATIONS)) {
                        $info[$name . '_url'] = build_http_img_url($value);
                    }
                    $info[$name] = $value;
                }
            }
            $subStatusRelation = $info->USER_SUB_STATUS_RELATION;
            if (!empty($subStatusRelation)) {
                foreach (json_decode($subStatusRelation) as $name => $value) {
                    if (in_array($name, self::STATUS_FILE_RELATIONS)) {
                        $info[$name . '_url'] = build_http_img_url($value);
                    }
                    $info[$name] = $value;
                }
            }
        }
        $admin = session('info');
        $lv1Value = explode(',', $admin['DMMCIDS'])[0];

        $opts = Opts::getTreeAll();

        $js = $this->loadJsCss(array('p:ueditor/ueditor', 'p:cate/jquery.cate','userusers_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $css = $this->loadJsCss(array('userusers_create'), 'css', 'admin');
        $this->assign('headercss', $css);
        $this->assign('info',$info);
        $this->assign('nations',BaseNationType::all());
        $this->assign('nationality',BaseNationalityType::all());
        $this->assign('card_types',$opts['card_types']);
        $this->assign('edus',$opts['edus']);
        $this->assign('genders',$opts['genders']);
        $this->assign('job_status',$opts['job_status']);
        $this->assign('marital_status',$opts['marital_status']);
        $this->assign('drug_types',$opts['drug_types']);
        $this->assign('narcotics_types',$opts['narcotics_types']);
        $this->assign('user_status',$opts['user_status']);
        $this->assign('user_sub_status', $opts['user_sub_status']);
        $this->assign('danger_level',$opts['danger_level']);
        $this->assign('lvlValue', $lv1Value);

        return $this->fetch('create');
    }

    public function zhipai0(Request $request){
        return $this->index($request, 'unzhipai');
    }
    public function zhipai1(Request $request){
        return $this->index($request, 'unzhipai2');
    }
    public function zhipai2(Request $request){
        return $this->index($request, 'zhipai');
    }
    public function zhipai3(Request $request){
        return $this->index($request, 'jiechu');
    }

    public function zhiPai(Request $request,$id=0){
        if($request->isPost()){
            return $this->saveZhiPai($request);
        }

        $info = UserUsersModel::find($id);
        if(!$info){
            $this->error('该用户不存在');
        }

        if(!$this->checkUUid($info->ID)){
            $this->error('权限不足');
        }

        $changeLogs = UserChangeLog::where(['UUID' => $id, 'LOG_TYPE' => self::CHANGE_LOG_TYPE_ASSIGN])
            ->order('CREATE_TIME', 'desc')
            ->paginate(3, false);
        if (!empty($changeLogs)) {
            foreach ($changeLogs as $log) {
                if (empty($log->CONTENT)) {
                    continue;
                }
                $log->CONTENT = json_decode($log->CONTENT);
                $log->CONTENT->newArea = '';
                if (!empty($log->CONTENT->new)) {
                    if (!empty($log->CONTENT->new->countyName)) {
                       $log->CONTENT->newArea .= $log->CONTENT->new->countyName;
                    }
                    if (!empty($log->CONTENT->new->streetName)) {
                        $log->CONTENT->newArea .= ' ' . $log->CONTENT->new->streetName;
                    }
                    if (!empty($log->CONTENT->new->communityName)) {
                        $log->CONTENT->newArea .= ' ' . $log->CONTENT->new->communityName;
                    }
                }
            }
        }

        $js = $this->loadJsCss(array('p:cate/jquery.cate','userusers_zhipai'), 'js', 'admin');
        $css = $this->loadJsCss(array('userusers_zhipai'), 'css', 'admin');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
        $this->assign('info', $info);
        $this->assign('changeLogs', $changeLogs);
        $this->assign('page', $changeLogs->render());
        $this->assign('total', $changeLogs->total());
        $this->assign('powerLevel', $this->getPowerLevel());
        return $this->fetch('zhi_pai');
    }

    public function userStatusTongJi(){

        //todo 加上权限范围条件
        $list = UserUsersModel::fieldRaw('USER_STATUS_ID,count(*) as t')
            ->where('ISDEL',0)
            ->where(function ($query){
                $ids = $this->getManageUUids();
                if($ids != 'all'){
                    $query->whereIn('ID', $ids);
                }
            })
            ->group('USER_STATUS_ID')
            ->select();
        $kv = create_kv($list->toArray(), 'USER_STATUS_ID', 't');
        //print_r($list);
//        return json_encode($list);

//        exit;
        $counts = BaseUserStatus::all()->map(function ($item) use($kv){
            $item->total = $kv[$item->ID] ?? 0;
            return $item;
        });

        $this->assign('uncounts', $kv[0] ?? 0);
        $this->assign('counts', $counts);
        return $this->fetch();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request,$id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }

        $info = UserUsersModel::find($id);
        if(!$info){
            $this->error('访问错误');
        }

        if(!$this->checkUUid($info->ID)){
            $this->error('权限不足');
        }

        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $cardtype = BaseCertificateType::find($info->ID_NUMBER_TYPE);
        //尿检总计次数统计
        $info->uarns = $info->uranCheck();
        //
        //$info->user_type = UserUsersModel::$utypes[$info->UTYPE_ID] ?? '';
        $info->user_type218 = UserUsersModel::$utypes[$info->UTYPE_ID_218] ?? '';
        //
        $userStatus = BaseUserStatus::find($info->USER_STATUS_ID);
        $dangerLevel = BaseUserDangerLevel::find($info->DANGER_LEVEL_ID);
        $info->user_status = $userStatus ? $userStatus->NAME : '';
        $info->danger_level = $dangerLevel ? $dangerLevel->NAME : '';

        $info->user_status_logs = UserChangeLog::where('UUID', $info->ID)
            ->where('LOG_TYPE', self::CHANGE_LOG_TYPE_STATUS)
            ->order('CREATE_TIME', 'desc')
            ->select();

        $this->assign('info',$info);
        $this->assign('cardtype',$cardtype);
        return $this->fetch();
    }

    public function changePwsd(Request $request, $id=0){

        if ($request->isPost()) {

            $id = $request->post('ID',0);
            $pwsd = $request->post('PWSD','');

            if(!$id || !$pwsd){
                $this->error('提交数据不能为空');
            }

            $info = UserUsersModel::where('ISDEL','=',0)->find($id);
            if(!$info){
                $this->error('该人员信息不存在或已删除');
            }

            if(!$this->checkUUid($info->ID)){
                $this->error('权限不足');
            }

            $stat = \think\helper\Str::random(6);
            $info->PWSD = create_pwd($pwsd,$stat);
            $info->SALT = $stat;
            $info->save();

            $this->addAdminLog(self::OPER_TYPE_UPDATE, '修改康复人员密码', '密码修改成功', $id);

            $this->jsalert('修改密码成功',7);
            return ;
        }


        if(!$id){
            $this->error('访问错误');
        }
        $info = UserUsersModel::where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该人员信息不存在或已删除');
        }
        if(!$this->checkUUid($info->ID)){
            $this->error('权限不足');
        }

        $js = $this->loadJsCss(array( 'userusers_change_pwsd'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        return $this->fetch('change_pwsd');
    }

    public function phoneData(Request $request, $id=0){
        if(!$id){
            $this->error('访问错误');
        }
        //todo 加上权限范围条件
        $info = UserUsersModel::where('ISDEL','=',0)

            ->find($id);
        if(!$info){
            $this->error('该人员信息不存在或已删除');
        }
        if(!$this->checkUUid($info->ID)){
            $this->error('权限不足');
        }

        $type_model_map = [
            'address'=>[
                'class'=>UserPhoneDataAddress::class,
                'tpl'=>'phone_data_address',
                'order'=>'UPDATE_TIME'
            ],
            'sms'=>[
                'class'=>UserPhoneDataSms::class,
                'tpl'=>'phone_data_sms',
                'order'=>'SMS_DATE'
            ],
            'calls'=>[
                'class'=>UserPhoneDataCalls::class,
                'tpl'=>'phone_data_calls',
                'order'=>'CALL_DATE_STR'
            ]
        ];

        $type = $request->get('type','address');
        if(!isset($type_model_map[$type])){
            $type = 'address';
        }
        $model = new $type_model_map[$type]['class'];

        $list = $model->where('UUID',$id)->order($type_model_map[$type]['order'],'desc')->paginate(50);

        //$this->assign('tpl',$type_model_map[$type]['tpl']);
        $this->assign('info',$info);
        $this->assign('type',$type);
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        return $this->fetch('phone_data');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request, $id = 0)
    {
        if($request->isGet() && !$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request, $id = 0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        $info = UserUsersModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该用户不存在或已删除');
        }

        if(!$this->checkUUid($info->ID)){
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        if($info->HEAD_IMG){
            WaitDeleteFiles::addOne([
                'table'=>'userusers',
                'id'=>$info->ID,
                'path'=>$info->HEAD_IMG
            ]);
        }


        $this->success('删除成功');
    }

    protected function saveZhiPai(Request $request){

        $zpact = $request->post('zpact', 0, 'int');

        if (!in_array($zpact, [self::ASSIGN_TYPE_ASSIGN, self::ASSIGN_TYPE_ARCHIVE, self::ASSIGN_TYPE_RELIEVE])) {
            $this->error('操作有误');
        }

        $uid = $request->post('uid', 0, 'int');
        $user = UserUsersModel::find($uid);
        if (!$user) {
            $this->error('用户信息有误');
        }

        if (!$this->checkUUid($user->ID)) {
            $this->error('权限不足');
        }
        $actName = '';
        $isArchive = $zpact == self::ASSIGN_TYPE_ARCHIVE;
        if ($zpact == self::ASSIGN_TYPE_ASSIGN || $isArchive) {

            $county_id_12 = $request->post('COUNTY_ID_12');
            if (empty($county_id_12)) {
                $county_id_12 = self::DEFAULT_COUNTY_ID;
            }
            $street_id = $request->post('STREET_ID', 0);
            $community_id = $request->post('COMMUNITY_ID', 0);

            $original_county_id = $user->COUNTY_ID_12;
            $original_street_id = $user->STREET_ID;
            $original_community_id = $user->COMMUNITY_ID;

            $original_area_names = Subareas::where('CODE12', 'in', [$original_county_id, $original_street_id, $original_community_id])
                ->order('CODE12 ASC')
                ->column('NAME');
            $new_area_names = Subareas::where('CODE12', 'in', [$county_id_12, $street_id, $community_id])
                ->order('CODE12 ASC')
                ->column('NAME');

            if ($isArchive && (!$county_id_12 || !$street_id || !$community_id)) {
                $this->error('指派地区有误');
            }

            /**
             * 将人员指派回退到上级区域时，需填写指派理由
             */
            $assignReason = $request->param('assignReason', '');
            $powerLevel = $this->getPowerLevel();
            $reasonRequired = false;
            if ($powerLevel == self::POWER_LEVEL_COUNTY && empty($county_id_12)) {
                $reasonRequired = true;
            }
            elseif ($powerLevel == self::POWER_LEVEL_STREET && empty($street_id)) {
                $reasonRequired = true;
            }
            elseif ($powerLevel == self::POWER_LEVEL_COMMUNITY && empty($community_id)) {
                $reasonRequired = true;
            }
            if ($reasonRequired && empty($assignReason)) {
                $this->error('将人员指派回退到上级区域时，需填写指派理由');
            }

            // 设置区域管辖范围
            $user->COUNTY_ID = substr($county_id_12, 0, 6);
            $user->COUNTY_ID_12 = $county_id_12;
            $user->STREET_ID = $street_id;
            $user->COMMUNITY_ID = $community_id;

            // 设置管辖社区
            if (!empty($community_id)) {
                $user->MANAGE_COMMUNITY = $new_area_names[2];
            }

            // 设置警务管辖单位
            $this->setManagePoliceArea($user);

            if ($isArchive) {
                $user->JD_ZHI_PAI_ID = 1;
            } else {
                $user->JD_ZHI_PAI_ID = 0;
            }

            $user->save();

            $original_county_name = isset($original_area_names[0]) ? $original_area_names[0] : '';
            $original_street_name = isset($original_area_names[1]) ? $original_area_names[1] : '';
            $original_community_name = isset($original_area_names[2]) ? $original_area_names[2] : '';
            $new_county_name = isset($new_area_names[0]) ? $new_area_names[0] : '';
            $new_street_name = isset($new_area_names[1]) ? $new_area_names[1] : '';
            $new_community_name = isset($new_area_names[2]) ? $new_area_names[2] : '';
            $changeLogContent = [
                'assignId' => $user->JD_ZHI_PAI_ID,
                'assignName' => $isArchive ? '建档' : '指派',
                'original' => [
                    'countyId' => $original_county_id,
                    'countyName' => $original_county_name,
                    'streetId' => $original_street_id,
                    'streetName' => $original_street_name,
                    'communityId' => $original_community_id,
                    'communityName' => $original_community_name
                ],
                'new' => [
                    'countyId' => $county_id_12,
                    'countyName' => $new_county_name,
                    'streetId' => $street_id,
                    'streetName' => $new_street_name,
                    'communityId' => $community_id,
                    'communityName' => $new_community_name
                ],
                'reason' => $assignReason
            ];

            $actName = $isArchive ? '确认建档' : '确认指派';

            $admin_log_content = '指派操作：' . $actName . '<br/>'
                . '指派流转：' . ($original_county_name ?: '市') . $original_street_name . $original_community_name . ' -> '
                . ($new_county_name ?: '市') . $new_street_name . $new_community_name . '<br/>'
                . '指派原因：' . ($reasonRequired ? $assignReason : '无');
        }
        elseif ($zpact == self::ASSIGN_TYPE_RELIEVE) {
            $user->JD_ZHI_PAI_ID = 2;
            $user->JD_JIE_ZHU_TIME = Carbon::now()->toDateTimeString();

            $user->save();

            $changeLogContent = [
                'assignId' => $user->JD_ZHI_PAI_ID,
                'assignName' => '解除'
            ];

            $actName = '解除社区康复/戒毒';
            $admin_log_content = $actName;
        }

        UserChangeLog::addRow([
            'UUID' => $user->ID,
            'LOG_TYPE' => self::CHANGE_LOG_TYPE_ASSIGN,
            'CONTENT' => json_encode($changeLogContent),
            'OPER_USER_ID' => session('user_id'),
            'OPER_USER_NAME' => session('name')
        ]);

        $this->addAdminLog(self::OPER_TYPE_UPDATE, '指派康复人员', $admin_log_content, $uid);

        $this->jsalert($actName.'成功',3);
    }

    private function setManagePoliceArea(&$user) {
        $dmmcs = NbAuthDept::where('AREACODE', 'in', [$user->COUNTY_ID_12, $user->STREET_ID, $user->COMMUNITY_ID])->order('DEPTCODE desc')
            ->select()->toArray();
        if (!empty($dmmcs)) {
            $dmmc = $dmmcs[0];
            $user->MANAGE_POLICE_AREA_CODE = $dmmc['DEPTCODE'];
            $user->MANAGE_POLICE_AREA_NAME = $dmmc['DEPTNAME'];
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    protected function save(Request $request)
    {

        $id = $request->post('ID',0,'int');

        if ($id > 0) {
            if (!$this->checkUUid($id)) {
                $this->error('权限不足');
            }
            $isNew = false;
        } else {
            $isNew = true;
        }

        $usermodel = new UserUsersModel();

        $mobile = $request->param('MOBILE','','trim');

        $mobile_exist = $usermodel->where(function($t) use ($id,$mobile) {
            $t->where('MOBILE', '=', $mobile);
            if ($id > 0) {
                $t->whereRaw('ID!='.$id);
            }
        })->where('ISDEL','=',0)->count();

        if ($mobile_exist) {
            $this->error('该手机号已经存在');
        }

        $levelarea = $request->param('levelarea',[]);
        $levelarea = array_filter($levelarea);
        $county_id = isset($levelarea[0]) ? $levelarea[0] : 0;
        $street_id = isset($levelarea[1]) ? $levelarea[1] : 0;
        $community_id = isset($levelarea[2]) ? $levelarea[2] : 0;
        $area_info = Subareas::where('CODE12', end($levelarea))->find();
        if(!$area_info){
            $this->error('缺少管辖社区信息');
        }

        $dmmcids = $request->param('dmmc', [], 'trim');
        $dmmcids = array_filter($dmmcids);
        if (empty($dmmcids) || empty($dmmcids[1])) {
            $this->error('缺少所属禁毒办信息');
        }
        $dmmc = NbAuthDept::find(end($dmmcids));
        if (!$dmmc) {
            $this->error('缺少所属禁毒办信息');
        }

        $user_status_id = $request->param('USER_STATUS_ID',0,'trim');
        $user_status_name = $request->param('USER_STATUS_NAME', '');
        $user_sub_status_id = $request->param('USER_SUB_STATUS_ID', 0);
        $user_sub_status_name = $request->param('USER_SUB_STATUS_NAME', '');

        $utype = $request->param('UTYPE_ID',0,'trim');
        $utype218 = $request->param('UTYPE_ID_218',0,'trim');

        $domicileplaceids = $request->param('domicileplace');

        $domicileplace = Upareatable::where('UPAREAID','in',$domicileplaceids)->order('UPAREAID','asc')->select()->column('NAME');

        $liveplaceids = $request->param('liveplace');
        $liveplace = Upareatable::where('UPAREAID','in',$liveplaceids)->order('UPAREAID','asc')->select()->column('NAME');

        $nationality = BaseNationalityType::find($request->param('NATIONALITY_ID','','trim'));
        $nation = BaseNationType::find($request->param('NATION_ID','','trim'));

        // 检验人员状态相关信息的合法性
        $this->validStatusRelations($request);

        $userStatusRelation = $this->buildStatusRelations($request);
        $userSubStatusRelation = $this->buildSubStatusRelations($request);

        $jd_start_time = $this->getJdStartTime($request, $user_status_name);
        $jd_end_time = $this->getJdEndTime($request, $user_status_name, $jd_start_time);

        $subStatusStartTime = $this->getSubStatusStartTime($request, $user_sub_status_name);
        $subStatusEndTime = $this->getSubStatusEndTime($request, $user_sub_status_name);

        $data = [
            'STATUS'=>1,
            'NAME'=>$request->param('NAME','','trim'),
            'ALIAS_NAME'=>$request->param('ALIAS_NAME','','trim'),
            'ID_NUMBER'=>$request->param('ID_NUMBER','','trim'),
            'ID_NUMBER_TYPE'=>$request->param('ID_NUMBER_TYPE','','trim'),
            'MOBILE'=>$request->param('MOBILE','','trim'),
            'GENDER'=>$request->param('GENDER','','trim'),

            'JD_START_TIME'=>$jd_start_time,
            'JD_END_TIME'=>$jd_end_time,
            'SUB_STATUS_START_TIME' => $subStatusStartTime,
            'SUB_STATUS_END_TIME' => $subStatusEndTime,
            'USER_STATUS_ID'=>$user_status_id,
            'USER_STATUS_NAME' => $user_status_name,
            'USER_SUB_STATUS_ID' => $user_sub_status_id,
            'USER_SUB_STATUS_NAME' => $user_sub_status_name,
            'USER_STATUS_RELATION' => $userStatusRelation,
            'USER_SUB_STATUS_RELATION' => $userSubStatusRelation,
            'DANGER_LEVEL_ID'=>$request->param('DANGER_LEVEL_ID','','trim'),
            'UTYPE_ID'=>$utype,
            'UTYPE_ID_218'=>$utype218,

            'NATIONALITY_ID'=>$request->param('NATIONALITY_ID','','trim'),
            'NATION_ID'=>$request->param('NATION_ID','','trim'),
            'HEIGHT'=>$request->param('HEIGHT','','trim'),
            'EDUCATION_ID'=>$request->param('EDUCATION_ID','','trim'),
            'JOB_STATUS_ID'=>$request->param('JOB_STATUS_ID','','trim'),
            'JOB_UNIT'=>$request->param('JOB_UNIT','','trim'),
            'MARITAL_STATUS_ID'=>$request->param('MARITAL_STATUS_ID','','trim'),

            'DOMICILE_ADDRESS'=>$request->param('DOMICILE_ADDRESS','','trim'),
            'DOMICILE_POLICE_STATION'=>$request->param('DOMICILE_POLICE_STATION','','trim'),
            'DOMICILE_POLICE_STATION_CODE'=>$request->param('DOMICILE_POLICE_STATION_CODE','','trim'),

            'LIVE_ADDRESS'=>$request->param('LIVE_ADDRESS','','trim'),
            'LIVE_POLICE_STATION'=>$request->param('LIVE_POLICE_STATION','','trim'),
            'LIVE_POLICE_STATION_CODE'=>$request->param('LIVE_POLICE_STATION_CODE','','trim'),

            'DRUG_TYPE_ID'=>$request->param('DRUG_TYPE_ID','','trim'),
            'NARCOTICS_TYPE_ID'=>$request->param('NARCOTICS_TYPE_ID','','trim'),

            'NATIONALITY'=>$nationality->NAME,
            'NATION'=>$nation->NAME,

            'EDUCATION'=>Opts::getEdu($request->param('EDUCATION_ID','','trim'))['NAME'],
            'JOB_STATUS'=>Opts::getJobStatus($request->param('JOB_STATUS_ID','','trim'))['NAME'],
            'MARITAL_STATUS'=>Opts::getMaritalStatus($request->param('MARITAL_STATUS_ID','','trim'))['NAME'],

            'PROVINCE_ID'=>$area_info->PROVICEID,
            'CITY_ID'=>$area_info->CITYID,
            'COUNTY_ID'=>$area_info->COUNTYID,
            'COUNTY_ID_12'=>$county_id,
            'STREET_ID'=>$street_id,
            'COMMUNITY_ID'=>$community_id,

            'DOMICILE_PLACE'=>implode(' ',$domicileplace),
            'DOMICILE_IDS'=>implode(',',$domicileplaceids),//new

            'LIVE_PLACE'=>implode(' ',$liveplace),
            'LIVE_IDS'=>implode(',',$liveplaceids),//new

            'DRUG_TYPE'=>Opts::getNameById($request->param('DRUG_TYPE_ID','','trim'))['NAME'],
            'NARCOTICS_TYPE'=>Opts::getNameById($request->param('NARCOTICS_TYPE_ID','','trim'))['NAME'],

            'MANAGE_POLICE_AREA_CODE'=>$dmmc->DEPTCODE,
            'MANAGE_POLICE_AREA_NAME'=>$dmmc->DEPTNAME,
            'MANAGE_COMMUNITY'=>$area_info->NAME,
            'DMMC_IDS'=>implode(',', $dmmcids),//new

            'POLICE_LIABLE_CODE'=>$request->param('POLICE_LIABLE_CODE','','trim'),
            'POLICE_LIABLE_NAME'=>$request->param('POLICE_LIABLE_NAME','','trim'),
            'POLICE_LIABLE_MOBILE'=>$request->param('POLICE_LIABLE_MOBILE','','trim'),
            'JD_ZHUANGAN'=>$request->param('JD_ZHUANGAN','','trim'),
            'JD_ZHUANGAN_MOBILE'=>$request->param('JD_ZHUANGAN_MOBILE','','trim'),
            'JD_REMARKS'=>$request->param('JD_REMARKS', '', 'trim')
        ];

        if (!$id) {
            $data['UUCODE'] = $usermodel->createNewUUCode();
        }

        if ($request->has('PWSD')) {
            $pwsd = $request->post('PWSD');
            $stat = Str::random(6);
            $data['PWSD'] = create_pwd($pwsd,$stat);
            $data['SALT'] = $stat;
        }
        $v = new UserUsersVer();
        if (!$v->check($data)) {
            $this->error($v->getError());
        }
        $needLogUserStatusChange = false;

        if ($id > 0) {
            $user = UserUsersModel::find($id);
            if ($user->USER_STATUS_ID != $user_status_id) {
                $needLogUserStatusChange = true;
            }
            $user->save($data);
        } else {

            $needLogUserStatusChange = true;
            $data['CREATE_USER_ID'] = session('user_id');
            $data['CREATE_USER_NAME'] = session('name');

            $user = UserUsersModel::create($data);
            $user->HEAD_IMG = '';
            $id = $user->ID;
        }


        if (!$id) {
            $this->error('保存失败');
        }

        if ($needLogUserStatusChange) {

            $userStatus = create_kv(BaseUserStatus::all()->toArray(), 'ID', 'NAME');
            $content = '人员状态：'.($userStatus[$user_status_id] ?? 'error');

            if (!empty($jd_start_time) && !empty($jd_end_time)) {
                $content .= "，起止时间:{$jd_start_time}到{$jd_end_time}";
            }

            UserChangeLog::addRow([
                'UUID'=>$user->ID,
                'LOG_TYPE' => 1,
                'CONTENT'=>$content,
                'OPER_USER_ID'=>session('user_id'),
                'OPER_USER_NAME'=>session('name')
            ]);
        }

        $img = $this->uploadImage($request,['userusers/']);
        if (isset($img['images'])) {
            // 如果存在老的图片，刚将其删除
            if($user->HEAD_IMG){
                WaitDeleteFiles::addOne([
                    'table'=>'userusers',
                    'id'=>$id,
                    'path'=>$user->HEAD_IMG
                ]);
            }

            $user->save(['HEAD_IMG'=>$img['images'][0]]);
        }

        //页面点击“保存”或“确认”键后提示成功或失败，自动停留在当前编辑界面
        $ref = url('UserUsers/edit',array('id'=>$user->ID));

        if ($isNew) {
            $log_oper_Name = '新增康复人员信息';
            $log_content = '新增康复人员信息，人员信息如下：' . self::LOG_CONTENT_BREAK;
            $log_oper_type = self::OPER_TYPE_CREATE;
        } else {
            $log_oper_Name = '修改康复人员信息';
            $log_content = '修改康复人员信息，人员信息如下：' . self::LOG_CONTENT_BREAK;
            $log_oper_type = self::OPER_TYPE_UPDATE;
        }
        foreach ($data as $name => $value) {
            if (!isset(self::USER_FIELD_NAME_DESC_MAPPER[$name])) {
                continue;
            }
            $log_content .= self::USER_FIELD_NAME_DESC_MAPPER[$name] . '：' . $value . self::LOG_CONTENT_BREAK;
        }
        $this->addAdminLog($log_oper_type, $log_oper_Name, $log_content, $user->ID);

        $this->success('保存人员资料成功',$ref);
    }

    private function getJdStartTime(Request $request, $user_status_name) {
        $relationNames = array_keys(self::STATUS_RELATIONS[$user_status_name]);
        if (in_array(self::STATUS_START_TIME_NAME, $relationNames)) {
            $jd_start_time = $request->param(self::STATUS_START_TIME_NAME,'');
        } else {
            $jd_start_time = null;
        }
        return $jd_start_time;
    }

    private function getJdEndTime(Request $request, $user_status_name, $jd_start_time = '') {
        $relationNames = array_keys(self::STATUS_RELATIONS[$user_status_name]);
        // 社区戒毒和社区康复起止时间时长均为固定3年
        if (in_array($user_status_name, [STATUS_COMMUNITY_DETOXIFICATION, STATUS_COMMUNITY_RECOVERING])) {
            $jd_end_time = date('Y-m-d', strtotime('+3 year', strtotime($jd_start_time)));
        }
        else if (in_array(self::STATUS_END_TIME_NAME, $relationNames)) {
            $jd_end_time = $request->param(self::STATUS_END_TIME_NAME,'');
        }
        else {
            $jd_end_time = null;
        }
        return $jd_end_time;
    }

    private function getSubStatusStartTime(Request $request, $user_sub_status_name) {
        if (empty($user_sub_status_name)) {
            return null;
        }
        $relationNames = array_keys(self::SUB_STATUS_RELATIONS[$user_sub_status_name]);
        if (in_array(self::SUB_STATUS_START_TIME_NAME, $relationNames)) {
            $subStatusStartTime = $request->param(self::SUB_STATUS_START_TIME_NAME,'');
        } else {
            $subStatusStartTime = null;
        }
        return $subStatusStartTime;
    }

    private function getSubStatusEndTime(Request $request, $user_sub_status_name) {
        if (empty($user_sub_status_name)) {
            return null;
        }
        $relationNames = array_keys(self::SUB_STATUS_RELATIONS[$user_sub_status_name]);
        if (in_array(self::SUB_STATUS_END_TIME_NAME, $relationNames)) {
            $subStatusEndTime = $request->param(self::SUB_STATUS_END_TIME_NAME,'');
        } else {
            $subStatusEndTime = null;
        }
        return $subStatusEndTime;
    }

    private function validStatusRelations(Request $request) {
        $statusName = $request->param('USER_STATUS_NAME');
        if (!isset(self::STATUS_RELATIONS[$statusName])) {
            $this->error('非法的人员状态');
        }
        $relations = self::STATUS_RELATIONS[$statusName];
        if (empty($relations)) {
            return;
        }
        foreach ($relations as $name => $desc) {
            if (in_array($name, self::STATUS_FILE_RELATIONS)) {
                if (empty($_FILES[$name]['tmp_name']) && empty($request->param($name . self::URI_FLAG))) {
                    $this->error('非法的' . $desc);
                }
            } else {
                if (empty($request->param($name))) {
                    $this->error('非法的' + $desc);
                }
            }
        }
        $subStatusId = $request->param('USER_SUB_STATUS_ID', 0);
        $subStatusName = $request->param('USER_SUB_STATUS_NAME');
        if (empty($subStatusId) || empty($subStatusName)) {
            return;
        }
        $relations = self::SUB_STATUS_RELATIONS[$subStatusName];
        if (empty($relations)) {
            return;
        }
        foreach ($relations as $name => $desc) {
            if (in_array($name, self::STATUS_FILE_RELATIONS)) {
                if (empty($_FILES[$name]['tmp_name']) && empty($request->param($name . self::URI_FLAG))) {
                    $this->error('非法的' . $desc);
                }
            } else {
                if (empty($request->param($name))) {
                    $this->error('非法的' . $desc);
                }
            }
        }
    }

    private function buildStatusRelations(Request $request) {
        $statusName = $request->param('USER_STATUS_NAME');
        $relations = self::STATUS_RELATIONS[$statusName];
        if (empty($relations)) {
            return '';
        }
        $statusRelation = [];
        foreach ($relations as $name => $desc) {
            // 时间相关依赖作额外处理
            if (in_array($name, [self::STATUS_START_TIME_NAME, self::STATUS_END_TIME_NAME])) {
                continue;
            }
            // 非文件类型依赖
            if (!in_array($name, self::STATUS_FILE_RELATIONS)) {
                $statusRelation[$name] = $request->param($name);
                continue;
            }
            // 文件类型依赖
            if (!empty($_FILES[$name]['tmp_name'])) {
                $result = $this->uploadImage($request, ['userusers', 'status/'], [$name]);
                if (empty($result) || empty($result['images'])) {
                    continue;
                }
                $statusRelation[$name] = $result['images'][0];
            } else {
                $statusRelation[$name] = $request->param($name . self::URI_FLAG);
            }
        }
        return json_encode($statusRelation);
    }

    private function buildSubStatusRelations(Request $request) {
        $subStatusName = $request->param('USER_SUB_STATUS_NAME');
        if (empty($subStatusName)) {
            return '';
        }
        $relations = self::SUB_STATUS_RELATIONS[$subStatusName];
        if (empty($relations)) {
            return '';
        }
        $subStatusRelation = [];
        foreach ($relations as $name => $desc) {
            // 非文件类型依赖
            if (!in_array($name, self::STATUS_FILE_RELATIONS)) {
                $subStatusRelation[$name] = $request->param($name);
                continue;
            }
            // 文件类型依赖
            if (!empty($_FILES[$name]['tmp_name'])) {
                $result = $this->uploadImage($request, ['userusers', 'sub_status/'], [$name]);
                if (empty($result) || empty($result['images'])) {
                    continue;
                }
                $subStatusRelation[$name] = $result['images'][0];
            } else {
                $subStatusRelation[$name] = $request->param($name . self::URI_FLAG);
            }
        }
        return json_encode($subStatusRelation);
    }

    public function logList($id = 0) {
        $data = $this->getLogData($id);

        $list = $data['query']->paginate(self::LOG_PAGE_SIZE, false, [
            'query' => request()->param(),
        ]);

        $js = $this->loadJsCss(array('p:cate/jquery.cate','userusers_log'), 'js', 'admin');
        $this->assign('footjs', $js);

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('is_so', $data['is_so']);
        $this->assign('param', $data['param']);
        $this->assign('keywords', $data['param']['keywords']);

        return $this->fetch('log');
    }

    protected function getLogData($id) {

        if (empty($id)) {
            $this->error("访问错误");
        }

        $is_so = false;

        $query = AdminLogs::where('TARGET_ID', $id)->where('TARGET_TYPE', 'UserUser')->order('ADD_TIME DESC');

        $operBeginTime = input('get.oper_begin_time', '');
        $operEndTime = input('get.oper_end_time', '');
        if (!empty($operBeginTime) && empty($operEndTime)) {

            $query->whereTime('ADD_TIME', '>=', Carbon::parse($operBeginTime)->startOfDay()->toDateTimeString());
            $is_so = true;

        } elseif (empty($operBeginTime) && !empty($operEndTime)) {

            $query->whereTime('ADD_TIME', '<=', Carbon::parse($operEndTime)->endOfDay()->toDateTimeString());
            $is_so = true;

        } elseif (!empty($operBeginTime) && !empty($operEndTime)) {

            $query->whereTime('ADD_TIME', 'between', [
                Carbon::parse($operBeginTime)->startOfDay()->toDateTimeString(),
                Carbon::parse($operEndTime)->endOfDay()->toDateTimeString()
            ]);
            $is_so = true;

        }
        $param = [
            'oper_begin_time' => $operBeginTime,
            'oper_end_time' => $operEndTime
        ];

        $fields = ['USER_NAME', 'OPER_NAME'];
        $keywords = input('get.keywords','');
        if(!empty($keywords)){
            $query->where(implode('|', $fields), 'like', '%'. $keywords .'%');
            $is_so = true;
        }
        $param['keywords'] = $keywords;

        return ['query' => $query, 'param' => $param, 'is_so' => $is_so];
    }

    public function statusChanges($id = 0) {
        if (!$this->checkUUid($id)) {
            $this->error('权限不足');
        }
        $changeLogs = UserChangeLog::where(['UUID' => $id, 'LOG_TYPE' => self::CHANGE_LOG_TYPE_STATUS])->order('CREATE_TIME', 'desc')->select();
        $this->assign('changeLogs', $changeLogs);
        return $this->fetch('status_changes');
    }

    public function statisticsStatus(Request $request) {
        return $this->statistic($request, function ($pageNO, $pageSize, $condition) {
            $statusList = create_kv(BaseUserStatus::all(), 'ID', 'NAME');
            $statusList[UserUsersModel::STATUS_ID_OTHER] = UserUsersModel::STATUS_NAME_OTHER;
            $statusList[UserUsersModel::STATISTICS_ID_TOTAL] = UserUsersModel::STATISTICS_NAME_TOTAL;
            $this->assign('statusList', $statusList);
            return UserUsersModel::statisticsStatus($pageNO, $pageSize, $condition);
        }, 'statistics_status');
    }

    public function exportStatisticsStatus(Request $request) {
        $this->exportStatistics($request, function ($pageNO, $pageSize, $condition) {
            return UserUsersModel::statisticsStatus($pageNO, $pageSize, $condition);
        }, '人员状态统计报表');
    }

    public function statisticsEstimates(Request $request) {
        return $this->statistic($request, function ($pageNO, $pageSize, $condition) {
            $this->assign('estimateList', UserUsersModel::ESTIMATE_DANGER_LEVEL_LIST);
            return UserUsersModel::statisticsEstimates($pageNO, $pageSize, $condition);
        }, 'statistics_estimates');
    }

    public function exportStatisticsEstimates(Request $request) {
        $this->exportStatistics($request, function ($pageNO, $pageSize, $condition) {
            return UserUsersModel::statisticsEstimates($pageNO, $pageSize, $condition);
        }, '风险评估统计报表');
    }

    public function statisticsAssignment(Request $request) {
        return $this->statistic($request, function ($pageNO, $pageSize, $condition) {
            $this->assign('assignmentList', UserUsersModel::ASSIGNMENT_STATUS_LIST);
            return UserUsersModel::statisticsAssignment($pageNO, $pageSize, $condition);
        }, 'statistics_assignment');
    }

    public function exportStatisticsAssignment(Request $request) {
        $this->exportStatistics($request, function ($pageNO, $pageSize, $condition) {
            return UserUsersModel::statisticsAssignment($pageNO, $pageSize, $condition);
        }, '人员指派统计报表');
    }

    public function statisticsUrine(Request $request) {
        return $this->statistic($request, function ($pageNO, $pageSize, $condition) {
            $data = UserUsersModel::statisticsUrine($pageNO, $pageSize, $condition);
            $columns_1 = $columns_2 = $columns_3 = [];
            $this->setHeaderRows($data, $columns_1, $columns_2, $columns_3);
            $this->assign('columns_1', $columns_1);
            $this->assign('columns_2', $columns_2);
            $this->assign('columns_3', $columns_3);
            return $data;
        }, 'statistics_urine');
    }

    public function exportStatisticsUrine(Request $request) {
        $this->exportStatistics($request, function ($pageNO, $pageSize, $condition) {
            return UserUsersModel::statisticsUrine($pageNO, $pageSize, $condition);
        }, '尿检记录统计报表', function ($data) {
            $columns_1 = $columns_2 = $columns_3 = [];
            $this->setHeaderRows($data, $columns_1, $columns_2, $columns_3);
            $row1 = $row2 = $row3 = [];
            foreach (self::STATISTICS_EXCEL_TITLE_LIST as $name) {
                array_push($row1, [
                    'name' => $name,
                    'mergeY' => 2,
                    'alignY' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                ]);
            }
            foreach ($columns_1 as $name) {
                array_push($row1, [
                    'name' => $name,
                    'mergeX' => end($columns_1) == $name ? 1 : 5
                ]);
            }
            $row2['offset'] = 3;
            foreach ($columns_2 as $name) {
                array_push($row2, [
                    'name' => $name,
                    'mergeX' => 1
                ]);
            }
            $row3['offset'] = 3;
            foreach (array_values($columns_3) as $name) {
                array_push($row3, [
                    'name' => $name
                ]);
            }
            return [$row1, $row2, $row3];
        }, ['QUIT_USER_NUM', 'RECOVERY_USER_NUM']);
    }

    private function setHeaderRows($data, &$columns_1, &$columns_2, &$columns_3) {
        $allListRow = $data['allList'][0];
        $availableStatus = UserUsersModel::getUrineAvailableStatus();
        $column1Format = "%s（%u人）";
        $totalUserNum = 0;
        foreach ($availableStatus as $attr) {
            $userNum = $allListRow[$attr['finished_name'] . "USER_NUM"];
            $totalUserNum += $userNum;
            array_push($columns_1, sprintf($column1Format, mb_substr($attr['name'], 0, -1), $userNum));
            for ($i = 0; $i < UserUsersModel::URINE_YEARS; $i++) {
                $year = $i + 1;
                $finishedName = $attr['finished_name'] . "$year";
                $missingName = $attr['missing_name'] . "$year";
                $chineseYear = number2chinese($year);
                array_push($columns_2, sprintf(self::YEAR_NUMBER, $chineseYear));
                $columns_3[$finishedName] = URINE_CHECK_FINISHED;
                $columns_3[$missingName] = URINE_CHECK_MISSING;
            }
        }
        array_push($columns_1, sprintf($column1Format, UserUsersModel::STATISTICS_NAME_TOTAL, $totalUserNum));
        array_push($columns_2, "整社戒社康期间");
        $columns_3['TOTAL_FINISHED'] = URINE_CHECK_FINISHED;
        $columns_3['TOTAL_MISSING'] = URINE_CHECK_MISSING;
    }

    private function statistic(Request $request, $dataGetter, $view) {
        $pageNO = $request->param('page', 1);
        if ($pageNO < 1) {
            $pageNO = 1;
        }
        $condition = $request->param();
        $powerLevel = $this->getPowerLevel();
        if (POWER_LEVEL_COUNTY == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = $request->param('area2');
            $condition['area3'] = $request->param('area3');
        }
        elseif (POWER_LEVEL_STREET == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = session('info')['POWER_STREET_ID'];
            $condition['area3'] = $request->param('area3');
        }
        elseif (POWER_LEVEL_COMMUNITY == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = session('info')['POWER_STREET_ID'];
            $condition['area3'] = session('info')['POWER_COMMUNITY_ID'];
        }
        else {
            $condition['area1'] = $request->param('area1', 0);
            $condition['area2'] = $request->param('area2', 0);
            $condition['area3'] = $request->param('area3', 0);
        }
        $result = $dataGetter($pageNO, self::PAGE_SIZE, $condition);
        $pageList = $result['pageList'];
        $pageTotal = $result['pageTotal'];
        $allList = $result['allList'];

        $paginator = Bootstrap::make($pageList, self::PAGE_SIZE, $pageNO, $pageTotal, false,
            ['path'=> Bootstrap::getCurrentPath(), 'query'=>request()->param()]
        );

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userusers_statistics'), 'js', 'admin');
        $css = $this->loadJsCss(array('userusers_statistics'), 'css', 'admin');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
        $this->assign('area1', $condition['area1']);
        $this->assign('area2', $condition['area2']);
        $this->assign('area3', $condition['area3']);
        $this->assign('allList', $allList);
        $this->assign('list', $pageList);
        $this->assign('total', $pageTotal);
        $this->assign('powerLevel', $powerLevel);
        $this->assign('page', $paginator->render());
        return $this->fetch("$view");
    }

    public function exportStatistics(Request $request, $dataGetter, $fileName, $headerGetter = 'default', $ignoredHeaders = []) {
        $condition = $request->param();
        $powerLevel = $this->getPowerLevel();
        if (self::POWER_LEVEL_COUNTY == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = $request->param('area2');
            $condition['area3'] = $request->param('area3');
        }
        elseif (self::POWER_LEVEL_STREET == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = session('info')['POWER_STREET_ID'];
            $condition['area3'] = $request->param('area3');
        }
        elseif (self::POWER_LEVEL_COMMUNITY == $powerLevel) {
            $condition['area1'] = session('info')['POWER_COUNTY_ID_12'];
            $condition['area2'] = session('info')['POWER_STREET_ID'];
            $condition['area3'] = session('info')['POWER_COMMUNITY_ID'];
        } else {
            $condition['area1'] = $request->param('area1', 0);
            $condition['area2'] = $request->param('area2', 0);
            $condition['area3'] = $request->param('area3', 0);
        }
        $result = $dataGetter(1, 9999, $condition);
        $pageList = $result['pageList'];
        $countyName = 'COUNTY_NAME';
        $streetName = 'STREET_NAME';
        $communityName = 'COMMUNITY_NAME';
        if (!empty($pageList)) {
            $areaNames = [];
            foreach ($pageList as $index => &$item) {
                unset($item['COUNTY_ID_12']);
                unset($item['STREET_ID']);
                unset($item['COMMUNITY_ID']);
                if (isset($item[$countyName])) {
                    $areaNames[$index][$countyName] = $item[$countyName];
                    unset($item[$countyName]);
                } else {
                    $areaNames[$index][$countyName] = '';
                }
                if (isset($item[$streetName])) {
                    $areaNames[$index][$streetName] = $item[$streetName];
                    unset($item[$streetName]);
                } else {
                    $areaNames[$index][$streetName] = '';
                }
                if (isset($item[$communityName])) {
                    $areaNames[$index][$communityName] = $item[$communityName];
                    unset($item[$communityName]);
                } else {
                    $areaNames[$index][$communityName] = '';
                }
            }
            foreach ($pageList as $index => $_item) {
                $newPageList[] = $areaNames[$index] + $_item;
            }
            $pageList = $newPageList;
        }
        $allList = $result['allList'];
        $totalRow = &$allList[0];

        if ($headerGetter !== 'default') {
            $headerRows = $headerGetter($result);
        } else {
            $headerRows = array_merge(self::STATISTICS_EXCEL_TITLE_LIST, array_keys($totalRow));
        }

        $totalRow = [
                $countyName => '全部',
                $streetName => '全部',
                $communityName => '全部'
            ] + $totalRow;

        $list = array_merge($allList, $pageList);
        if (!empty($ignoredHeaders)) {
            foreach ($ignoredHeaders as $header) {
                foreach ($list as &$item) {
                    if (!isset($item[$header])) {
                        continue;
                    }
                    unset($item[$header]);
                }
            }
        }
        exportExcel($headerRows, $list, '统计数据', $fileName);
    }

    public function setJdStartTime(Request $request, $id = 0) {
        if (empty($id)) {
            $this->error('参数错误');
        }
        $user = UserUsersModel::find($id);
        if (!$user) {
            $this->error('人员不存在');
        }
        if (!$this->checkUUid($id)) {
            $this->error('权限不足');
        }
        if ($request->isGet()) {
            $js = $this->loadJsCss(array( 'userusers_setjdstarttime'), 'js', 'admin');
            $this->assign('footjs', $js);
            $this->assign('ref', $request->param('ref', '', 'trim'));
            $this->assign("info", $user);
            return $this->fetch();
        }
        $startTime = $request->param('startTime', '', 'trim');
        $user->save([
            'JD_START_TIME' => $startTime,
            'JD_END_TIME' => date('Y-m-d', strtotime('+3 year', strtotime($startTime)))
        ]);
        $this->addAdminLog(self::OPER_TYPE_UPDATE, '设置康复人员社戒社康时间', '设置成功', $id);

        $this->jsalert('社戒社康时间设置成功',4, $request->param('ref', '', 'trim'));
    }

}
