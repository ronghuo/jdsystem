<?php

namespace app\common\validate;

use think\Validate;

class MessageBoardAsksVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'ASKER_UID'=>'require',
        'ASKER_NAME'=>'require',
        'QUESTION'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'ASKER_UID.require'=>'缺少提问者信息',
        'ASKER_NAME.require'=>'缺少提问者名称',
        'QUESTION.require'=>'缺少问题内容',
    ];
}
