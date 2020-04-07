<?php

namespace app\common\validate;

use think\Validate;

class UserHolidayApplyListsVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'UHA_ID'=>'require',
	    'UUID'=>'require',
	    'MOBILE'=>'require',
	    'OUT_TIME'=>'require',
	    'BACK_TIME'=>'require',
	    'REASON'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
      'UHA_ID.require'  =>'缺少假期信息',
      'UUID.require'  =>'缺少申请人信息',
      'MOBILE.require'  =>'缺少联系号码',
      'OUT_TIME.require'  =>'缺少出所时间',
      'BACK_TIME.require'  =>'缺少回所时间',
      'REASON.require'  =>'缺少请假理由',
    ];
}
