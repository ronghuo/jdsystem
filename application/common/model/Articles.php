<?php

namespace app\common\model;

use think\Model;

class Articles extends BaseModel
{
    //
    protected $pk = 'ID';
    public $table = 'ARTICLES';

    public function cate()
    {
        return $this->belongsTo('ArticleCate','CATE_ID','ID');
    }

    public function getClientTagTextAttr($value,$data){
        $map = [1=>'康复端',2=>'管理端',3=>'康复端&管理端'];
        return isset($map[$data['CLIENT_TAG']]) ? $map[$data['CLIENT_TAG']] : '';
    }
}
