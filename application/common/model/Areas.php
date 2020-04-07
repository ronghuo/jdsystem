<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/16
 */
namespace app\common\model;

class Areas extends BaseModel{

    protected $pk = 'ID';
    public $table = 'AREAS';



    public static function getAName($id){
        $info = self::find($id);
        if($info){
            return $info->NAME;
        }
        return '';
    }
}