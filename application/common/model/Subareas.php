<?php
namespace app\common\model;

use think\Model;

class Subareas extends BaseModel
{
    //
    protected $pk = 'ID';
    public $table = 'SUBAREAS';


    public static function getNewCode12($p_code_12, $step=0){
        $maxCode = self::where('PID', $p_code_12)->max('CODE12');

        if(!$maxCode){
            $code12 = substr($p_code_12, 0, 9).'100';
        }else{
            $last3 = substr($maxCode, -3);

            //乡镇
            if($last3 == '000'){
                $code12 = $maxCode + (1 + $step) * 1000;
            }else{
                $code12 = $maxCode + (1 + $step) * 20;
            }
        }

        $exist = self::where('CODE12', $code12)->count();
        if($exist > 0){
            return self::getNewCode12($p_code_12, ++$step);
        }
        return $code12;
    }
}