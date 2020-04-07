<?php

namespace app\common\validate;

use think\Validate;

class UserUsersVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    //'UUCODE'=>'require',//|unique:\\app\\common\\model\\UserUsers
	    'NAME'=>'require',
//	    'ALIAS_NAME'=>'require',
	    'GENDER'=>'require',
//	    'NATIONALITY'=>'require',
//	    'NATION_ID'=>'require',
	    'ID_NUMBER'=>'require',//|idCard
//	    'HEIGHT'=>'require',
//	    'EDUCATION_ID'=>'require',
//	    'JOB_STATUS_ID'=>'require',
//	    'MARITAL_STATUS_ID'=>'require',
	    //'JOB_UNIT'=>'require',
	    'MOBILE'=>'require',//|length:4,15|mobile
	    'DOMICILE_PLACE'=>'require',
	    'DOMICILE_ADDRESS'=>'require',
	    'DOMICILE_POLICE_STATION'=>'require',
//	    'DOMICILE_POLICE_STATION_CODE'=>'require',
	    'LIVE_PLACE'=>'require',
	    'LIVE_ADDRESS'=>'require',
	    'LIVE_POLICE_STATION'=>'require',
//	    'LIVE_POLICE_STATION_CODE'=>'require',
//	    'DRUG_TYPE_ID'=>'require',
//	    'NARCOTICS_TYPE_ID'=>'require',
//	    'MANAGE_POLICE_AREA_CODE'=>'require',
//	    'MANAGE_POLICE_AREA_NAME'=>'require',
	    'MANAGE_COMMUNITY'=>'require',
//	    'POLICE_LIABLE_CODE'=>'require',
//	    'POLICE_LIABLE_NAME'=>'require',
//	    'POLICE_LIABLE_MOBILE'=>'require'//|mobile
        'JD_ZHUANGAN'=>'require',
        'JD_ZHUANGAN_MOBILE'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'UUCODE.require'=>'缺少人员编号',
        'UUCODE.unique'=>'人员编号不能重复',
        'NAME.require'=>'缺少姓名',
//        'ALIAS_NAME.require'=>'缺少绰号',
        'GENDER.require'=>'缺少性别',
//        'NATIONALITY.require'=>'缺少国藉',
//        'NATION_ID.require'=>'缺少民族',
        'ID_NUMBER.require'=>'缺少身份证',
//        'ID_NUMBER.idCard'=>'身份证号有误',
//        'HEIGHT.require'=>'缺少身高',
//        'EDUCATION_ID.require'=>'缺少文化程度',
//        'JOB_STATUS_ID.require'=>'缺少就业信息',
//        'MARITAL_STATUS_ID.require'=>'缺少婚姻状况',
        //'JOB_UNIT.require'=>'缺少工作单位',
        'MOBILE.require'=>'缺少手机号码',
//        'MOBILE.length'=>'手机号码有误',
//        'MOBILE.mobile'=>'手机号码有误',
        'DOMICILE_PLACE.require'=>'缺少户籍地',
        'DOMICILE_ADDRESS.require'=>'缺少户籍地详细地址',
        'DOMICILE_POLICE_STATION.require'=>'缺少户籍地派出所名称',
//        'DOMICILE_POLICE_STATION_CODE.require'=>'缺少户籍地派出所代码',
        'LIVE_PLACE.require'=>'缺少居住地',
        'LIVE_ADDRESS.require'=>'缺少居住地详细地址',
        'LIVE_POLICE_STATION.require'=>'缺少居住地派出所名称',
//        'LIVE_POLICE_STATION_CODE.require'=>'缺少居住地派出所代码',
//        'DRUG_TYPE_ID.require'=>'缺少吸毒方式',
//        'NARCOTICS_TYPE_ID.require'=>'缺少毒品种类',
//        'MANAGE_POLICE_AREA_CODE.require'=>'缺少管辖警务区代码',
//        'MANAGE_POLICE_AREA_NAME.require'=>'缺少管辖警务区名称',
//        'MANAGE_COMMUNITY.require'=>'缺少管辖社区',
//        'POLICE_LIABLE_CODE.require'=>'缺少责任民警警号',
//        'POLICE_LIABLE_NAME.require'=>'缺少责任民警姓名',
//        'POLICE_LIABLE_MOBILE.require'=>'缺少责任民警联系电话',
//        'POLICE_LIABLE_MOBILE.mobile'=>'责任民警联系电话有误',
    'JD_ZHUANGAN.require'=>'缺少负责专干姓名',
    'JD_ZHUANGAN_MOBILE.require'=>'缺少负责专干联系电话',
    ];
}
