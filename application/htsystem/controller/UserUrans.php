<?php

namespace app\htsystem\controller;

use app\common\model\BaseUserStatus;
use Carbon\Carbon;
use think\paginator\driver\Bootstrap;
use think\Request;
use app\common\model\Urans;
use app\common\model\UserUsers;
use app\common\model\Areas,
    app\common\model\AreasSubs;
use app\common\model\BaseSexType;

class UserUrans extends Common
{

    protected $MODULE = 'UserUser';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($uuid = 0)
    {
        $data = $this->doSearch($uuid);
        $list = $data['query']
            ->order('CHECK_TIME', 'desc')
            ->paginate(self::PAGE_SIZE, false, ['query'=>request()->param()]);

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userurans_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('is_so', $data['is_so']);
        $this->assign('param', $data['param']);
        if (!empty($uuid)) {
            $this->assign('uuid', $uuid);
        }
        return $this->fetch();
    }

    protected function doSearch($uuid) {

        $is_so = false;
        $query = Urans::alias('A')
            ->leftJoin('user_users B', 'A.UUID = B.ID')
            ->where('A.ISDEL',0)
            ->where('B.ISDEL', 0)
            ->with([
                'uuser' => function($query){
                    return $query->field('ID,NAME');
                }
            ])
            ->field(['A.ID', 'A.URAN_CODE', 'A.UUID', 'A.UNIT_NAME', 'A.RESULT', 'A.CHECK_TIME']);

        $fields = ['UUID', 'UNIT_NAME', 'URAN_CODE'];
        $param['keywords'] = input('get.keywords','');

        if (!empty($uuid)) {
            $query->where('UUID', $uuid);
        }

        if (!empty($param['keywords'])) {
            $query->where(function ($query) use ($fields, $param) {
                $query->where(implode('|', $fields), 'like', '%'. $param['keywords'] .'%');
                $query->whereOr('B.NAME', 'like', "%" . $param['keywords']. "%");
            });
            $is_so = true;
        }

        $param['sdate'] = input('get.sdate', '');
        $param['edate'] = input('get.edate', '');

        if ($param['sdate'] && !$param['edate']) {

            $query->whereTime('CHECK_TIME', '>=', Carbon::parse($param['sdate'])->startOfDay()->toDateTimeString());
            $is_so = true;
        } elseif (!$param['sdate'] && $param['edate']) {

            $query->whereTime('CHECK_TIME', '<=', Carbon::parse($param['edate'])->endOfDay()->toDateTimeString());
            $is_so = true;
        } elseif ($param['sdate'] && $param['edate']) {

            $query->whereTime('CHECK_TIME', 'between', [

                Carbon::parse($param['sdate'])->startOfDay()->toDateTimeString(),
                Carbon::parse($param['edate'])->endOfDay()->toDateTimeString()
            ]);
            $is_so = true;
        }

        $param['result'] = input('get.result', '0');
        if (in_array($param['result'], ['1', '2'])) {
            if($param['result'] == '1'){
                $query->where('RESULT', '阴性');
            }else{
                $query->where('RESULT', '阳性');
            }
            $is_so = true;
        }

        $param['a1'] = input('area1', '');
        $param['a2'] = input('area2', '');
        $param['a3'] = input('area3', '');
        if ($param['a3'] > 0) {
            $query->where('B.COMMUNITY_ID', $param['a3']);
            $is_so = true;
        }
        else if ($param['a2'] > 0) {
            $query->where('B.STREET_ID', $param['a2']);
            $is_so = true;
        }
        else if ($param['a1'] > 0) {
            $query->where('B.COUNTY_ID_12', $param['a1']);
            $is_so = true;
        }

        $query->where(function ($query) {
            $muids = $this->getManageUUids();
            if ($muids != 'all') {
                $query->whereIn('UUID', $muids);
            }
        });

        return ['query' => $query, 'param' => $param, 'is_so' => $is_so];
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
        $info = Urans::with([
            'dmmc'=>function($query){
                $query->field('ID,DEPTCODE as DM,DEPTNAME as DMMC');
            },
            'uuser'=>function($query){
                $query->field('ID,UUCODE,NAME,ID_NUMBER,HEAD_IMG');
            },
            'muser'=>function($query){
                $query->field('ID,NAME,UCODE');
            }
        ])
            ->where(function ($query){
                $ids = $this->getManageUUids();
                if($ids != 'all'){
                    $query->whereIn('UUID', $ids);
                }
            })
            ->where('ISDEL','=',0)->find($id);

        if(!$info){
            $this->error('该报告不存在或已删除');
        }
        $areas = Areas::where('ID','in',[$info->PROVINCE_ID,$info->CITY_ID,$info->COUNTY_ID])
            ->order('ID','asc')->select()->column('NAME');
        $areasubs = [];
        if($info->STREET_ID>0){
            $areasubs = AreasSubs::where('ID','in',[$info->STREET_ID,$info->COMMUNITY_ID])
                ->where('ACTIVE',1)
                ->order('ID','asc')->select()->column('NAME');
        }

        $info->IMGS->map(function($t){
            $t->IMG_URL = build_http_img_url($t->SRC_PATH);
            return $t;
        });

        $info->AREAS = implode(' ',array_merge($areas,$areasubs));
        $info->uuser->HEAD_IMG_URL = build_http_img_url($info->uuser->HEAD_IMG);

        $this->assign('info',$info);
        return $this->fetch();
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
        $info = Urans::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该报告事项不存在或已删除');
        }

        if (!$this->checkUUid($info->UUID)) {
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        $this->logAdmin(self::OPER_TYPE_DELETE, '删除尿检报告事项', '尿检报告事项删除成功', $info->UUID);

        $this->success('删除成功');
    }

    /**
     * 打印记录表
     * @param int $id
     * @return mixed
     */
    public function printSheet($id = 0) {
        $user = UserUsers::find($id);
        if (empty($user)) {
            $this->error('用户不存在或已删除.');
        }
        if (!$this->checkUUid($id)) {
            $this->error('权限不足.');
        }
        $testList = Urans::where('UUID', $id)
            ->field('A.*,B.NAME')
            ->alias('A')
            ->leftJoin('user_managers B', 'A.UMID = B.ID')
            ->order('A.CHECK_TIME ASC')->all();

        foreach ($testList as $test) {
            $test->CHECK_TIME = date('Y-m-d', strtotime($test->CHECK_TIME));
        }
        $genders = BaseSexType::all();
        foreach ($genders as $gender) {
            if ($gender->ID == $user->GENDER) {
                $user->GENDER = $gender->NAME;
                break;
            }
        }
        $this->assign('user', $user);
        $this->assign('testList', $testList);
        $emptyRows = 15 - count($testList);
        $this->assign('emptyRows', $emptyRows > 0 ? $emptyRows : 0);

        $this->logAdmin(self::OPER_TYPE_QUERY, '打印尿检报告记录表', '尿检报告记录表打印成功', $user->ID);

        return $this->fetch('print');
    }

    public function howItGoes(Request $request) {
        $userStatus = create_kv(BaseUserStatus::all()->toArray(), 'ID', 'NAME');
        $fitStatusIds = [];
        foreach ($userStatus as $id => $name) {
            if ($name == STATUS_COMMUNITY_DETOXIFICATION) {
                $fitStatusIds[$id] = URINE_CHECK_RATE_DETOXIFICATION;
                continue;
            }
            if ($name == STATUS_COMMUNITY_RECOVERING) {
                $fitStatusIds[$id] = URINE_CHECK_RATE_RECOVERING;
                continue;
            }
        }
        $whereIn = implode(',', array_keys($fitStatusIds));

        $subSql = "select *,";
        for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
            $year = $i + 1;
            $subSql .= "case";
            foreach ($fitStatusIds as $statusId => $checkTimesList) {
                $checkTimes = $checkTimesList[$i];
                $subSql .= " when USER_STATUS_ID = $statusId then FLOOR(IF(MONTHS > 12*$year, 12, IF(MONTHS > 12*$i, MONTHS - 12*$i, 0)) / (12 / $checkTimes))";
            }
            $subSql .= " end SHOULD_$year,";
        }
        $subSql = substr($subSql, 0, -1);
        $subSql .= " from (select ID,`NAME`,USER_STATUS_ID,USER_STATUS_NAME,JD_START_TIME,if(JD_START_TIME is not null, TIMESTAMPDIFF(MONTH, JD_START_TIME, DATE_FORMAT(now(),'%Y-%m-%d')), 0) MONTHS,";
        $subSql .= "COUNTY_ID_12,STREET_ID,COMMUNITY_ID,concat_ws(' ', (select `NAME` from subareas where CODE12 = COUNTY_ID_12),(select `NAME` from subareas where CODE12 = STREET_ID),(select `NAME` from subareas where CODE12 = COMMUNITY_ID)) AREA,";

        $shouldNames = $finishedNames = [];
        for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
            $year = $i + 1;
            if (!isset($shouldNames[$i])) {
                $shouldNames[$i] = "SHOULD_$year";
            }
            if (!isset($finishedNames[$i])) {
                $finishedNames[$i] = "FINISHED_$year";
            }
            $subSql .= "case";
            foreach ($fitStatusIds as $statusId => $checkTimesList) {
                $checkTimes = $checkTimesList[$i];
                $subSql .= " when USER_STATUS_ID = $statusId then ";
                $interval = 12 / $checkTimes;
                for ($n = 0; $n < $checkTimes; $n++) {
                    $from = $n * $interval + $i * 12;
                    $to = ($n + 1) * $interval + $i * 12;
                    $subSql .= "(select if(count(1) >= 1, 1, 0) from urans where UUID = A.ID and ISDEL = 0 and CHECK_TIME >= DATE_ADD(A.JD_START_TIME,INTERVAL $from MONTH) and CHECK_TIME < DATE_ADD(DATE_ADD(A.JD_START_TIME,INTERVAL $to MONTH),INTERVAL 1 DAY))+";
                }
                $subSql = substr($subSql, 0, -1);
            }
            $subSql .= " end FINISHED_$year,";
        }
        $subSql = substr($subSql, 0, -1);
        $subSql .= " from user_users A where USER_STATUS_ID in ($whereIn) and ISDEL = 0";

        $where = [];
        $powerLevel = $this->getPowerLevel();
        $area1 = $request->param('area1', 0);
        $area2 = $request->param('area2', 0);
        $area3 = $request->param('area3', 0);
        if (POWER_LEVEL_COUNTY == $powerLevel) {
            $where['COUNTY_ID_12'] = session('info')['POWER_COUNTY_ID_12'];
            $where['STREET_ID'] = $area2;
            $where['COMMUNITY_ID'] = $area3;
            $is_so = !empty($area2) || !empty($area3);
        }
        elseif (POWER_LEVEL_STREET == $powerLevel) {
            $where['COUNTY_ID_12'] = session('info')['POWER_COUNTY_ID_12'];
            $where['STREET_ID'] = session('info')['POWER_STREET_ID'];
            $where['COMMUNITY_ID'] = $area3;
            $is_so = !empty($area3);
        }
        elseif (POWER_LEVEL_COMMUNITY == $powerLevel) {
            $where['COUNTY_ID_12'] = session('info')['POWER_COUNTY_ID_12'];
            $where['STREET_ID'] = session('info')['POWER_STREET_ID'];
            $where['COMMUNITY_ID'] = session('info')['POWER_COMMUNITY_ID'];
        }
        else {
            $where['COUNTY_ID_12'] = $area1;
            $where['STREET_ID'] = $area2;
            $where['COMMUNITY_ID'] = $area3;
            $is_so = !empty($area1) || !empty($area2) || !empty($area3);
        }
        if (!empty($where)) {
            foreach ($where as $name => $value) {
                if (empty($value)) {
                    continue;
                }
                $subSql .= " and $name = '$value'";
            }
        }
        $subSql .= ") A";

        $finishStatus = $request->param('finishStatus', '', 'trim');

        $sql = "select count(1) from ($subSql) A where 1 = 1";
        $sql = $this->setWhere($sql, $finishStatus, $shouldNames, $finishedNames);
        $rows = db()->query($sql);
        foreach ($rows as $row) {
            foreach ($row as $index => $value) {
                $total = $value;
            }
        }

        $sql = "select ID,`NAME`,AREA,USER_STATUS_ID,USER_STATUS_NAME,JD_START_TIME,COUNTY_ID_12,STREET_ID,COMMUNITY_ID,";
        for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
            $year = $i + 1;
            $finishedName = "FINISHED_$year";
            $missingName = "MISSING_$year";
            $sql .= "FINISHED_$year $finishedName,case";
            foreach ($fitStatusIds as $statusId => $checkTimesList) {
                $sql .= " when USER_STATUS_ID = $statusId then if(SHOULD_$year >= FINISHED_$year, SHOULD_$year - FINISHED_$year, 0)";
            }
            $sql .= " end $missingName,";
        }
        $sql = substr($sql, 0, -1);
        $sql .= " from ($subSql) A where 1 = 1";

        $sql = $this->setWhere($sql, $finishStatus, $shouldNames, $finishedNames);

        $sql .= " order by COUNTY_ID_12,STREET_ID,COMMUNITY_ID";
        $pageNO = $request->param('page', 1);
        if ($pageNO < 1) {
            $pageNO = 1;
        }
        $limit = self::PAGE_SIZE;
        $offset = ($pageNO - 1) * $limit;
        $sql .= ' limit ?,?';
        $rows = db()->query($sql, [$offset,$limit]);

        foreach ($rows as &$row) {
            $row['TOTAL_FINISHED'] = $row['TOTAL_MISSING'] = 0;
            for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
                $year = $i + 1;
                $row['TOTAL_FINISHED'] += $row["FINISHED_$year"];
                $row['TOTAL_MISSING'] += $row["MISSING_$year"];
            }
        }

        $paginator = Bootstrap::make($rows, self::PAGE_SIZE, $pageNO, $total, false,
            ['path'=> Bootstrap::getCurrentPath(), 'query' => $request->param()]
        );

        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userurans_howitgoes'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('is_so', empty($is_so) ? false : true);
        $this->assign('param', [
            'area1' => $where['COUNTY_ID_12'],
            'area2' => $where['STREET_ID'],
            'area3' => $where['COMMUNITY_ID'],
            'finishStatus' => $finishStatus
        ]);
        $this->assign('powerLevel', $powerLevel);
        $this->assign('total', $total);
        $this->assign('rows', $rows);
        $this->assign('page', $paginator->render());
        return $this->fetch();
    }

    private function setWhere($sql, $finishStatus, $shouldNames, $finishedNames) {
        if (!empty($finishStatus)) {
            $correctCondition = "(";
            $incorrectCondition = "(";
            for ($i = 0; $i < count($shouldNames); $i++) {
                $correctCondition .= "$shouldNames[$i] <= $finishedNames[$i]";
                $incorrectCondition .= "$shouldNames[$i] > $finishedNames[$i]";
                if ($i < count($shouldNames) - 1) {
                    $correctCondition .= " and ";
                    $incorrectCondition .= " or ";
                }
            }
            $correctCondition .= ")";
            $incorrectCondition .= ")";
            if ($finishStatus == 'CORRECT') {
                $sql .= " and JD_START_TIME IS NOT NULL and $correctCondition";
            }
            elseif ($finishStatus == 'INCORRECT') {
                $sql .= " and JD_START_TIME IS NULL or $incorrectCondition";
            }
        }
        return $sql;
    }

}
