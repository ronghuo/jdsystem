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
	    'NAME'=>'require',
	    'GENDER'=>'require',
	    'ID_NUMBER'=>'require',
	    'MOBILE'=>'require',
	    'DOMICILE_PLACE'=>'require',
	    'DOMICILE_ADDRESS'=>'require',
	    'DOMICILE_POLICE_STATION'=>'require',
	    'LIVE_PLACE'=>'require',
	    'LIVE_ADDRESS'=>'require',
	    'LIVE_POLICE_STATION'=>'require',
	    'MANAGE_COMMUNITY'=>'require',
        'JD_ZHUANGAN'=>'require',
        'JD_ZHUANGAN_MOBILE'=>'require'
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
        'GENDER.require'=>'缺少性别',
        'ID_NUMBER.require'=>'缺少身份证',
        'MOBILE.require'=>'缺少手机号码',
        'DOMICILE_PLACE.require'=>'缺少户籍地',
        'DOMICILE_ADDRESS.require'=>'缺少户籍地详细地址',
        'DOMICILE_POLICE_STATION.require'=>'缺少户籍地派出所名称',
        'LIVE_PLACE.require'=>'缺少居住地',
        'LIVE_ADDRESS.require'=>'缺少居住地详细地址',
        'LIVE_POLICE_STATION.require'=>'缺少居住地派出所名称',
        'JD_ZHUANGAN.require'=>'缺少负责专干姓名',
        'JD_ZHUANGAN_MOBILE.require'=>'缺少负责专干联系电话'
    ];
}
