<?php

namespace app\common\validate;

use think\Validate;

class TroubleshootPersonVer extends Validate
{
	protected $rule = [
	    'TEMPLATE_ID' => 'require',
	    'NAME' => 'require',
	    'ID_CODE' => 'require',
	    'DOMICILE_COUNTY_CODE' => 'require',
        'DOMICILE_STREET_CODE' => 'require',
        'DOMICILE_COMMUNITY_CODE' => 'require'
    ];
    
    protected $message = [
        'TEMPLATE_ID.require' => '缺少模板ID',
        'NAME.require' => '缺少被排除人员姓名',
        'ID_CODE.require' => '缺少被排除人员身份证号码',
        'DOMICILE_COUNTY_CODE.require' => '缺少户籍地县市区代码',
        'DOMICILE_STREET_CODE.require' => '缺少户籍地乡镇街道代码',
        'DOMICILE_COMMUNITY_CODE.require' => '缺少户籍地村级社区代码'
    ];

    protected $scene = [
        'create' => ['TEMPLATE_ID','NAME','ID_CODE','DOMICILE_COUNTY_CODE','DOMICILE_STREET_CODE','DOMICILE_COMMUNITY_CODE'],
        'modify' => ['TEMPLATE_ID','NAME','ID_CODE','DOMICILE_COUNTY_CODE','DOMICILE_STREET_CODE','DOMICILE_COMMUNITY_CODE']
    ];
}
