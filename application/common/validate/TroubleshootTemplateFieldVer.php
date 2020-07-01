<?php

namespace app\common\validate;

use think\Validate;

class TroubleshootTemplateFieldVer extends Validate
{
	protected $rule = [
	    'TEMPLATE_ID' => 'require',
	    'CODE' => 'require|alphaDash',
	    'NAME' => 'require',
	    'WIDGET' => 'require',
	    'SORT' => 'require|number|gt:0'
    ];
    
    protected $message = [
        'TEMPLATE_ID.require' => '没有指定模板',
        'CODE.require' => '缺少字段代码',
        'CODE.alphaDash' => '字段代码只能包含字母和数字下划线_及破折号-',
        'NAME.require' => '缺少字段名称',
        'WIDGET.require' => '缺少控件类型',
        'SORT.require' => '缺少字段排序号',
        'SORT.number' => '字段排序号必须是数字',
        'SORT.gt' => '字段排序号必须大于0'
    ];

    protected $scene = [
        'create' => ['TEMPLATE_ID','CODE','NAME','WIDGET','SORT'],
        'modify' => ['CODE','NAME','WIDGET','SORT']
    ];
}
