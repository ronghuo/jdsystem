<?php
namespace app\common\model;

use think\Model;
use app\common\library\Mylog;


class BXdry extends BaseModel
{
    //
    protected $pk = 'ID';
    public $table = 't_xdry_huaihua';
//    public $table = 'B_XDRY';


    public function importToUserTable($options, $community_id=0){
        $error = [];
        $exist = true;
        $user = UserUsers::where('ID_NUMBER', $this->C_18SFZHM)->find();
        if(!$user){
            $user = UserUsers::where('UUCODE', $this->C_RYBH)->find();

            if($user && $user->ID_NUMBER != $this->C_18SFZHM){
                $error[] = 'UUCODE冲突:'.$this->C_RYBH;
                Mylog::write([
                    'table'=>'t_xdry_huaihua',
                    'id'=>$this->ID,
                    'error'=>$error,
//                    'user'=>$user->toArray(),
//                    'info'=>$this->toArray()
                ], 'import_2_user_uucode');
                return false;
            }
        }
        if($user){
            $error[] = '用户已存在';
            Mylog::write([
                'table'=>'t_xdry_huaihua',
                'id'=>$this->ID,
                'error'=>$error,
//                'user'=>$user->toArray(),
//                'info'=>$this->toArray()
            ], 'import_2_user_exist');
            return false;
        }

        if(!$user){
            $exist = false;
            $user = new UserUsers();
        }

        if(!$exist){
            $user->UUCODE = $this->C_RYBH ? : '';
        }

        $user->STATUS = 1;
        $user->NAME = $this->C_XM ? : '';
        $user->ALIAS_NAME = $this->C_CH ? : '';
        $user->ID_NUMBER_TYPE = $this->C_SFZJZL ? : '';
        $user->ID_NUMBER = $this->C_18SFZHM ? : '';
        $user->MOBILE = $this->C_SJHM ? : '';

        $user->GENDER = $this->C_XB ? : '';
        $user->HEIGHT = $this->N_SG ? : '';

//        $nation = BaseNationType::where('ID', $this->C_MZ)->find();
        $user->NATION = $options['nations'][$this->C_MZ] ?? '';
        $user->NATION_ID = $this->C_MZ ? : 0;

//        $nationality = BaseNationalityType::where('ID', $this->C_GJ)->find();
        $user->NATIONALITY = $options['nationality'][$this->C_GJ] ?? '';
        $user->NATIONALITY_ID = $this->C_GJ ? : 0;

//        $edu = BaseCultureType::where('ID', $this->C_WHCD)->find();
        $user->EDUCATION = $options['edus'][$this->C_WHCD] ?? '';
        $user->EDUCATION_ID = $this->C_WHCD ? : 0;

//        $job = BaseWorkStatus::where('ID', $this->C_JYQK)->find();
        $user->JOB_STATUS = $options['jobs'][$this->C_JYQK] ?? '';
        $user->JOB_STATUS_ID = $this->C_JYQK ? : 0;

//        $marry = BaseMarryType::where('ID', $this->C_HY)->find();
        $user->MARITAL_STATUS = $options['marry'][$this->C_HY] ?? '';
        $user->MARITAL_STATUS_ID = $this->C_HY ? : 0;

        $user->JOB_UNIT = $this->C_FWCS ? : '';

        if($this->C_SJJZD){
            $user->PROVINCE_ID = substr($this->C_SJJZD, 0, 2).'0000';
            $user->CITY_ID = substr($this->C_SJJZD, 0, 4).'00';;
            $user->COUNTY_ID = $this->C_SJJZD;
            $user->COUNTY_ID_12 = $this->C_SJJZD.'000000';
//        $user->STREET_ID = '';
//        $user->COMMUNITY_ID = '';
        }
        if($community_id>0){
            if(strpos($community_id, '243') !== false){
                $community_id = substr($community_id, 1);
            }

            $community = Subareas::where('CODE12', $community_id)->find();
            if($community){
                $user->STREET_ID = $community->PID;
                $user->COMMUNITY_ID = $community->CODE12;
            }else{
                Mylog::write($community_id, 'no_found_community_ids', false);
            }
        }


        //$this->C_HJSZD
//        $user->DOMICILE_PLACE = '';
        $user->DOMICILE_ADDRESS = $this->C_HJSZDXZ ? : '';
        $user->DOMICILE_POLICE_STATION = $this->C_HJPCS ? : '';
//        $user->DOMICILE_POLICE_STATION_CODE = '';

        //$this->C_SJJZD
//        $user->LIVE_PLACE = '';
        $user->LIVE_ADDRESS = $this->C_SJJZDXZ ? : '';
        $user->LIVE_POLICE_STATION = $this->C_SJPCS ? : '';
//        $user->LIVE_POLICE_STATION_CODE = '';

//        $user->DRUG_TYPE_ID = '';
//        $user->DRUG_TYPE = '';
//        $user->NARCOTICS_TYPE_ID = '';
//        $user->NARCOTICS_TYPE = '';


        //查找所属单位信息
        $managePolice = null;
        $manageCode = '';
        if($this->C_GSDW && strpos($this->C_GSDW, '43') !== false){
            $manageCode = '02'.$this->C_GSDW;
            $managePolice = NbAuthDept::where('DEPTCODE', '02'.$this->C_GSDW)->find();
        }

        if(!$managePolice && $this->C_TBDW){
            $manageCode = $this->C_TBDW;
            $managePolice = NbAuthDept::where('DEPTCODE', $this->C_TBDW)->find();
        }
        //向上级查找
        if(!$managePolice && $manageCode){
            $managePolice = $managePolice = NbAuthDept::where('DEPTCODE', substr($manageCode, 0, 8).'000000')->find();
        }

        $user->MANAGE_POLICE_AREA_CODE = $manageCode ? : '';
        $user->MANAGE_POLICE_AREA_NAME = $managePolice ? $managePolice->DEPTNAME : '';
//        $user->MANAGE_COMMUNITY = '';

//        $user->POLICE_LIABLE_UID = '';
//        $user->POLICE_LIABLE_CODE = '';
//        $user->POLICE_LIABLE_NAME = '';
//        $user->POLICE_LIABLE_MOBILE = '';

//        $user->HEAD_IMG = '';
        if($this->C_HJSZD){
            $user->DOMICILE_IDS = substr($this->C_HJSZD, 0, 2).'0000,'.substr($this->C_HJSZD, 0, 4).'00,'.$this->C_HJSZD;
        }else{
            $error[] = 'C_HJSZD 为空';
        }
        if($this->C_SJJZD){
            $user->LIVE_IDS = substr($this->C_SJJZD, 0, 2).'0000,'.substr($this->C_SJJZD, 0, 4).'00,'.$this->C_SJJZD;
        }else{
            $error[] = 'C_SJJZD 为空';
        }



        if($managePolice){
            $dmmc_ids = [$managePolice->ID];

            $p_managePolice = NbAuthDept::where('ID', $managePolice->PARENTDEPTID)->find();

            if($p_managePolice && $p_managePolice->PARENTDEPTID){
                array_unshift($dmmc_ids, $p_managePolice->ID);
                $pp_managePolice = NbAuthDept::where('ID', $p_managePolice->PARENTDEPTID)->find();
                if($pp_managePolice && $p_managePolice->PARENTDEPTID){
                    array_unshift($dmmc_ids, $pp_managePolice->ID);
                    $ppp_managePolice = NbAuthDept::where('ID', $pp_managePolice->PARENTDEPTID)->find();
                    if($ppp_managePolice){
                        array_unshift($dmmc_ids, $ppp_managePolice->ID);
                    }
                }
            }
            $user->DMMC_IDS = implode(',', $dmmc_ids);
        }else{
            $error[] = '所属单位信息 为空';

        }


        //默认后6位
        if($exist && !$user->PWSD && $this->C_SJHM){
//            if($this->C_SJHM){
//                substr($this->C_SJHM, -6);
//            }
            $stat = \think\helper\Str::random(6);
            $pwsd = create_pwd(substr($this->C_SJHM, -6), $stat);

            $user->PWSD = $pwsd;
            $user->SALT = $stat;
        }


//        $user->CREATE_USER_ID = '';
//        $user->CREATE_USER_NAME = '';

        $user->ADD_TIME = $this->INPUTTIME ? : date('Y-m-d H:i:s');
        $user->UPDATE_TIME = $this->UPDATETIME ? : date('Y-m-d H:i:s');

        $user->save();

        if(!empty($error)){
            Mylog::write([
                'table'=>'t_xdry_huaihua',
                'id'=>$this->ID,
                'error'=>$error,
                'info'=>$this->toArray()
            ], 'import_2_user');
        }

    }

}