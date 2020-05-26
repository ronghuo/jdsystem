<?php

namespace app\common\validate\other;

use think\Validate;

class SmsInfoVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'smsAddress' => 'require',
        'smsbody' => 'require',
        'smstime' => 'require',
        'type' => 'require'

    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'smsAddress.require'=>'缺少发送方号码',
        'smsbody.require'=>'缺少短信内容',
        'smstime.require'=>'缺少发送时间',
        'userphone.require'=>'缺少接收方号码',
        'username.require'=>'缺少接收方姓名',
        'type.require'=>'缺少短信来源'
    ];
}
