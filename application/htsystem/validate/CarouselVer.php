<?php
namespace app\htsystem\validate;
use think\Validate;

class CarouselVer extends Validate{


    protected $rule = [
        'CLIENT_TAG'  =>  'require|in:1,2,3',
        //'TITLE' =>  'require',
        //'PIC' =>  'require',
        'JUMP_LINK' =>  'require',
        'STABLE'=>'require',
        'SID'=>'require',
    ];

    protected $message = [
        'CLIENT_TAG.require'  =>  '缺少接收端',
        'CLIENT_TAG.in'  =>  '缺少接收端',
        //'TITLE.require' =>  '缺少消息标题',
        //'PIC.require' =>  '缺少轮播图片',
        'JUMP_LINK.require' =>  '缺少跳转链接',
        'STABLE.require' =>  '缺少源数据',
        'SID.require' =>  '缺少源数据',
    ];

    protected $scene = [
        'un_stable'=> ['CLIENT_TAG','JUMP_LINK'],
        'stable'=> ['CLIENT_TAG','STABLE','SID'],
    ];
}