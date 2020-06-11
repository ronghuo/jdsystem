<?php
namespace app\index\controller;

use app\common\model\Agreement;
use app\common\model\AgreementImgs;
use app\common\model\BXdry;
use app\common\library\Mylog;
use app\common\model\UserChangeLog;
use think\image\Exception;
use app\common\model\UserUsers;
use app\common\model\BaseNationType,
    app\common\model\BaseNationalityType,
    app\common\model\BaseCultureType,
    app\common\model\BaseWorkStatus,
    app\common\model\BaseMarryType,
    app\common\model\Subareas,
    app\common\model\NbAuthDept;
use app\index\tmodel\TXdcz;
use Carbon\Carbon;
use app\common\model\UserManagers;
use app\common\model\UserManagerPower;
use app\common\model\HelperAreas;
use app\htsystem\model\Admins;

class Data
{

    public function index()
    {
        return 'hello.';
    }

    public function resetUserStatus(){
        //212是强戒
        UserUsers::where('UTYPE_ID', 212)
            ->where('USER_STATUS_ID', 0)
            ->update(['USER_STATUS_ID'=>5]);
        //213社戒
        UserUsers::where('UTYPE_ID', 213)
            ->where('USER_STATUS_ID', 0)
            ->update(['USER_STATUS_ID'=>6]);
        //218社康
        UserUsers::where('UTYPE_ID', 218)
            ->where('USER_STATUS_ID', 0)
            ->update(['USER_STATUS_ID'=>7]);

        return 'ok';
    }

    public function addManagers(){

        //echo substr('17520475173', -6);exit;

        $file = fopen("./doc/managers.csv","r");

        $data = [];
        while(! feof($file)) {
//            $line = fgetcsv($file);
            $row = fgets($file);
            //echo  $row.PHP_EOL;
            $line = explode(',', trim($row));

//            print_r($line);

            $phones = explode(' ', $line[5]);
            $phone = array_filter($phones);

            $data[] = [
                'name'=>$line[1],
                'gender'=>$line[2] ? 3 : 2,
                'dw_name'=>$line[3],
                'job_title'=>$line[4],
                'phone'=>array_shift($phone),
                'is_admin'=>$line[6],
                'area'=>$line[7] ?? '',
                'dw_ids'=>str_replace('"', '', implode(',', [$line[8], $line[9], $line[10]])),
                'mark'=>implode(',', $phone)
            ];
        }

        fclose($file);
//        print_r($data);exit;
        try{
            $n = 0;
            $logs = [];
            foreach($data as $da){
                if($n > 10){
                    break;
                }
                $admin = null;
                $manager = $this->addBManager($da);
                if($da['is_admin']==1){
                    $role = [
                        'id'=>4,
                        'name'=>'县级权限'
                    ];
                    $admin = $this->addAdmin($manager, $role);
                }
                $logs[] = [
                    'phone'=>$da['phone'],
                    'manager_id'=>$manager->ID,
                    'admin_id'=>$admin ? $admin->ID : 0,
                ];
                $n++;
            }

            print_r($logs);
        }catch (\Exception $e){
            print_r([
                $e->getFile(),
                $e->getLine(),
                $e->getMessage(),
                $e->getTrace()
            ]);
        }
    }

    public function reserPwd(){

        $manages = UserManagers::all();
        foreach($manages as $manage){
            $stat = \think\helper\Str::random(5);
            $password = create_pwd(substr($manage->MOBILE,-6), $stat);

            $manage->PWSD = $password;
            $manage->SALT = $stat;

            $manage->save();
        }


    }

    public function addBManager($data){

        $manager = UserManagers::where('MOBILE', $data['phone'])->find();
//        if(!$manager){
//            $manager = new UserManagers();
//        }

        $ids = explode(',', $data['dw_ids']);
        $dmmId = end($ids);


        $row = [
            'STATUS'=>1,
            'MOBILE'=>$data['phone'],
            'NAME'=>$data['name'],
            'GENDER'=>$data['gender'],
            'JOB'=>$data['job_title'],
            'PROVINCE_ID'=>430000,
            'CITY_ID'=>431200,
            'COUNTY_ID'=>substr($data['area'], 0, 6),
        ];


        //431200000000
        $address = '';
        $lever = 0;
        $area = Subareas::where('CODE12', $data['area'])->find();
        if($area->PID == 431200000000){
            $row['COUNTY_ID_12'] = $area->CODE12;
            $row['STREET_ID'] = 0;
            $row['COMMUNITY_ID'] = 0;
            $address = $area->NAME;
        }else{
            $parea = Subareas::where('CODE12', $area->PID)->find();
            if($parea->PID == 431200000000){
                $row['COUNTY_ID_12'] = $parea->CODE12;
                $row['STREET_ID'] = $area->CODE12;
                $row['COMMUNITY_ID'] = 0;
                $address = $parea->NAME.' '.$area->NAME;
            }else{
                $pparea = Subareas::where('CODE12', $parea->PID)->find();
                if($pparea->PID == 431200000000){
                    $row['COUNTY_ID_12'] = $pparea->CODE12;
                    $row['STREET_ID'] = $parea->CODE12;
                    $row['COMMUNITY_ID'] = $area->CODE12;
                    $address = $pparea->NAME.' '.$parea->NAME.' '.$area->NAME;
                }
            }
        }
//        $manager->COUNTY_ID_12 = '';
//        $manager->STREET_ID = 1;
//        $manager->COMMUNITY_ID = 1;

        $row['ADDRESS'] = $address;


        $dw = NbAuthDept::where('ID', $dmmId)->find();
        $row['DMM_ID'] = $dmmId;
        $row['DMMC_IDS'] = $data['dw_ids'];
        $row['UNIT_NAME'] = $dw->DEPTNAME;
        $row['UNIT_ADDRESS'] = $dw->DEPTDESC;


        if($manager && !$manager->APPLY_TIME){
            $row['APPLY_TIME'] = Carbon::now()->toDateTimeString();
            $row['CHECK_OK_TIME'] = Carbon::now()->addSeconds(2)->toDateTimeString();
        }


        if($manager && !$manager->MARK){
            $row['MARK'] = $data['mark'];
        }

        if($manager && !$manager->ADD_TIME){
            $row['ADD_TIME'] = Carbon::now()->toDateTimeString();
        }


        if($manager && !$manager->PWSD){
            $stat = \think\helper\Str::random(5);
            $password = create_pwd(substr($data['phone'],-6), $stat);
            $row['PWSD'] = $password;
            $row['SALT'] = $stat;
        }

//        print_r([$row,$manager->UCODE]);exit;


        if(!$manager){
            $row['UCODE'] = '';
            $row['MARK'] = '';
        }

        if($manager){
            $manager->save($row);
        }else{
            $manager = (new UserManagers())->create($row);

            //echo 'UCODE='.$manager->UCODE;
//            print_r($manager);exit;
        }


        if(!$manager->UCODE){
            $ucode = $manager->createNewUCode($dmmId);
            $manager->UCODE = $ucode;
            $manager->save();
        }

        $this->setManagerPower($manager);
        $this->setHelperPower($manager);

        return $manager;
    }

    public function importManagerToAdmin(){
        UserManagers::where('ISDEL', 0)->select()->map(function($manager){
            $role = [];
            if($manager->COMMUNITY_ID > 0){
                $role = [
                    'id'=>6,
                    'name'=>'村级权限'
                ];
            }elseif($manager->STREET_ID > 0){
                $role = [
                    'id'=>5,
                    'name'=>'乡级权限'
                ];
            }elseif($manager->COUNTY_ID_12 > 0){
                $role = [
                    'id'=>4,
                    'name'=>'县级权限'
                ];
            }else{
                $role = [
                    'id'=>7,
                    'name'=>'临时录入人员'
                ];
            }

            $this->addAdmin($manager, $role, true);
        });
    }


    public function addAdmin(UserManagers $manager, $role, $existReturn = false){
        $admin = Admins::where('MOBILE', $manager->MOBILE)->find();
        if($existReturn && $admin){
            return $admin;
        }
        $row = [
            'LOG'=>$manager->MOBILE,
            'MOBILE'=>$manager->MOBILE,
            'ROLE_ID'=>$role['id'],
            'ROLE'=>$role['name'],
            'NAME'=>$manager->NAME,
            'GENDER'=>$manager->GENDER == 2 ? 1 : 2,
            'DMMC_ID'=>$manager->DMM_ID,
            'DMMC_NAME'=>$manager->UNIT_NAME,
            'POST'=>$manager->JOB,
            'REMARK'=>$manager->MARK,
            'POWER_CITY_ID'=>431200,
            'POWER_COUNTY_ID'=>$manager->COUNTY_ID,
            'POWER_COUNTY_ID_12'=>$manager->COUNTY_ID_12,
            'POWER_STREET_ID'=>$manager->STREET_ID,
            'POWER_COMMUNITY_ID'=>$manager->COMMUNITY_ID,
        ];

        $powerIds = [];
        if($manager->COUNTY_ID_12 > 0){
            $powerIds[] = $manager->COUNTY_ID_12;
        }

        if($manager->STREET_ID > 0){
            $powerIds[] = $manager->STREET_ID;
        }

        if($manager->COMMUNITY_ID > 0){
            $powerIds[] = $manager->COMMUNITY_ID;
        }

        $row['POWER_IDS'] = implode(',', $powerIds);
        $row['DMMCIDS'] = $manager->DMMC_IDS;

        $stat = \think\helper\Str::random(5);
//        $password = create_pwd(substr($data['phone'],-6), $stat);
        $row['PWD'] = create_pwd(substr($manager->MOBILE,-6), $stat);
        $row['STAT'] = $stat;


        if($admin){
            $admin->save($row);
        }else{
            $admin = (new Admins())->create($row);
        }
        return $admin;
    }

    public function setManagerPower(UserManagers $manager){

        UserManagerPower::where('UMID', $manager->ID)->delete();
        $power = new UserManagerPower();

        $power->UMID = $manager->ID;
        $power->PROVINCE_ID = $manager->PROVINCE_ID;
        $power->CITY_ID = $manager->CITY_ID;
        if($manager->COMMUNITY_ID > 0){
            $power->COUNTY_ID = $manager->COUNTY_ID_12;
            $power->STREET_ID = $manager->STREET_ID;
            $power->LEVEL = 4;
            $power->AREA_IDS = $manager->COMMUNITY_ID;
        }else if($manager->STREET_ID > 0){
            $power->COUNTY_ID = $manager->COUNTY_ID_12;
            $power->LEVEL = 3;
            $power->AREA_IDS = $manager->STREET_ID;
        }elseif($manager->COUNTY_ID_12 > 0){
            $power->LEVEL = 2;
            $power->AREA_IDS = $manager->COUNTY_ID_12;
        }

        $power->save();

    }

    public function setHelperPower(UserManagers $manager){
        HelperAreas::where('UMID', $manager->ID)->delete();
        $power = new HelperAreas();

        $power->UMID = $manager->ID;
        $power->PROVINCE_ID = $manager->PROVINCE_ID;
        $power->CITY_ID = $manager->CITY_ID;

        if($manager->COMMUNITY_ID > 0){
            $power->COUNTY_ID = $manager->COUNTY_ID_12;
            $power->STREET_ID = $manager->STREET_ID;
            $power->LEVEL = 4;
            $power->AREA_IDS = $manager->COMMUNITY_ID;
        }else if($manager->STREET_ID > 0){
            $power->COUNTY_ID = $manager->COUNTY_ID_12;
            $power->LEVEL = 3;
            $power->AREA_IDS = $manager->STREET_ID;
        }elseif($manager->COUNTY_ID_12 > 0){
            $power->LEVEL = 2;
            $power->AREA_IDS = $manager->COUNTY_ID_12;
        }

        $power->save();
    }

    public function addDanWei(){
        $maindata = [
            'dept_code'=>'02431271000000',
            'area_code'=>'431271000000',
            'pid'=>10074,
            'name'=>'洪江区禁毒委员会',
            'desc'=>'湖南省怀化市洪江区禁毒委员会',
        ];
        $subdata = [
                    [
                        'dept_code'=>'02431271000001',
                        'area_code'=>'431271000000',
                        'pid'=>0,
                        'name'=>'洪江区禁毒委员会办公室',
                        'desc'=>'湖南省怀化市洪江区禁毒委员会办公室',
                    ],
                    [
                        'dept_code'=>'02431271001000',
                        'area_code'=>'431271001000',
                        'pid'=>0,
                        'name'=>'河滨路街道禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江区河滨路街道禁毒工作领导小组',
                    ],
                    [
                        'dept_code'=>'02431271002000',
                        'area_code'=>'431271002000',
                        'pid'=>0,
                        'name'=>'沅江路街道禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江区沅江路街道禁毒工作领导小组',
                    ],
                    [
                        'dept_code'=>'02431271003000',
                        'area_code'=>'431271003000',
                        'pid'=>0,
                        'name'=>'新街街道禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江区新街街道禁毒工作领导小组',
                    ],
                    [
                        'dept_code'=>'02431271004000',
                        'area_code'=>'431271004000',
                        'pid'=>0,
                        'name'=>'高坡街街道禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江市高坡街街道禁毒工作领导小组',
                    ],
                    [
                        'dept_code'=>'02431271218000',
                        'area_code'=>'431271218000',
                        'pid'=>0,
                        'name'=>'横岩乡禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江区横岩乡禁毒工作领导小组',
                    ],
                    [
                        'dept_code'=>'02431271220000',
                        'area_code'=>'431271220000',
                        'pid'=>0,
                        'name'=>'桂花园乡禁毒工作领导小组',
                        'desc'=>'湖南省怀化市洪江区桂花园乡禁毒工作领导小组',
                    ],
                ];


        $count = NbAuthDept::where('FLAG', 0)->count();

        $main = NbAuthDept::where('DEPTCODE', $maindata['dept_code'])->find();
        if(!$main){
            $main = (new NbAuthDept())->create([
                'PARENTDEPTID'=>$maindata['pid'],
                'DEPTCODE'=>$maindata['dept_code'],
                'DEPTNAME'=>$maindata['name'],
                'DEPTDESC'=>$maindata['desc'],
                'FLAG'=> 0,
                'CREATOR'=>0,
                'SORTORDER'=>$count + 1,
                'SERVICEPARENTDEPTID'=>0,
                'AREACODE'=>$maindata['area_code'],
                'DEPTGROUPID'=>10030,
                'AUTHDEPTID'=>0
            ]);
        }

        $main_id = $main->ID;

        $insert = [];
        foreach($subdata as $k=>$da){
            $insert[] = [
                'PARENTDEPTID'=>$main_id,
                'DEPTCODE'=>$da['dept_code'],
                'DEPTNAME'=>$da['name'],
                'DEPTDESC'=>$da['desc'],
                'FLAG'=> 0,
                'CREATOR'=>0,
                'SORTORDER'=>$count + $k + 2,
                'SERVICEPARENTDEPTID'=>0,
                'AREACODE'=>$da['area_code'],
                'DEPTGROUPID'=>10030,
                'AUTHDEPTID'=>0
            ];
        }
        $m = new \app\common\model\NbAuthDept();
        $m->insertAll($insert);
    }

    public function fixXdcz(){
        header("content:application/json;chartset=uft-8");
        set_time_limit(600);
        ini_set('memory_limit', '2G');

        $page = request()->get('page', 1);
        $size = 500;
        $offset = ($page - 1) * $size;

        $data = TXdcz::field('C_RYBH,C_CZQK')
            ->where('C_CZQK', '>', 0)
            ->limit($offset, $size)
            ->select();

        if(!$data){
            return json_encode([
                'count'=>0
            ]);
        }

        foreach($data as $da){
            $user = UserUsers::where('UUCODE', $da->C_RYBH)->find();
            if(!$user){
                continue;
            }

            $user->UTYPE_ID = $da->C_CZQK;
            $user->save();
        }

        return json_encode([
            'count'=>count($data)
        ]);
    }

    public function fixCommunity(UserUsers $user, $community_id){
        if(!$community_id || $community_id==0){
            return false;
        }
            if(strpos($community_id, '243') !== false){
                $community_id = substr($community_id, 1);
            }

            $community = Subareas::where('CODE12', $community_id)->find();
            if($community){
                $user->STREET_ID = $community->PID;
                $user->COMMUNITY_ID = $community->CODE12;
                $user->save();
                return true;
            }

        Mylog::write($community_id, 'no_found_community_ids', false);
            return false;
    }

    public function importToUser(){
        set_time_limit(600);
        ini_set('memory_limit', '2G');
        $page = request()->get('page', 1);
        $size = 500;
        $offset = ($page - 1) * $size;
//        print_r([$offset, $page]);exit;
        $users = \app\common\model\UserUsersTmp::field('UUCODE,ID_NUMBER,COMMUNITY_ID')
            ->order('ADD_TIME','asc')
            ->limit($offset, $size)
            ->select();
//        $users = \app\common\model\UserUsersTmp::limit(0, 2)->select();

//        $xdry = new BXdry();

        $options = [
            'nations'=>create_kv(BaseNationType::all(),'ID', 'NAME'),
            'nationality'=>create_kv(BaseNationalityType::all(),'ID', 'NAME'),
            'edus'=>create_kv(BaseCultureType::all(),'ID', 'NAME'),
            'jobs'=>create_kv(BaseWorkStatus::all(),'ID', 'NAME'),
            'marry'=>create_kv(BaseMarryType::all(),'ID', 'NAME'),
        ];


        foreach($users as $user){
            $uuser = UserUsers::where('ID_NUMBER', $user->ID_NUMBER)->find();
            if($uuser && $uuser->COMMUNITY_ID==0 && $user->COMMUNITY_ID>0){
                $this->fixCommunity($uuser, $user->COMMUNITY_ID);
                continue;
            }
//            if(UserUsers::where('UUCODE', $user->UUCODE)->count()){
//                continue;
//            }
            try{
                $xdry = BXdry::where('C_18SFZHM', $user->ID_NUMBER)->find();
                if(!$xdry){
                    throw new Exception('BXdry exist');
                }
                $xdry->importToUserTable($options,$user->COMMUNITY_ID);
            }catch (\Exception $e){

                Mylog::write([
                    'table'=>'t_xdry_huaihua',
                    'uucode'=>$user->UUCODE,
                    'error'=>$e->getMessage()
                ], 'import_2_user_catch');

                continue;
            }

            usleep(2);
        }
    }

    public function addBaseDatas(){
//        $file = fopen("./doc/card.csv","r");
//        $file = fopen("./doc/edu.csv","r");
//        $file = fopen("./doc/marry.csv","r");
//        $file = fopen("./doc/nation.csv","r");
//        $file = fopen("./doc/nationality.csv","r");
//        $file = fopen("./doc/sex.csv","r");
//        $file = fopen("./doc/work.csv","r");
        $file = fopen("./doc/workinfo.csv","r");

        $data = [];
        while(! feof($file)) {
            $line = fgetcsv($file);
            $id = trim($line[0]);
            if(!$id || $id=='ID'){
                continue;
            }else{
                $data[] = [
                    'ID'=>$id,
                    'NAME'=>trim($line[1])
                ];
            }

        }
        fclose($file);

//        $m = new \app\common\model\BaseCertificateType();
//        $m = new \app\common\model\BaseCultureType();
//        $m = new \app\common\model\BaseMarryType();
//        $m = new \app\common\model\BaseNationType();
//        $m = new \app\common\model\BaseNationalityType();
//        $m = new \app\common\model\BaseSexType();
//        $m = new \app\common\model\BaseWorkStatus();
        $m = new \app\common\model\BaseWorkinfoType();

        $m->insertAll($data);
        print_r($data);
    }

    public function addUpareas(){

        $file = fopen("./doc/upareas.csv","r");
//        $file = fopen("./doc/test.csv","r");

        $data = [];
        while(! feof($file)) {
            $line = fgetcsv($file);
            $id = trim($line[0]);
            if(!$id || $id=='upareaid'){
                continue;
            }else{
                $last4 = substr($id, -4);
                $last2 = substr($id, -2);

                $name = str_replace([trim($line[10]), trim($line[12])], '', trim($line[3]));
                $pid = 0;
                //省
                if($last4 == '0000'){
                    $pid = 0;
                }else{
                    //$pid = substr($id, 0, 2).'0000';
                    if($last2 == '00'){
                        $pid = substr($id, 0, 2).'0000';
                    }else{
                        $pid = substr($id, 0, 4).'00';
                    }
                }


                $data[] = [
                    'UPAREAID'=>$id,
                    'UPTYPE'=>trim($line[1]),
                    'RELATEID'=>trim($line[2]),
                    'UPAREANAME'=>trim($line[3]),
                    'UPCORPNAME'=>trim($line[4]),
                    'FLAG'=>trim($line[5]),
                    'TASKFLAG'=>trim($line[6]),
                    'LISTORDER'=>trim($line[7]),
                    'REMARKSTR'=>trim($line[8]),
                    'PROVINCEID'=>trim($line[9]),
                    'PROVINCENAME'=>trim($line[10]),
                    'CITYID'=>trim($line[11]),
                    'CITYNAME'=>trim($line[12]),
                    'TELCODE'=>trim($line[13]),
                    'NAME'=>$name,
                    'PID'=>$pid
                ];
            }

        }
        fclose($file);

        $m = new \app\common\model\Upareatable();
        $m->insertAll($data);
        print_r($data);
    }

    public function addDepts(){
        $file = fopen("./doc/depts.csv","r");

        $data = [];
        while(! feof($file)) {
            $line = fgetcsv($file);
            $id = trim($line[0]);
            if(!$id || $id=='id'){
                continue;
            }else{
                $data[] = [
                    'ID'=>$id,
                    'PARENTDEPTID'=>trim($line[1]),
                    'DEPTCODE'=>trim($line[2]),
                    'DEPTNAME'=>trim($line[3]),
                    'DEPTDESC'=>trim($line[4]),
                    'FLAG'=>trim($line[5]),
                    'CREATOR'=>trim($line[6]),
                    'SORTORDER'=>trim($line[7]),
                    'SERVICEPARENTDEPTID'=>trim($line[8]),
                    'AREACODE'=>trim($line[9]),
                    'DEPTGROUPID'=>trim($line[10]),
                    'AUTHDEPTID'=>trim($line[11])
                ];
            }

        }
        fclose($file);

        $m = new \app\common\model\NbAuthDept();

        $m->insertAll($data);
        print_r($data);
    }

    public function addSubareas(){
        $file = fopen("./doc/hhareas.csv","r");
//        $file = fopen("./doc/test.csv","r");
        $proviceid = 430000;
        $cityid = 431200;
        $data = [];
        while(! feof($file)) {
            $line = fgetcsv($file);
            $id = trim($line[0]);
            if(!$id || $id=='id'){
                continue;
            }else{
                $code6 = '';
                $code12 = trim($line[0]);
                $code6 = substr($code12, 0, 6);

                $pid = 0;
                $iscity = false;
                if(substr($code12, -8) == '00000000'){
                    $pid = 0;
                    $iscity = true;
                }elseif(substr($code12, -6) == '000000'){
                    $pid = substr($code12, 0, 4).'00000000';
                }elseif(substr($code12, -3) == '000'){
                    $pid = substr($code12, 0, 6).'000000';
                }else{
                    $pid = substr($code12, 0, 9).'000';
                }

                //dump($line);
                $data[] = [
                    'CODE12'=>$code12,
                    'COUNTRY_CODE'=>trim($line[1]),
                    'CITY_COUNTRY_CODE'=>trim($line[2]),
                    'NAME'=>trim($line[3]),
                    'PROVICEID'=>$proviceid,
                    'CITYID'=> $cityid,
                    'COUNTYID'=>$iscity ? 0 : $code6,
                    'PID'=> $pid,

                ];


            }

        }
        fclose($file);

        $m = new \app\common\model\Subareas();
        $m->insertAll($data);
        print_r($data);
    }

    public function deptTrees(){
        $all = \app\common\model\NbAuthDept::field('ID, PARENTDEPTID as PID, DEPTCODE, DEPTNAME as NAME')
            ->where('FLAG', 0)
            ->all();
        $trees = create_level_tree($all,10040);

        return json_encode($trees);
    }

    /**
     * 修复县市区下属单位（禁毒办）信息，保证县市区下各乡镇街道都有一个对应的”禁毒办”，其余非县市区下属乡镇街道都将其隐藏
     * @param $countyName 县市区名称（如：鹤城区、芷江侗族自治县等）
     */
    public function fixNbAuthDept($countyName) {
        $parentDept = NbAuthDept::where('PARENTDEPTID', 10074)->whereLike('DEPTNAME', "$countyName%")->find();
        if (empty($parentDept)) {
            echo "Could not find a department specified by name \"$countyName\"";
            exit;
        }
        $parentDeptId = $parentDept->ID;
        $parentAreaCode = $parentDept->AREACODE;
        $subQuery = NbAuthDept::where('PARENTDEPTID', $parentDeptId)->buildSql();
        $unhandledDepts = db()->table('subareas')->alias('A')
            ->leftJoin("$subQuery B", 'A.CODE12 = B.AREACODE')
            ->where('A.PID|A.CODE12', $parentAreaCode)
            ->whereNull('B.ID')
            ->field('A.NAME,A.CODE12')
            ->select();
        print_r($unhandledDepts);die;
        if (!empty($unhandledDepts)) {
            $newSort = NbAuthDept::where('PARENTDEPTID', $parentDeptId)->max('SORTORDER');
            foreach ($unhandledDepts as $dept) {
                $newDept = new NbAuthDept();
                $newDept->PARENTDEPTID = $parentDeptId;
                $newDept->DEPTCODE = '02' . $dept['CODE12'];
                $newDept->DEPTNAME = $dept['NAME'] . '禁毒工作领导小组';
                $newDept->DEPTDESC = '湖南省怀化市' . $dept['NAME'] . '禁毒工作领导小组';
                $newDept->FLAG = 0;
                $newDept->CREATOR = 0;
                $newDept->SERVICEPARENTDEPTID = 0;
                $newDept->AREACODE = $dept['CODE12'];
                $newDept->DEPTGROUPID = 10030;
                $newDept->AUTHDEPTID = 0;
                $newDept->SORTORDER = ++$newSort;
                $newDept->save();

                $id = $newDept->ID;
                $newDept = new NbAuthDept();
                $newDept->PARENTDEPTID = $id;
                $newDept->DEPTCODE = '02' . ($dept['CODE12'] + 1);
                $newDept->DEPTNAME = $dept['NAME'] . '禁毒工作领导小组办公室';
                $newDept->DEPTDESC = '湖南省怀化市' . $dept['NAME'] . '禁毒工作领导小组办公室';
                $newDept->FLAG = 0;
                $newDept->CREATOR = 0;
                $newDept->SERVICEPARENTDEPTID = 0;
                $newDept->AREACODE = $dept['CODE12'];
                $newDept->DEPTGROUPID = 10030;
                $newDept->AUTHDEPTID = 0;
                $newDept->SORTORDER = 1;
                $newDept->save();
            }
        }
        $abandonedDepts = NbAuthDept::where('PARENTDEPTID', $parentDeptId)
            ->where('AREACODE', 'NOT IN', function ($query) use ($parentAreaCode) {
                $query->table('subareas')->where('PID|CODE12', $parentAreaCode)->field('CODE12');
            })->select();
        if (!empty($abandonedDepts)) {
            foreach ($abandonedDepts as &$dept) {
                $dept->FLAG = 1;
                $dept->save();
                $children = NbAuthDept::where('PARENTDEPTID', $dept->ID)->select();
                if (!empty($children)) {
                    foreach ($children as $child) {
                        $child->FLAG = 1;
                        $child->save();
                    }
                }
            }
        }
        echo 'Done.';
    }

    /**
     * 将“非法”区域所属人员分派到“市辖区”
     */
    public function assignUsersToDefaultArea() {
        $defaultArea = Subareas::where('NAME', '市辖区')->find();
        $users = UserUsers::where('ISDEL', 0)->where(function($query) {
            $query->where('COUNTY_ID_12', 'NOT IN', function ($query) {
                $query->table('subareas')->field('CODE12');
            });
            $query->whereOr('COUNTY_ID_12', '431200000000');
        })->select();
        if (empty($users)) {
            echo 'No users need to be fixed.';
            exit;
        }
        foreach ($users as &$user) {
            $user->PROVINCE_ID = $defaultArea->PROVICEID;
            $user->CITY_ID = $defaultArea->CITYID;
            $user->COUNTY_ID = $defaultArea->COUNTYID;
            $user->COUNTY_ID_12 = $defaultArea->CODE12;
            $user->STREET_ID = 0;
            $user->COMMUNITY_ID = 0;
            $user->save();
        }
        echo 'Done.';
    }

    /**
     * 修复康复人员变化日志中“指派”退回到“市级”的数据
     */
    public function fixUserChangeLog() {
        $logs = UserChangeLog::whereLike('CONTENT', '%"new":{"countyId":""%')->select();
        if (empty($logs->toArray())) {
            echo 'No logs need to be fixed.';
            exit;
        }
        $defaultArea = Subareas::where('NAME', '市辖区')->find();
        foreach ($logs as &$log) {
            $content = json_decode($log->CONTENT);
            $content->new->countyId = $defaultArea->CODE12;
            $content->new->countyName = $defaultArea->NAME;
            $log->CONTENT = json_encode($content);
            $log->save();
        }
        echo 'Done.';
    }

    public function fixAgreementImages() {
        $agreements = Agreement::all();
        foreach ($agreements as $agreement) {
            $content = $agreement->CONTENT;
            $images = [];
            $this->getImageUrl($content, $images);
            if (!empty($images)) {
                $filteredImages = [];
                foreach ($images as $image) {
                    $count = AgreementImgs::where(['AGREEMENT_ID' => $agreement->ID, 'SRC_PATH' => $image])->count();
                    if ($count > 0) {
                        continue;
                    }
                    array_push($filteredImages, $image);
                }
                (new AgreementImgs())->saveData($agreement->ID, $filteredImages);
            }
        }
        echo 'Done.';
    }

    private function getImageUrl($content, &$images, $startNeedle = 'src="', $endNeedle = '.jpg') {
        $start = strpos($content, $startNeedle, 0);
        if (!$start) {
            return $images;
        }
        $getStart = $start + strlen($startNeedle);
        $end = strpos($content, $endNeedle, $getStart);
        if (!$end) {
            $end = strpos($content, '.png', $getStart);
        }
        $getEnd = $end + strlen($endNeedle);
        $len = $getEnd - $getStart;
        array_push($images, substr($content, $getStart, $len));
        $content = substr($content, $getEnd, strlen($content) - $getEnd);
        $this->getImageUrl($content, $images);
    }

}