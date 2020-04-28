<?php

namespace app\common\validate;

use think\Validate;

class UserRecoveryPlanVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'UUID' => 'require',
        'NAME' => 'require',
        'GENDER' => 'require',
        'ID_NUMBER' => 'require',
        'MOBILE' => 'require',
        'DOMICILE_PLACE' => 'require',
        'LIVE_PLACE' => 'require',
//        'BEGIN_DATE' => 'require',
//        'END_DATE' => 'require',
        'FAMILY_MEMBERS' => 'require',
        'DRUG_HISTORY_AND_TREATMENT' => 'require',
//        'CURRENT_STATUS' => 'require',
        'CURE_MEASURES' => 'require',
//        'WHETHER_MEDICHINE_ENCOURAGED' => 'require',
//        'WHETHER_DETOXIFICATION_REQUIRED' => 'require',
        'PSYCHOLOGICAL_CONSULTING_PLAN' => 'require',
        'ASSISTANCE_MEASURES' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'UUID.require' => '缺少康复人员ID',
        'NAME.require' => '缺少康复人员姓名',
        'GENDER.require' => '缺少康复人员性别',
        'ID_NUMBER.require' => '缺少康复人员身份证号码',
        'MOBILE.require' => '缺少康复人员手机号码',
        'DOMICILE_PLACE.require' => '缺少康复人员户籍地址',
        'LIVE_PLACE.require' => '缺少康复人员现居住地址',
//        'BEGIN_DATE.require' => '缺少计划开始时间',
//        'END_DATE.require' => '缺少计划结束时间',
        'FAMILY_MEMBERS.require' => '缺少家庭成员信息',
        'DRUG_HISTORY_AND_TREATMENT.require' => '缺少吸毒史及治疗情况说明',
//        'CURRENT_STATUS.require' => '缺少当前情况说明',
        'CURE_MEASURES.require' => '缺少应采取的戒毒治疗措施说明',
//        'WHETHER_MEDICHINE_ENCOURAGED.require' => '缺少是否动员参加药物维持治疗情况说明',
//        'WHETHER_DETOXIFICATION_REQUIRED.require' => '缺少是否需要戒毒治疗说明',
        'PSYCHOLOGICAL_CONSULTING_PLAN.require' => '缺少心理咨询疏导计划说明',
        'ASSISTANCE_MEASURES.require' => '缺少拟采取帮扶救助措施说明'
    ];
}
