<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\common\model;


class ArticleCate extends BaseModel
{
    //
    protected $pk = 'ID';
    public $table = 'ARTICLE_CATES';

    public function Articles()
    {
        return $this->hasMany('Articles','CATE_ID','ID');
    }

    public function getClientTagTextAttr($value,$data){
        $map = [1=>'康复端',2=>'管理端',3=>'康复端&管理端'];
        return isset($map[$data['CLIENT_TAG']]) ? $map[$data['CLIENT_TAG']] : '';
    }
}