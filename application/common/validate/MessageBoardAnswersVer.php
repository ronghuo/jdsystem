<?php

namespace app\common\validate;

use think\Validate;

class MessageBoardAnswersVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'ASKID'=>'require',
	    'ANSWERER_UID'=>'require',
	    'ANSWERER_NAME'=>'require',
	    'CONTENT'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'ASKID.require'=>'缺少问题信息',
        'ANSWERER_UID.require'=>'缺少回答者信息',
        'ANSWERER_NAME.require'=>'缺少回答者名称',
        'CONTENT.require'=>'缺少回答内容'
    ];
}
