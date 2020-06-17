<?php

namespace app\common\validate;

use think\Validate;

class UransVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'URAN_CODE'=>'require',
	    'UUID'=>'require',
	    'CHECK_TIME'=>'require',
        'PROVINCE_ID'=>'require',
        'CITY_ID'=>'require',
        'COUNTY_ID'=>'require',
	    'ADDRESS'=>'require',
	    'UMID'=>'require',
	    'DMM_ID'=>'require',
	    'RESULT'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'URAN_CODE.require'=>'缺少尿检编号',
        'UUID.require'=>'缺少受检人员信息',
        'CHECK_TIME.require'=>'缺少检查时间',
        'PROVINCE_ID.require'=>'缺少省id',
        'CITY_ID.require'=>'缺少市id',
        'COUNTY_ID.require'=>'缺少区/县id',
        'ADDRESS.require'=>'缺少地区',
        'UMID.require'=>'缺少登记的管理员',
        'DMM_ID.require'=>'缺少登记单位信息',
        'RESULT.require'=>'缺少检查结果',
    ];

    protected $scene = [
        'add'  => ['URAN_CODE','UUID','CHECK_TIME','PROVINCE_ID','CITY_ID','COUNTY_ID','ADDRESS','UMID','DMM_ID','RESULT'],
        'edit' => ['CHECK_TIME','PROVINCE_ID','CITY_ID','COUNTY_ID','ADDRESS','UMID','DMM_ID','RESULT']
    ];
}
