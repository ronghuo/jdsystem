<?php

namespace app\common\validate;

use think\Validate;

class UserManagersVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'UCODE'=>'require|unique:\\app\\common\\model\\UserManagers',
	    'MOBILE'=>'require|length:4,15|mobile',
	    'NAME'=>'require',
	    'GENDER'=>'require',
	    'ID_NUMBER'=>'require',//|idCard
	    'JOB'=>'require',
	    'PROVINCE_ID'=>'require',
	    'CITY_ID'=>'require',
	    'COUNTY_ID'=>'require',
	    'STREET_ID'=>'require',
	    'ADDRESS'=>'require',
	    'UNIT_NAME'=>'require',
	    'DMM_ID'=>'require',
	    'SPECIAL_ABILITY'=>'require',
	    'DOMICILE_PLACE'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'UCODE.require'=>'缺少人员编号',
        'UCODE.unique'=>'人员编号不能重复',
        'MOBILE.require'=>'缺少手机号码',
        'MOBILE.length'=>'手机号码不正确',
        'MOBILE.mobile'=>'手机号码不正确',
        //'MOBILE.unique'=>'手机号码已存在',
        'NAME.require'=>'缺少姓名',
        'GENDER.require'=>'缺少性别',
        'ID_NUMBER.require'=>'缺少身份证号',
//        'ID_NUMBER.idCard'=>'身份证号有误',
        'JOB.require'=>'缺少职业',
        'PROVINCE_ID.require'=>'缺少省',
        'CITY_ID.require'=>'缺少市',
        'COUNTY_ID.require'=>'缺少区/县',
        'STREET_ID.require'=>'缺少区/县',
        'ADDRESS.require'=>'缺少地址',
        'UNIT_NAME.require'=>'缺少单位名称',
        'DMM_ID.require'=>'缺少管辖警务',
        'SPECIAL_ABILITY.require'=>'缺少特殊能力',
        'DOMICILE_PLACE.require'=>'缺少籍贯',
    ];

    protected $scene = [
        'add'  =>  ['MOBILE','NAME','ID_NUMBER','JOB','UNIT_NAME','SPECIAL_ABILITY'],
        'htadd'  =>  ['MOBILE','NAME','ID_NUMBER','DMM_ID'],
        'edit'=>['NAME','GENDER','ID_NUMBER','JOB','UNIT_NAME']
    ];
}
