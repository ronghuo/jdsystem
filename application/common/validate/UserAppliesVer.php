<?php

namespace app\common\validate;

use think\Validate;

class UserAppliesVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'UUID'=>'require',
	    'TITLE'=>'require',
	    'CONTENT'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'UUID.require'=>'缺少申请者信息',
        'TITLE.require'=>'缺少标题',
        'CONTENT.require'=>'缺少申请内容',
    ];
}
