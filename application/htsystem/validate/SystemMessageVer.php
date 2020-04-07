<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/13
 */
namespace app\htsystem\validate;
use think\Validate;

class SystemMessageVer extends Validate{


    protected $rule = [
        'CLIENT_TAG'  =>  'require|in:1,2,3',
        'TITLE' =>  'require',
        'CONTENT' =>  'require',
    ];

    protected $message = [
        'CLIENT_TAG.require'  =>  '缺少接收端',
        'CLIENT_TAG.in'  =>  '缺少接收端',
        'TITLE.require' =>  '缺少消息标题',
        'CONTENT.require' =>  '缺少消息内容',
    ];


}