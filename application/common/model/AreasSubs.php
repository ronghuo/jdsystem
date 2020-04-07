<?php
namespace app\common\model;



class AreasSubs extends BaseModel{

    protected $pk = 'ID';
    public $table = 'AREAS_SUBS';

    public static function getAName($id){
        $info = self::find($id);
        if($info){
            return $info->NAME;
        }
        return '';
    }
}