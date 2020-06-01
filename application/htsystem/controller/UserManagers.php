<?php

namespace app\htsystem\controller;

use app\htsystem\model\Admins;
use Carbon\Carbon;
use function PHPSTORM_META\type;
use think\Collection;
use think\Request;
use think\helper\Str;
use app\common\model\UserManagers as UserManagersModel;
//use app\common\model\Areas;
use app\common\model\Upareatable;
//use app\common\model\AreasSubs;
use app\common\model\Subareas;
use app\common\validate\UserManagersVer;
use app\common\model\UserManagerPower;
use app\common\model\HelperAreas;
use app\common\model\WaitDeleteFiles;
use app\common\model\NbAuthDept;

use app\common\model\BaseSexType,
    app\common\model\BaseCertificateType;
use app\common\model\UserManagerLogs as UserManagerLogsModel;
use app\htsystem\model\Admins as AdminsModel;

class UserManagers extends Common
{

    const LOG_PAGE_SIZE = 20;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $sop = $this->dosearch();

        $list = $sop['query']->where(function ($query){
            $ids = $this->getManageMuids();
            if($ids != 'all'){
                $query->whereIn('ID', $ids);
            }
        })->with([
        'dmmc'=>function($query){
            $query->field('ID,DEPTCODE as DM,DEPTNAME as DMMC');
        }
    ])->paginate(self::PAGE_SIZE, false, [
            'query'=>request()->param(),
        ])->each(function($item,$key){
            $item->HEAD_IMG_URL= build_http_img_url($item->HEAD_IMG);
            return $item;
        });

        $js = $this->loadJsCss(array('p:cate/jquery.cate','usermanagers_index'), 'js', 'admin');
        $css = $this->loadJsCss(array('usermanagers_index'), 'css', 'admin');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);

        $this->assign('list',$list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('keywords',$sop['p']['keywords']);
        $this->assign('is_so',$sop['is_so']);
        $this->assign('area1',$sop['p']['a1']);
        $this->assign('area2',$sop['p']['a2']);
        $this->assign('area3',$sop['p']['a3']);
        $this->assign('powerLevel', $this->getPowerLevel());
        return $this->fetch();
    }
    protected function dosearch(){

        $is_so = false;

        $query = UserManagersModel::where('ISDEL',0);

        $fields = ['ID', 'UCODE', 'NAME', 'MOBILE', 'ID_NUMBER'];
        $p['keywords'] = input('get.keywords','');
        if (!empty($p['keywords'])) {
            $query->where(implode('|', $fields), 'like', '%' . $p['keywords'] . '%');
            $is_so = true;
        }

        $powerLevel = $this->getPowerLevel();
        $admin = session('info');
        if (self::POWER_LEVEL_COUNTY == $powerLevel) {
            $p['a1'] = $admin['POWER_COUNTY_ID_12'];
            $p['a2'] = input('area2', '');
            $p['a3'] = input('area3', '');
        }
        elseif (self::POWER_LEVEL_STREET == $powerLevel) {
            $p['a1'] = $admin['POWER_COUNTY_ID_12'];
            $p['a2'] = $admin['POWER_STREET_ID'];
            $p['a3'] = input('area3', '');
        }
        elseif (self::POWER_LEVEL_COMMUNITY == $powerLevel) {
            $p['a1'] = $admin['POWER_COUNTY_ID_12'];
            $p['a2'] = $admin['POWER_STREET_ID'];
            $p['a3'] = $admin['POWER_COMMUNITY_ID'];
        }
        else {
            $p['a1'] = input('area1', '');
            $p['a2'] = input('area2', '');
            $p['a3'] = input('area3', '');
        }

        if($p['a1'] > 0){
            $query->where('COUNTY_ID_12', $p['a1']);
            $is_so = true;
        }
        if($p['a2'] > 0){
            $query->where('STREET_ID', $p['a2']);
            $is_so = true;
        }
        if($p['a3'] > 0){
            $query->where('COMMUNITY_ID', $p['a3']);
            $is_so = true;
        }

        return ['query' => $query, 'p' => $p, 'is_so' => $is_so];
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request, $id = 0, $act = 'add')
    {

        if($request->isPost()){
            if ($act == 'add') {
                return $this->saveCreate($request);
            }else{
                return $this->save($request);
            }
        }

        $info = [];
        $post_url = Url('UserManagers/create');
        if ($id > 0) {
            $post_url = Url('UserManagers/edit', ['id'=>$id]);
            $info = UserManagersModel::where('ISDEL','=',0)->find($id);
            if(!$info){
                $this->error('该人员信息不存在或已删除');
            }
            if(!$this->checkMUid($info->ID)){
                $this->error('权限不足');
            }

            $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);

            //$liveids = explode(',',$info->LIVE_IDS);
            $modids = explode(',',$info->DOMICILE_IDS);
            $dmids = explode(',',$info->DMMC_IDS);
            //$info->LIVE_IDS = fillArrayToLen($liveids,3);
            $info->DOMICILE_IDS = fillArrayToLen($modids,3);
            $info->DMMC_IDS = fillArrayToLen($dmids,4);

        }
        $admin = session('info');
        $lv1Value = explode(',', $admin['DMMCIDS'])[0];

        $js = $this->loadJsCss(array('p:cate/jquery.cate','usermanagers_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        $this->assign('lv1Value', $lv1Value);
        $this->assign('genders',BaseSexType::all());
        $this->assign('card_types',BaseCertificateType::all());
        $this->assign('act',$act);
        $this->assign('post_url',$post_url);
        return $this->fetch('create');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        //todo 加上权限范围条件
        $info = UserManagersModel::with([
            'dmmc'=>function($query){
                $query->field('ID,DEPTCODE as DM,DEPTNAME as DMMC');
            }
        ])->where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该人员信息不存在或已删除');
        }

        if(!$this->checkMUid($info->ID)){
            $this->error('权限不足');
        }

        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $this->assign('info',$info);
        return $this->fetch();
    }

    public function changePwsd(Request $request, $id=0){
        //todo 加上权限范围条件
        if($request->isPost()){

            $id = $request->post('ID',0);
            $pwsd = $request->post('PWSD','');

            if(!$id || !$pwsd){
                $this->error('提交数据不能为空');
            }

            // 校验密码长度
            $length = strlen($pwsd);
            if ($length < 6) {
                $this->error('密码长度不得少于6位');
            }
            // 校验密码强度
//            $strength = calcPwdStrength($pwsd);
//            if ($strength < 5) {
//                $this->error('密码必须是数字、字母、特殊字符的组合');
//            }

            $info = UserManagersModel::where('ISDEL','=',0)->find($id);
            if(!$info){
                $this->error('该人员信息不存在或已删除');
            }

            if(!$this->checkMUid($info->ID)){
                $this->error('权限不足');
            }

            $stat = Str::random(5);
            $info->PWSD = create_pwd($pwsd,$stat);
            $info->SALT = $stat;
            $info->save();

            // 同步修改后台管理用户的密码
            $adminsModel = new AdminsModel();
            $adminsModel->changePwdByMobile($info->MOBILE, $info->PWSD, $info->SALT);

            $this->jsalert('修改密码成功',7);
            return ;
        }


        if(!$id){
            $this->error('访问错误');
        }
        $info = UserManagersModel::where('ISDEL','=',0)->find($id);
        if(!$info){
            $this->error('该人员信息不存在或已删除');
        }

        if(!$this->checkMUid($info->ID)){
            $this->error('权限不足');
        }

        $js = $this->loadJsCss(array( 'usermanages_change_pwsd'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info',$info);
        return $this->fetch('change_pwsd');
    }

    /**
     * 编辑
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request, $id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id,'edit');
    }

    /**
     * 审批
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function check(Request $request, $id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id,'check');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request,$id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }

        $info = UserManagersModel::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该用户不存在或已删除');
        }

        if(!$this->checkMUid($info->ID)){
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();


        if($info->HEAD_IMG){
            WaitDeleteFiles::addOne([
                'table'=>'usermanagers',
                'id'=>$info->ID,
                'path'=>$info->HEAD_IMG
            ]);
        }


        $this->success('删除成功');
    }

    public function helpareas(Request $request,$id=0){

        if(!$id){
            $this->error('错误访问');
        }

        if($request->isPost()){

            return $this->saveHelpAreas($request);
        }

//        $trees = Areas::where('PID','=',4312)->select()->map(function($t){
//            $subs = AreasSubs::where('COUNTY_ID','=',$t->ID)
//                ->where('ACTIVE',1)->select();
//            $t->SUB = create_level_tree($subs);
//            return $t;
//        });
        $prove_id = config('app.default_province_id');
        $city_id = config('app.default_city_id');

        $all = Subareas::field('CODE12 as ID,NAME,PID')
            ->where('PROVICEID', $prove_id)
            ->where('CITYID', $city_id)
            ->all();
        $trees = create_level_tree($all->toArray());
//        print_r($trees[0]['SUB']);exit;
//        print_r($countrys);

        $areaids = [];
        $levels = [];
        $access = HelperAreas::where('UMID','=',$id)->select()->map(function($t) use (&$areaids,&$levels){
            $areaids = array_merge($areaids,explode(',',$t->AREA_IDS)) ;
            $levels[] = $t->LEVEL;
            //return $t;
        });


        //print_r($areaids);exit;

        $js = $this->loadJsCss(array('usermanagers_helpareas'), 'js', 'admin');
        $this->assign('footjs', $js);

        $this->assign('trees',$trees[0]['SUB']);
        $this->assign('trees_count',count($trees[0]['SUB']));
        $this->assign('uid',$id);
        $this->assign('access',$access);
        $this->assign('areaids',$areaids);
        $this->assign('areaidsjson',json_encode($areaids));
        $this->assign('levels',$levels);
        $this->assign('prove_id',$prove_id);
        $this->assign('city_id',$city_id);
        return $this->fetch();
    }

    public function access(Request $request,$id=0){

        if($request->isPost()){

            return $this->saveAccess($request);
        }


//        $trees = Areas::where('PID','=',4312)->select()->map(function($t){
//            $subs = AreasSubs::where('COUNTY_ID','=',$t->ID)
//                ->where('ACTIVE',1)->select();
//            $t->SUB = create_level_tree($subs);
//            return $t;
//        });

        $prove_id = config('app.default_province_id');
        $city_id = config('app.default_city_id');

        $all = Subareas::field('CODE12 as ID,NAME,PID')
            ->where('PROVICEID', $prove_id)
            ->where('CITYID', $city_id)
            ->all();
        $trees = create_level_tree($all->toArray());

//        print_r($countrys);

        $areaids = [];
        $levels = [];
        $access = UserManagerPower::where('UMID','=',$id)->select()->map(function($t) use (&$areaids,&$levels){
            $areaids = array_merge($areaids,explode(',',$t->AREA_IDS)) ;
            $levels[] = $t->LEVEL;
            //return $t;
        });


        //print_r($areaids);exit;

        $js = $this->loadJsCss(array('usermanagers_access'), 'js', 'admin');
        $this->assign('footjs', $js);

        $this->assign('trees',$trees[0]['SUB']);
        $this->assign('trees_count',count($trees[0]['SUB']));
        $this->assign('uid',$id);
        $this->assign('access',$access);
        $this->assign('areaids',$areaids);
        $this->assign('areaidsjson',json_encode($areaids));
        $this->assign('levels',$levels);
        $this->assign('prove_id',$prove_id);
        $this->assign('city_id',$city_id);
        return $this->fetch();
    }

    /**
     * Ajax-获取单位中的人员  弃用 20190822
     * @param Request $request
     * @return array
     */
    public function policesByDmid(Request $request){
        if(!$request->isAjax()){
            return ['err'=>1,'msg'=>'访问错误'];
        }

        $dmid = $request->param('dmid',0,'int');

        if(!$dmid){
            return ['err'=>1,'msg'=>'参数有误','d'=>$dmid];
        }

        $list = UserManagersModel::field('ID,UCODE,NAME,MOBILE')
            ->where('STATUS','=',1)
            ->where('DMM_ID','=',$dmid)
            ->select()->toArray();

        return ['err'=>0,'msg'=>'ok','len'=>count($list),'data'=>$list];
    }


    protected function saveHelpAreas(Request $request){
        $uuid = $request->param('id',0,'int');
        $lv1 = $request->param('lv1',[]);
        $lv2 = $request->param('lv2',[]);
        $lv3 = $request->param('lv3',[]);
        $lv4 = $request->param('lv4',[]);

        if(!$this->checkMUid($uuid)){
            $this->error('权限不足');
        }

        $ha = new HelperAreas();

        $PROVINCE_ID = $request->param('PROVINCE_ID');
        $CITY_ID = $request->param('CITY_ID');

        $lv4_datas = $lv3_datas = $lv2_datas = $lv1_datas = [
            'UMID'=>$uuid,
            'PROVINCE_ID'=>$PROVINCE_ID,
            'CITY_ID'=>$CITY_ID,
            'COUNTY_ID'=>0,
            'STREET_ID'=>0,
        ];

        if(!empty($lv4)){
            $lv4_datas = [
                'UMID'=>$uuid,
                'PROVINCE_ID'=>$PROVINCE_ID,
                'CITY_ID'=>$CITY_ID,
                'COUNTY_ID'=>0,
                'STREET_ID'=>0,
            ];

            $coids = Collection::make($lv4)->map(function($t) use (&$lv4_datas){
                list($cid,$sid,$coid) = explode('-',$t);
                if($lv4_datas['COUNTY_ID']!=$cid){
                    $lv4_datas['COUNTY_ID'] = $cid;
                }
                if($lv4_datas['STREET_ID']!=$sid){
                    $lv4_datas['STREET_ID'] = $sid;
                }
                return $coid;
            })->toArray();
            //var_dump($coids);
            $lv4_datas['LEVEL'] = 4;
            $lv4_datas['AREA_IDS'] = implode(',',$coids);

            $ha->saveSettings($lv4_datas);
        }else{
            $ha->where('UMID',$uuid)->where('LEVEL',4)->delete();
        }

        if(!empty($lv3)){
            $lv3_datas = [
                'UMID'=>$uuid,
                'PROVINCE_ID'=>$PROVINCE_ID,
                'CITY_ID'=>$CITY_ID,
                'COUNTY_ID'=>0,
                'STREET_ID'=>0,
            ];
            $sids = Collection::make($lv3)->map(function($t) use (&$lv3_datas){
                list($cid,$sid) = explode('-',$t);
                if($lv3_datas['COUNTY_ID']!=$cid){
                    $lv3_datas['COUNTY_ID'] = $cid;
                }

                return $sid;
            })->filter(function($t) use($lv4_datas){
                return $t != $lv4_datas['STREET_ID'];
            })->toArray();
            $lv3_datas['LEVEL'] = 3;
            $lv3_datas['AREA_IDS'] = implode(',',$sids);

            $ha->saveSettings($lv3_datas);
        }else{
            $ha->where('UMID',$uuid)->where('LEVEL',3)->delete();
        }

        if(!empty($lv2)){

            $cids = Collection::make($lv2)->filter(function($t) use ($lv3_datas){
                return $t != $lv3_datas['COUNTY_ID'];
            })->toArray();

            $lv2_datas = [
                'UMID'=>$uuid,
                'PROVINCE_ID'=>$PROVINCE_ID,
                'CITY_ID'=>$CITY_ID,
                'COUNTY_ID'=>0,
                'STREET_ID'=>0,
                'LEVEL'=>2,
                'AREA_IDS'=>implode(',',$cids),
            ];

            $ha->saveSettings($lv2_datas);
        }else{
            $ha->where('UMID',$uuid)->where('LEVEL',2)->delete();
        }

        if(!empty($lv1)){

            $ids = Collection::make($lv1)->filter(function($t) use ($lv2_datas){
                return $t != $lv2_datas['CITY_ID'];
            })->toArray();


            $lv1_datas = [
                'UMID'=>$uuid,
                'PROVINCE_ID'=>$PROVINCE_ID,
                'CITY_ID'=>$CITY_ID,
                'COUNTY_ID'=>0,
                'STREET_ID'=>0,
                'LEVEL'=>1,
                'AREA_IDS'=>implode(',',$ids),
            ];
            $ha->saveSettings($lv1_datas);
        }else{
            $ha->where('UMID',$uuid)->where('LEVEL',1)->delete();
        }

        $this->success('配置成功',url('UserManagers/index'));
    }

    protected function saveAccess(Request $request){
        $uuid = $request->param('id',0,'int');
        $lv1 = $request->param('lv1',[]);
        $lv2 = $request->param('lv2',[]);
        $lv3 = $request->param('lv3',[]);
        $lv4 = $request->param('lv4',[]);

        if (!$this->checkMUid($uuid)) {
            $this->error('权限不足');
        }
        $umpower = new UserManagerPower();
        // 先移除所有该管理员的管辖设置
        $umpower->where('UMID',$uuid)->delete();

        $PROVINCE_ID = $request->param('PROVINCE_ID');
        $CITY_ID = $request->param('CITY_ID');

        if (!empty($lv4)) {
            $lv4_datas = [
                'UMID' => $uuid,
                'PROVINCE_ID' => $PROVINCE_ID,
                'CITY_ID' => $CITY_ID,
                'COUNTY_ID' => 0,
                'STREET_ID' => 0,
                'COMMUNITY_ID' => 0,
                'LEVEL' => self::POWER_LEVEL_COMMUNITY
            ];
            $coids = Collection::make($lv4)->map(function($t) use (&$lv4_datas){
                list($cid, $sid, $coid) = explode('-', $t);
                $lv4_datas['COUNTY_ID'] = $cid;
                $lv4_datas['STREET_ID'] = $sid;
                $lv4_datas['COMMUNITY_ID'] = $coid;
                return $coid;
            })->toArray();
            $lv4_datas['AREA_IDS'] = implode(',',$coids);

            $umpower->savePowerSettings($lv4_datas);
        }
        else if (!empty($lv3)) {
            $lv3_datas = [
                'UMID' => $uuid,
                'PROVINCE_ID' => $PROVINCE_ID,
                'CITY_ID' => $CITY_ID,
                'COUNTY_ID' => 0,
                'STREET_ID' => 0,
                'LEVEL' => self::POWER_LEVEL_STREET
            ];
            $sids = Collection::make($lv3)->map(function($t) use (&$lv3_datas){
                list($cid, $sid) = explode('-',$t);
                $lv3_datas['COUNTY_ID'] = $cid;
                $lv3_datas['STREET_ID'] = $sid;
                return $sid;
            })->toArray();
            $lv3_datas['AREA_IDS'] = implode(',', $sids);

            $umpower->savePowerSettings($lv3_datas);
        }
        else if (!empty($lv2)) {
            $lv2_datas = [
                'UMID' => $uuid,
                'PROVINCE_ID' => $PROVINCE_ID,
                'CITY_ID' => $CITY_ID,
                'COUNTY_ID' => 0,
                'STREET_ID' => 0,
                'LEVEL' => self::POWER_LEVEL_COUNTY
            ];
            $cids = Collection::make($lv2)->map(function($t) use (&$lv2_datas) {
                $lv2_datas['COUNTY_ID'] = $t;
                return $t;
            })->toArray();
            $lv2_datas['AREA_IDS'] = implode(',',$cids);
            $umpower->savePowerSettings($lv2_datas);
        }
        else if(!empty($lv1)) {
            $lv1_datas = [
                'UMID' => $uuid,
                'PROVINCE_ID' => $PROVINCE_ID,
                'CITY_ID' => $CITY_ID,
                'COUNTY_ID' => 0,
                'STREET_ID' => 0,
                'LEVEL' => self::POWER_LEVEL_CITY,
                'AREA_IDS' => $CITY_ID
            ];

            $umpower->savePowerSettings($lv1_datas);
        }

        $this->success('配置成功',url('UserManagers/index'));
    }


    protected function saveCreate(Request $request){
        $ref = $request->post('ref') ? : url('UserManagers/index');

        $mobile = $request->param('MOBILE','','trim');


        $exist = UserManagersModel::where('ISDEL','=',0)
            ->where('MOBILE','=',$mobile)
            ->count();

        if ($exist) {
            $this->error('新的手机号已存在，请换一个');
        }

        // 考虑到“籍贯”和“现住址”信息对管理人员没有实际意义，所以暂时将其忽略
        /*
        $domicileplaceids = $request->param('domicileplace',[]);
        if (empty($domicileplaceids) || empty($domicileplaceids[2])) {
            $this->error('请完整地选择籍贯');
        }
        $domicileplace = Upareatable::where('UPAREAID','in',$domicileplaceids)->order('UPAREAID','asc')->select()->column('NAME');

        $liveplaceids = $request->param('liveplace',[]);
        if (empty($liveplaceids) || empty($liveplaceids[2])) {
            $this->error('请完整地选择现住址');
        }
        $liveplace = Upareatable::where('UPAREAID','in',$liveplaceids)->order('UPAREAID','asc')->select()->column('NAME');
        */

        $levelareaids = $request->param('levelarea',[]);
        $levelareaids = array_filter($levelareaids);
        if (empty($levelareaids)) {
            $this->error('缺少所在社区信息');
        }
        $levelarea = Subareas::where('CODE12','in', $levelareaids)
            ->order('ID','asc')->select()->column('NAME');

        $dmmcs = $request->param('dmmc', []);
        $dmmcs = array_filter($dmmcs);
        if (empty($dmmcs) || empty($dmmcs[1])) {
            $this->error('缺少所属禁毒办信息');
        }

        $data = [
            'NAME'=>$request->param('NAME','','trim'),
            'GENDER'=>$request->param('GENDER','','trim'),
            'ID_NUMBER_TYPE'=>$request->param('ID_NUMBER_TYPE','','trim'),
            'ID_NUMBER'=>$request->param('ID_NUMBER','','trim'),
            'MOBILE'=>$request->param('MOBILE','','trim'),
            'QQ'=>$request->param('QQ','','trim'),
            'WECHAT'=>$request->param('WECHAT','','trim'),
            'JOB'=>$request->param('JOB','','trim'),
            'SPECIAL_ABILITY'=>$request->param('SPECIAL_ABILITY','','trim'),
            'UNIT_NAME'=>$request->param('UNIT_NAME','','trim'),
            'UNIT_ADDRESS'=>$request->param('UNIT_ADDRESS','','trim'),
            'ADDRESS'=>$request->param('ADDRESS','','trim'),
            'MARK'=>$request->param('MARK','','trim'),

            'COUNTY_ID_12'=>$levelareaids[0],
            'STREET_ID'=>isset($levelareaids[1]) ? $levelareaids[1]: 0,
            'COMMUNITY_ID'=>isset($levelareaids[2]) ? $levelareaids[2]: 0,
            'LIVE_PLACE'=>implode(' ', $levelarea),

            'DMM_ID'=>end($dmmcs),
            'DMMC_IDS'=>implode(',',$dmmcs),

//            'PROVINCE_ID'=>$liveplaceids[0],
//            'CITY_ID'=>$liveplaceids[1],
//            'COUNTY_ID'=>$liveplaceids[2],
//            'DOMICILE_PLACE'=>implode(' ',$domicileplace),
//            'DOMICILE_IDS'=>implode(',',$domicileplaceids)
        ];

        if($request->has('PWSD')){
            $pwsd = $request->post('PWSD');
            $stat = Str::random(6);
            $data['PWSD'] = create_pwd($pwsd,$stat);
            $data['SALT'] = $stat;
        }

        $data['CHECK_USER_ID'] = session('user_id');
        $data['CHECK_USER_NAME'] = session('name');
        $data['STATUS'] = 1;
        $data['CHECK_OK_TIME'] = Carbon::now()->toDateTimeString();

        $manager = new UserManagersModel();
        $manager->COUNTY_ID_12 = $data['COUNTY_ID_12'];
        $data['UCODE'] = $manager->createNewUCode($data['DMM_ID']);


        $v = new UserManagersVer();
        if (!$v->scene('htadd')->check($data)) {
            $this->error($v->getError());
        }

        $img = $this->uploadImage($request,['usermanagers/']);

        if(isset($img['images'])){

            $data['HEAD_IMG'] = $img['images'][0];
        }

        $manager->save($data);

        $this->success('保存人员资料成功',$ref);

    }
    /**
     * 保存资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    protected function save(Request $request)
    {
        $ref = $request->post('ref') ? : url('UserManagers/index');

        $id = $request->param('ID',0,'int');
        if(!$id){
            $this->error('访问错误');
        }
        $manager = UserManagersModel::where('ISDEL','=',0)->find($id);
        if(!$manager){
            $this->error('未找到相关人员信息');
        }

        if(!$this->checkMUid($manager->ID)){
            $this->error('权限不足');
        }

        $mobile = $request->param('MOBILE','','trim');
        if($manager->MOBILE != $mobile){

            $exist = UserManagersModel::where('ISDEL','=',0)
                ->where('MOBILE','=',$mobile)
                ->whereRaw('ID!='.$manager->ID)
                ->count();

            if($exist){
                $this->error('新的手机号已存在，请换一个');
            }
        }
        // 考虑到“籍贯”和“现住址”信息对管理人员没有实际意义，所以暂时将其忽略
        /*
        $domicileplaceids = $request->param('domicileplace',[]);
        if (empty($domicileplaceids) || empty($domicileplaceids[2])) {
            $this->error('请完整地选择籍贯');
        }
        $domicileplace = Upareatable::where('UPAREAID','in',$domicileplaceids)->order('UPAREAID','asc')->select()->column('NAME');

        $liveplaceids = $request->param('liveplace',[]);
        if (empty($liveplaceids) || empty($liveplaceids[2])) {
            $this->error('请完整地选择现住址');
        }
        $liveplace = Upareatable::where('UPAREAID','in',$liveplaceids)->order('UPAREAID','asc')->select()->column('NAME');
        */

        $levelareaids = $request->param('levelarea',[]);
        $levelareaids = array_filter($levelareaids);
        if (empty($levelareaids)) {
            $this->error('缺少所在社区信息');
        }
        $levelarea = Subareas::where('CODE12','in', $levelareaids)
            ->order('ID','asc')->select()->column('NAME');

        $dmmcs = $request->param('dmmc', []);
        $dmmcs = array_filter($dmmcs);
        if (empty($dmmcs) || empty($dmmcs[1])) {
            $this->error('缺少所属禁毒办信息');
        }

        $ckeck_result = $request->param('check_result','','trim');

        $data = [
            'NAME'=>$request->param('NAME','','trim'),
            'GENDER'=>$request->param('GENDER','','trim'),
            'ID_NUMBER_TYPE'=>$request->param('ID_NUMBER_TYPE','','trim'),
            'ID_NUMBER'=>$request->param('ID_NUMBER','','trim'),
            'MOBILE'=>$request->param('MOBILE','','trim'),
            'QQ'=>$request->param('QQ','','trim'),
            'WECHAT'=>$request->param('WECHAT','','trim'),
            'JOB'=>$request->param('JOB','','trim'),
            'SPECIAL_ABILITY'=>$request->param('SPECIAL_ABILITY','','trim'),
            'UNIT_NAME'=>$request->param('UNIT_NAME','','trim'),
            'UNIT_ADDRESS'=>$request->param('UNIT_ADDRESS','','trim'),
            'ADDRESS'=>$request->param('ADDRESS','','trim'),
            'MARK'=>$request->param('MARK','','trim'),

            'COUNTY_ID_12'=>$levelareaids[0],
            'STREET_ID'=>isset($levelareaids[1]) ? $levelareaids[1]: 0,
            'COMMUNITY_ID'=>isset($levelareaids[2]) ? $levelareaids[2]: 0,
            'LIVE_PLACE'=>implode(' ', $levelarea),

            'DMM_ID'=>end($dmmcs),
            'DMMC_IDS'=>implode(',',$dmmcs),

//            'PROVINCE_ID'=>$liveplaceids[0],
//            'CITY_ID'=>$liveplaceids[1],
//            'COUNTY_ID'=>$liveplaceids[2],
//            'DOMICILE_PLACE'=>implode(' ',$domicileplace),
//            'DOMICILE_IDS'=>implode(',',$domicileplaceids)
        ];

        if ($request->has('PWSD')) {
            $pwsd = $request->post('PWSD');
            $stat = Str::random(6);
            $data['PWSD'] = create_pwd($pwsd,$stat);
            $data['SALT'] = $stat;
        }

        $isedit = true;
        //审批通过
        if ($ckeck_result == 1) {
            $data['CHECK_USER_ID'] = session('user_id');
            $data['CHECK_USER_NAME'] = session('name');
            $data['STATUS'] = 1;
            $data['CHECK_OK_TIME'] = Carbon::now()->toDateTimeString();
            $isedit = false;
        }//不通过
        elseif ($ckeck_result == 2) {
            $data['CHECK_USER_ID'] = session('user_id');
            $data['CHECK_USER_NAME'] = session('name');
            $data['STATUS'] = 2;
            $data['CHECK_FAIL_TIME'] = Carbon::now()->toDateTimeString();
            $isedit = false;
        }

        if (!$manager->UCODE) {
            $data['UCODE'] = $manager->createNewUCode($data['DMM_ID']);
        }

        $v = new UserManagersVer();
        if(!$v->scene('htedit')->check($data)) {
            $this->error($v->getError());
        }

        $img = $this->uploadImage($request,['usermanagers/']);

        if(isset($img['images'])){
            // 如果存在老的图片，刚将其删除
            if($manager->HEAD_IMG){
                WaitDeleteFiles::addOne([
                    'table'=>'usermanagers',
                    'id'=>$manager->ID,
                    'path'=>$manager->HEAD_IMG
                ]);
            }

            $manager->HEAD_IMG = $img['images'][0];
        }

        $manager->save($data);


        if ($isedit) {
            $this->success('保存人员资料成功', $ref);
        }else{
            $this->success('审批人员资料成功', $ref);
        }

    }

    public function logList($id = 0) {
        $data = $this->getLogData($id);

        $list = $data['query']->paginate(self::LOG_PAGE_SIZE, false, [
            'query' => request()->param(),
        ]);

        $js = $this->loadJsCss(array('p:cate/jquery.cate','usermanagers_log'), 'js', 'admin');
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

        $query = UserManagerLogsModel::where('UMID', $id)->order('ADD_TIME DESC');

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

        $fields = ['log_action'];
        $keywords = input('get.keywords','');
        if(!empty($keywords)){
            foreach ($fields as $field) {
                $query->where(strtoupper($field), 'like', '%'. $keywords .'%');
            }
            $is_so = true;
        }
        $param['keywords'] = $keywords;

        return ['query' => $query, 'param' => $param, 'is_so' => $is_so];
    }
}
