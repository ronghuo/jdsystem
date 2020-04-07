<?php

namespace app\common\validate;

use think\Validate;

class ArticleCateVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'CLIENT_TAG'=>'require',
	    'NAME'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'CLIENT_TAG.require'=>'缺少客户端',
        'NAME.require'=>'缺少分类名称'
    ];
}
