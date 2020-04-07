<?php

namespace app\common\validate;

use think\Validate;

class ArticlesVer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'CLIENT_TAG'=>'require',
	    'CATE_ID'=>'require',
	    'POSTER_UID'=>'require',
	    'TITLE'=>'require',
//	    'COVER_IMG'=>'require',
	    'CONTENT'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'CLIENT_TAG.require'=>'缺少客户端',
        'CATE_ID.require'=>'缺少分类信息',
        'POSTER_UID.require'=>'缺少发布者信息',
        'TITLE.require'=>'缺少标题',
//        'COVER_IMG.require'=>'缺少封面',
        'CONTENT.require'=>'缺少内容'
    ];
}
