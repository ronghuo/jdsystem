<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/23
 */
namespace app\common\validate;

use think\Validate;

class DrugMessageReportsVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
//        'UMID'=>'require',
        'TITLE'=>'require',
        'CLUE_STATUS_ID'=>'require',
        'CLUE_TYPE_ID'=>'require',
        'EMEY_LEVEL_ID'=>'require',
        'REPORT_TYPE_ID'=>'require',
        'GATHER_TYPE_ID'=>'require',
//        'PROVINCE_ID'=>'require',
        'CITY_ID'=>'require',
        'COUNTY_ID'=>'require',
//        'STREET_ID'=>'require',
//        'COMMUNITY_ID'=>'require',
        'ADDRESS'=>'require',
        'REPORT_TIME'=>'require',
        'WRITE_TIME'=>'require',
        'GPS_LAT'=>'require',
        'GPS_LONG'=>'require',
        'CONTENT'=>'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        //        'UMID.require'=>'require',
        'TITLE.require'=>'缺少毒情标题',
        'CLUE_STATUS_ID.require'=>'缺少线索状态',
        'CLUE_TYPE_ID.require'=>'缺少线索类型',
        'EMEY_LEVEL_ID.require'=>'缺少紧急程度',
        'REPORT_TYPE_ID.require'=>'缺少上报方式',
        'GATHER_TYPE_ID.require'=>'缺少采集方式',
//        'PROVINCE_ID.require'=>'缺少发生地区-省份',
        'CITY_ID.require'=>'缺少发生地区-城市',
        'COUNTY_ID.require'=>'缺少发生地区-区/县',
//        'STREET_ID.require'=>'缺少发生地区-街道',
//        'COMMUNITY_ID.require'=>'缺少发生地区-社区',
        'ADDRESS.require'=>'缺少发生地点',
        'REPORT_TIME.require'=>'缺少上报时间',
        'WRITE_TIME.require'=>'缺少填报时间',
        'GPS_LAT.require'=>'缺少地理位置信息',
        'GPS_LONG.require'=>'缺少地理位置信息',
        'CONTENT.require'=>'缺少线索内容',
    ];
}