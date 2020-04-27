<?php

namespace app\common\validate;

use think\Validate;

class UserDecisionsVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'UUID' => 'require',
	    'BEGIN_TIME' => 'require',
        'END_TIME' => 'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'UUID.require' => '缺少康复人员ID',
        'BEGIN_TIME.require' => '缺少决定书有效期起始时间',
        'END_TIME.require' => '缺少决定书有效期截止时间'
    ];
}
