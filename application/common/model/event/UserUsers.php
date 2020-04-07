<?php
namespace app\common\model\event;

use app\common\model\BXdry;
use app\common\model\UserUsers as UserModel;
use app\common\library\Mylog;
use Carbon\Carbon;

class UserUsers{


    public function afterInsert(UserModel $user){
        Mylog::write([
            'afterInsert',
            $user->ID,
            $user->NAME
        ], 'user_event');
        $this->updateToXDRYTable($user);
    }

    public function afterUpdate(UserModel $user){
        Mylog::write([
            'afterUpdate',
            $user->ID,
            $user->NAME
        ], 'user_event');
        $this->updateToXDRYTable($user);
    }

    public function afterDelete(UserModel $user)
    {
        return false;
        Mylog::write([
            'afterDelete',
            $user->ID,
            $user->NAME
        ], 'user_event');

        $sdry = BXdry::where('JD_USER_USER_ID', $user->ID)->find();

        if($sdry){
            $sdry->UPDATETIME = Carbon::now()->toDateTimeString();//更新时间
            $sdry->N_YXX = 0;//有效性
            $sdry->save();
        }
    }



    protected function updateToXDRYTable(UserModel $user){
        return false;
        $sdry = BXdry::where('JD_USER_USER_ID', $user->ID)->find();
        if(!$sdry){
            $sdry = new BXdry();
        }

        $sdry->JD_USER_USER_ID = $user->ID;//康复人员表中的id
//        $sdry->N_XH = $user->ID;//吸毒人员序号（主键）
//        $sdry->C_XDRYLX = $user->ID;//人员类型
//        $sdry->C_RYBH = $user->ID;//人员编号
//        $sdry->C_TNAME = $user->ID;//业务类型（1：社区戒毒  2：社区康复，3：强制隔离戒毒）

        $sdry->C_XM = $user->NAME;//姓名
        $sdry->C_CH = $user->ALIAS_NAME;//绰号/别名
        $sdry->C_SFZJZL = $user->ID_NUMBER_TYPE;//证件种类
        $sdry->C_18SFZHM = $user->ID_NUMBER;//18位身份证号
        $sdry->C_ZJLX = $user->ID_NUMBER_TYPE;//证件类型
        $sdry->C_SFZHM = $user->ID_NUMBER;//身份证号
        $sdry->C_SJHM = $user->ID_NUMBER;//身份证号
        //431202 19920909 1111
        $dobstr = substr($user->ID_NUMBER, 6, 8);
        if(date('Ymd', strtotime($dobstr)) == $dobstr){
            $sdry->D_CS = date('Y-m-d', strtotime($dobstr));//出生日期
        }

        $sdry->C_XB = $user->GENDER;//性别
        $sdry->C_MZ = $user->NATION_ID;//民族
        $sdry->N_SG = $user->HEIGHT;//身高
        $sdry->C_GJ = $user->NATIONALITY_ID;//国籍
        $sdry->C_WHCD = $user->EDUCATION_ID;//文化程度
        $sdry->C_JYQK = $user->JOB_STATUS_ID;//就业情况
//        $sdry->C_CYZK = $user->ID;//从业状况
        $sdry->C_HY = $user->MARITAL_STATUS_ID;//婚姻状况

        $sdry->C_FWCS = $user->JOB_UNIT;//工作单位


//        $sdry->C_XDHGST = $user->ID;//指纹编号
//        $sdry->C_DNA = $user->ID;//DNA编号
        $sdry->D_DJ = $user->ADD_TIME;//录入日期

        $sdry->C_HJSZD = $user->DOMICILE_PLACE;//户籍所在地
        $sdry->C_HJSZDXZ = $user->DOMICILE_ADDRESS;//户籍所在地行政区划
        $sdry->C_HJPCS = $user->DOMICILE_POLICE_STATION;//户籍地派出所
        $sdry->C_SJJZD = $user->LIVE_PLACE;//实际居住地
        $sdry->C_SJJZDXZ = $user->LIVE_ADDRESS;//实际居住地祥址
        $sdry->C_SJPCS = $user->LIVE_POLICE_STATION;//实际居住地派出所


//        $sdry->C_CZBS = $user->ID;//操作标识
//        $sdry->C_DJR = $user->ID;//录入人
//        $sdry->C_TBDW = $user->ID;//填表单位
        $sdry->C_TBR = $user->ADD_TIME;//填表人
        $sdry->C_GSDW = $user->MANAGE_POLICE_AREA_NAME;//归属单位

//        $sdry->SYS_DATAFROM = $user->ID;//数据来源（1,3-法制处置记录 5-外省处置记录  2-动态管控接管）
        $sdry->INPUTTIME = $user->ADD_TIME;//录入时间
        $sdry->UPDATETIME = Carbon::now()->toDateTimeString();//更新时间
        $sdry->N_YXX = $user->STATUS;//有效性

        $sdry->save();
    }
}