<?php

namespace app\common\model;

use app\common\library\UranCheckTime;
use Carbon\Carbon;
use think\Db;
use think\paginator\driver\Bootstrap;

class UserUsers extends BaseModel
{
    public static $utypes = [
        '213'=>'社区戒毒',
        '218'=>'社区康复',
        '212'=>'强制戒毒'
    ];
    public static $utype218 = [
        1=>'2年',
        2=>'3年'
    ];
    const ESTIMATE_DANGER_LEVEL_LIST = [
        1 => '低风险',
        2 => '中风险',
        3 => '高风险'
    ];
    const ASSIGNMENT_STATUS_UNASSIGNED = '未指派';
    const ASSIGNMENT_STATUS_NOT_ALL_ASSIGNED = '未完全指派';
    const ASSIGNMENT_STATUS_ASSIGNED = '已完全指派';
    const ASSIGNMENT_STATUS_RELIVED = '已解除社戒社康';
    const ASSIGNMENT_STATUS_LIST = [
        'zhipai0' => self::ASSIGNMENT_STATUS_UNASSIGNED,
        'zhipai1' => self::ASSIGNMENT_STATUS_NOT_ALL_ASSIGNED,
        'zhipai2' => self::ASSIGNMENT_STATUS_ASSIGNED,
        'zhipai3' => self::ASSIGNMENT_STATUS_RELIVED
    ];

    const STATISTICS_ID_TOTAL = 'total';
    const STATISTICS_NAME_TOTAL = '合计';
    const STATISTICS_OTHER_TITLE = '其它';
    const STATUS_ID_OTHER = 'other';
    const STATUS_NAME_OTHER = '状况不明';
    const STATISTICS_URINE_YEARS = 3;

    protected $pk = 'ID';
    public $table = 'USER_USERS';

    protected static function init()
    {
        //self::observe(UserEvent::class);
    }

    //TODO rewrite this
    public function createNewUUCode(){

        $code = \think\helper\Str::substr(str_shuffle(str_repeat('1234567890', 8)), 0, 8);

        $ucode = 'JD4312'.$code;

        if($this->where('UUCODE', $ucode)->count()){
            return $this->createNewUUCode();
        }else{
            return $ucode;
        }

//
//        $code = 1;
////        $dmm = Dmmcs::find($DMM_ID);
//        $last = $this->field('ID,UUCODE')->order('ID','DESC')->find();
//        //$prefix = substr(str_replace('431','',$dmm['PDM']),0,5);
//        $prefix = 'JD431';
//        if($last){
//            $code =  (int) str_replace($prefix,'',$last['UUCODE']);
////            $code = (int) substr($last['UUCODE'],5);
//        }
////        echo $code;
//        $code = sprintf("%05d", ($code+1));
//        return $prefix.$code;

    }

    public function getGenderTextAttr($value,$data){
        $map = [1=>'未知的性别',2=>'男',3=>'女', 4=>'未说明的性别'];
        return isset($map[$data['GENDER']]) ? $map[$data['GENDER']] : $map[1];
    }

    //URAN_RATE
    public function getUranRateTextAttr($value,$data){
        $map = [0=>'',1=>'1次/月',2=>'2月/1次',3=>'3月/1次',4=>'2次/年'];
        return isset($map[$data['URAN_RATE']]) ? $map[$data['URAN_RATE']] : $map[0];
    }

    public function getUtypeTextAttr($value,$data){
        //213社戒 218社康 212是强戒
        //$map = [212=>'强戒', 213=>'社戒', 218=>'社康'];

        return self::$utypes[$data['UTYPE_ID']] ?? '';
    }

    public function getUtype218TextAttr($value,$data){
        if($data['UTYPE_ID'] != 218){
            return '';
        }
        return self::$utype218[$data['UTYPE_ID_218']] ?? '';
    }

    public function getZhiPaiTextAttr($value,$data){
        if($data['COMMUNITY_ID'] == 0){
            return '<span class="badge badge-default">未指派</span>';
        }elseif($data['COMMUNITY_ID'] > 0 && $data['JD_ZHI_PAI_ID'] == 0){
            return '<span class="badge badge-default">未完全指派</span>';
        }elseif($data['COMMUNITY_ID'] > 0 && $data['JD_ZHI_PAI_ID'] == 1){
            return '<span class="badge badge-info">已完全指派</span>';
        }elseif($data['JD_ZHI_PAI_ID'] == 2){
            return '<span class="badge badge-error">已解除社戒社康</span>';
        }

        return '';
    }



    public function getUserIdsByAreas($areas){


        return $this->field('ID')->where(function($t) use($areas) {

            if(!empty($areas['COMMUNITY_IDS'])){
                $t->whereOr('COMMUNITY_ID','in',$areas['COMMUNITY_IDS']);
            }
            if(!empty($areas['STREET_IDS'])){
                $t->whereOr('STREET_ID','in',$areas['STREET_IDS']);
            }
            if(!empty($areas['COUNTY_IDS'])){
                $t->whereOr('COUNTY_ID_12','in',$areas['COUNTY_IDS']);
            }
            if(!empty($areas['CITY_IDS'])){
                $t->whereOr('CITY_ID','in',$areas['CITY_IDS']);
            }
            /*if(!empty($areas['PROVINCE_ID'])){
                return $t->where('PROVINCE_ID','in',$areas['PROVINCE_ID']);
            }*/

            return $t;

        })->where('JD_ZHI_PAI_ID', '<', 2)->select()->column('ID');
    }

    /**
     * 社区戒毒人员(UTYPE_ID=213)
     * 1,第一年12次每个月一次，第二年6次每两个月一次，第三年4次每三个月一次，共不低于22次。
     * 社区康复(UTYPE_ID=218)
     * 1,决定三年的人员第一年6次每两个月一次，第二年4次每三个月一次，第三年每半年一次，共不低于12次，
     * 2,决定两年的每两个月一次，共不低于12次。
     */

    public function uranCheck(){
        $return = [
            'finish_count'=>0,
            'rest_count'=>0,
            'next_uran_time'=>'',
            'rate'=>1,
            'is_completed'=>0
        ];



        $now = Carbon::now();
        $urans = Urans::where('UUID', $this->ID)->where('ISDEL', 0)->order('CHECK_TIME', 'asc')->select()->toArray();
        $return['finish_count'] = count($urans);

        if($this->UTYPE_ID == UranCheckTime::TYPE_213_VALUE){
            $return['rest_count'] = UranCheckTime::TYPE_213_TOTAL - $return['finish_count'];
        }elseif($this->UTYPE_ID == UranCheckTime::TYPE_218_VALUE){
            $return['rest_count'] = UranCheckTime::TYPE_218_TOTAL - $return['finish_count'];
        }

        //没有尿检，默认为下个月的1号
        if($return['finish_count'] == 0){
            $return['next_uran_time'] = $now->addMonth()->firstOfMonth()->toDateString();

            return $return;
        }

        if($return['rest_count'] < 0){
            $return['rest_count'] = 0;
            $return['next_uran_time'] = '';
            return $return;
        }

        

        $first = current($urans);
        $last = end($urans);

//        $lastCheckTime = Carbon::parse($last['CHECK_TIME']);
//        $diffMonths = $lastCheckTime->diffInMonths($first['CHECK_TIME']);

        $next = UranCheckTime::getNextCheckTime($this->UTYPE_ID, $this->UTYPE_ID_218, $return['finish_count'], $first['CHECK_TIME'], $last['CHECK_TIME']);

        $return['next_uran_time'] = $next['next'];
        $return['rate'] = $next['rate'];
        $return['is_completed'] = $next['is_completed'];

        return $return;
    }

    public static function statisticsStatus($pageNO = 1, $pageSize = 20, $condition = []) {
        $userStatus = create_kv(BaseUserStatus::all()->toArray(), 'ID', 'NAME');
        return self::statistics(function ($subSql, $groupBy) use ($userStatus) {
            $userStatusId = array_keys($userStatus);
            foreach ($userStatus as $id => $name) {
                $subSql .= "sum(case USER_STATUS_ID when $id then 1 else 0 end) '$name',";
            }
            $subSql .= "sum(case when USER_STATUS_ID not in (" . implode(',', $userStatusId) . ") then 1 else 0 end) '" . self::STATUS_NAME_OTHER . "'";
            $subSql .= " from user_users A where ISDEL = 0 group by " . implode(',', $groupBy);
            return $subSql;
        }, function ($sql) use ($userStatus) {
            foreach ($userStatus as $id => $name) {
                $sql .= "sum($name) '$name',";
            }
            $sql .= "sum(" . self::STATUS_NAME_OTHER . ") '" . self::STATUS_NAME_OTHER . "'";
            return $sql;
        }, function (&$item) use ($userStatus) {
            $total = $item[self::STATUS_NAME_OTHER];
            foreach ($userStatus as $id => $name) {
                $total += $item[$name];
            }
            $item[self::STATISTICS_NAME_TOTAL] = $total;
        }, $pageSize, $pageNO, $condition);
    }

    public static function statisticsEstimates($pageNO = 1, $pageSize = 20, $condition = []) {
        return self::statistics(function ($subSql, $groupBy) {
            $dangerLevelId = array_keys(self::ESTIMATE_DANGER_LEVEL_LIST);
            foreach (self::ESTIMATE_DANGER_LEVEL_LIST as $id => $name) {
                $subSql .= "sum(case when B.DANGER_LEVEL_ID = $id then 1 else 0 end) '$name',";
            }
            $subSql .= "sum(case when B.DANGER_LEVEL_ID is null or B.DANGER_LEVEL_ID not in (" . implode(',', $dangerLevelId) . ") then 1 else 0 end) '" . self::STATISTICS_OTHER_TITLE . "'";
            $subSql .= " from user_users A";
            $subSql .= " left join (select * from (select * from user_estimates order by add_time desc) C group by UUID) B";
            $subSql .= " on A.ID = B.UUID where ISDEL = 0";
            $subSql .= " group by " . implode(',', $groupBy);
            return $subSql;
        }, function ($sql) {
            foreach (self::ESTIMATE_DANGER_LEVEL_LIST as $id => $name) {
                $sql .= "sum($name) '$name',";
            }
            $sql .= "sum(" . self::STATISTICS_OTHER_TITLE . ") '" . self::STATISTICS_OTHER_TITLE . "'";
            return $sql;
        }, function (&$item) {
            $total = $item[self::STATISTICS_OTHER_TITLE];
            foreach (self::ESTIMATE_DANGER_LEVEL_LIST as $id => $name) {
                $total += $item[$name];
            }
            $item[self::STATISTICS_NAME_TOTAL] = $total;
        }, $pageSize, $pageNO, $condition);
    }

    public static function statisticsAssignment($pageNO = 1, $pageSize = 20, $condition = []) {
        return self::statistics(function ($subSql, $groupBy) {
            $unassigned = self::ASSIGNMENT_STATUS_UNASSIGNED;
            $subSql .= "sum(case when COMMUNITY_ID = 0 then 1 else 0 end) '$unassigned',";
            $notAllAssigned = self::ASSIGNMENT_STATUS_NOT_ALL_ASSIGNED;
            $subSql .= "sum(case when COMMUNITY_ID > 0 and JD_ZHI_PAI_ID = 0 then 1 else 0 end) '$notAllAssigned',";
            $assigned = self::ASSIGNMENT_STATUS_ASSIGNED;
            $subSql .= "sum(case when COMMUNITY_ID > 0 and JD_ZHI_PAI_ID = 1 then 1 else 0 end) '$assigned',";
            $relived = self::ASSIGNMENT_STATUS_RELIVED;
            $subSql .= "sum(case when JD_ZHI_PAI_ID = 2 then 1 else 0 end) '$relived'";
            $subSql .= " from user_users A where ISDEL = 0 group by " . implode(',', $groupBy);
            return $subSql;
        }, function ($sql) {
            $statusList = array_values(self::ASSIGNMENT_STATUS_LIST);
            foreach ($statusList as $name) {
                $sql .= "sum($name) '$name',";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);
            return $sql;
        }, function (&$item) {
            $total = 0;
            $statusList = array_values(self::ASSIGNMENT_STATUS_LIST);
            foreach ($statusList as $name) {
                $total += $item[$name];
            }
            $item[self::STATISTICS_NAME_TOTAL] = $total;
        }, $pageSize, $pageNO, $condition);

    }

    public static function statisticsUrine($pageNO = 1, $pageSize = 20, $condition = []) {
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
        return self::statistics(function ($subSql, $groupBy) use ($fitStatusIds) {
            $whereIn = implode(',', array_keys($fitStatusIds));
            for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
                $year = $i + 1;
                $num = ($i + 1) . "th";
                $subSql .= "sum($num) $num,sum(case";
                foreach ($fitStatusIds as $statusId => $checkTimesList) {
                    $checkTimes = $checkTimesList[$i];
                    $shouldTimes = "ceil(if(months > 12*$year, 12, if(months > 12*$i, months - 12*$i, 0)) / (12 / $checkTimes))";
                    $subSql .= " when USER_STATUS_ID = $statusId then $shouldTimes - $num";
                }
                $subSql .= " end) lack_$num,";
            }
            $subSql = substr($subSql, 0, strlen($subSql) - 1);
            $subSql .= " from (select USER_STATUS_ID,JD_START_TIME,if(JD_START_TIME is not null, TIMESTAMPDIFF(MONTH, JD_START_TIME, DATE_FORMAT(NOW(),'%Y-%m-%d')) + if(JD_START_TIME > now(), 0, 1), 0) months,COUNTY_ID_12,STREET_ID,COMMUNITY_ID,";
            for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
                $num = ($i + 1) . "th";
                $subSql .= "(select count(1) from urans where UUID = A.ID and CHECK_TIME >= DATE_ADD(A.JD_START_TIME,INTERVAL $i year) and CHECK_TIME < DATE_ADD(A.JD_START_TIME,INTERVAL ($i + 1) year)) $num,";
            }
            $subSql = substr($subSql, 0, strlen($subSql) - 1);
            $subSql .= " from user_users A where USER_STATUS_ID in ($whereIn) and ISDEL = 0) A";
            $subSql .= "  group by " . implode(',', $groupBy);
            return $subSql;
        }, function ($sql) {
            for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
                $num = ($i + 1) . "th";
                $sql .= "sum($num) $num,sum(lack_$num) lack_$num,";
            }
            $sql = substr($sql, 0, strlen($sql) - 1);
            return $sql;
        }, function (&$item) use ($userStatus) {
            $totalFinished = $totalMissing = 0;
            for ($i = 0; $i < URINE_CHECK_YEARS; $i++) {
                $num = ($i + 1) . "th";
                $totalFinished += $item[$num];
                $totalMissing += $item["lack_$num"];
            }
            $item['totalFinished'] = $totalFinished;
            $item['totalMissing'] = $totalMissing;
        }, $pageSize, $pageNO, $condition);
    }

    private static function statistics($buildSubSql, $buildSql, $calcTotal, $pageSize, $pageNO, $condition) {
        $subSql = 'select ';
        $subSql .= 'COUNTY_ID_12,(select `NAME` from subareas where CODE12 = A.COUNTY_ID_12) COUNTY_NAME,';
        $groupBy = ['COUNTY_ID_12'];
        $orderBy = ['COUNTY_ID_12'];
        if (!empty($condition['area1'])) {
            $subSql .= 'STREET_ID,(select `NAME` from subareas where CODE12 = A.STREET_ID) STREET_NAME,';
            $where['COUNTY_ID_12'] = $condition['area1'];
            array_push($groupBy, 'STREET_ID');
            array_push($orderBy, 'STREET_ID');
        }
        if (!empty($condition['area2'])) {
            $subSql .= 'COMMUNITY_ID,(select `NAME` from subareas where CODE12 = A.COMMUNITY_ID) COMMUNITY_NAME,';
            $where['STREET_ID'] = $condition['area2'];
            array_push($groupBy, 'COMMUNITY_ID');
            array_push($orderBy, 'COMMUNITY_ID');
        }
        if (!empty($condition['area3'])) {
            $where['COMMUNITY_ID'] = $condition['area3'];
        }
        $subSql = $buildSubSql($subSql, $groupBy);

        // 统计符合条件的数据总数
        $sql = 'select ';
        $sql = $buildSql($sql);
        $sql .= " from ($subSql) B where (COUNTY_ID_12 = 0 or COUNTY_NAME is not null)";
        if (!empty($where)) {
            $sql .= ' and (';
            $and = ' and ';
            foreach ($where as $name => $value) {
                $sql .= "$name='$value' $and";
            }
            $sql = substr($sql, 0, strlen($sql) - strlen($and));
            $sql .= ')';
        }
        $allList = db()->query($sql);
        if (!empty($allList)) {
            foreach ($allList as &$item) {
                $calcTotal($item);
            }
        }

        // 获取分页数据
        $sql = "select * from ($subSql) B where (COUNTY_ID_12 = 0 or COUNTY_NAME is not null)";
        if (!empty($where)) {
            $sql .= ' and (';
            $and = ' and ';
            foreach ($where as $name => $value) {
                $sql .= "$name='$value' $and";
            }
            $sql = substr($sql, 0, strlen($sql) - strlen($and));
            $sql .= ')';
        }
        $sql .= " order by " . implode(',', $orderBy);
        $limit = $pageSize;
        $offset = ($pageNO - 1) * $limit;
        $sql .= ' limit ?,?';
        $pageList = db()->query($sql, [$offset,$limit]);
        if (!empty($pageList)) {
            foreach ($pageList as &$item) {
                $calcTotal($item);
            }
        }

        // 获取统计总记录数
        $sql = "select count(*) from ($subSql) B where (COUNTY_ID_12 = 0 or COUNTY_NAME is not null)";
        if (!empty($where)) {
            $sql .= ' and (';
            $and = ' and ';
            foreach ($where as $name => $value) {
                $sql .= "$name='$value' $and";
            }
            $sql = substr($sql, 0, strlen($sql) - strlen($and));
            $sql .= ')';
        }
        $pageTotals = db()->query($sql);
        foreach ($pageTotals as $pageTotal) {
            foreach ($pageTotal as $key => $value) {
                $pageTotal = $value;
            }
        }
        return [
            'allList' => $allList,
            'pageTotal' => $pageTotal,
            'pageList' => $pageList
        ];
    }

}
