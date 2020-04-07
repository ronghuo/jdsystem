<?php

namespace app\common\validate;

use think\Validate;

class HelperDiarysVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'UMID'=>'require',
	    'UUID'=>'require',
	    'ADD_YEAR'=>'require',
        'ADD_MONTH'=>'require',
        'ADD_DAY'=>'require',
        'TITLE'=>'require',
	    'CONTENT'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'UMID.require'=>'缺少填写人员信息',
        'UUID.require'=>'缺少帮扶人员信息',
        'ADD_YEAR.require'=>'缺少时间信息',
        'ADD_MONTH.require'=>'缺少时间信息',
        'ADD_DAY.require'=>'缺少时间信息',
        'TITLE.require'=>'缺少标题',
        'CONTENT.require'=>'缺少内容',
    ];
}
