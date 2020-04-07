<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/16
 */
namespace app\common\model;

use think\Collection as collect;

class Options extends BaseModel{
    //
    protected $pk = 'ID';
    public $table = 'OPTIONS';


    protected static $obj;

    public static function instance(){
        if(!self::$obj instanceof self){
            self::$obj = new self();
        }
        return self::$obj;
    }

    public static function getTreeAll(){
        $opts = self::field('ID,PID,NAME')->all();
        $opt_trees = create_level_tree($opts->toArray());

        $data = [];
        foreach($opt_trees as $v){
            $data[$v['NAME']] = $v['SUB'];
        }
        $data['edus'] = BaseCultureType::all();
        $data['job_status'] = BaseWorkStatus::all();
//        $data['marital_status'] = BaseMarryType::all();
        $data['marital_status'] = BaseMarryType::all();
        $data['genders'] = BaseSexType::all();
        $data['card_types'] = BaseCertificateType::all();
        $data['user_status'] = BaseUserStatus::all();
        $data['user_sub_status'] = BaseUserSubStatus::all();
        $data['danger_level'] = BaseUserDangerLevel::all();

        return $data;
    }

    public static function getGender($id){
        $info = BaseSexType::find($id);
        return $info ? $info->toArray() : ['ID'=>0,'NAME'=>''];
//        return self::getNameById($id)['NAME'];
    }

    public static function getEdu($id){
        $info = BaseCultureType::find($id);
        return $info ? $info->toArray() : ['ID'=>0,'NAME'=>''];
//        return self::getNameById($id)['NAME'];
    }

    public static function getJobStatus($id){
        $info = BaseWorkStatus::find($id);
        return $info ? $info->toArray() : ['ID'=>0,'NAME'=>''];
//        return self::getNameById($id)['NAME'];
    }

    public static function getMaritalStatus($id){
        $info = BaseMarryType::find($id);
        return $info ? $info->toArray() : ['ID'=>0,'NAME'=>''];
//        return self::getNameById($id)['NAME'];
    }

    public static function getDrugType($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getNarcoticsType($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getClueStatus($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getClueType($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getEmeyLevel($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getReportType($id){
        return self::getNameById($id)['NAME'];
    }

    public static function getGatherType($id){
        return self::getNameById($id)['NAME'];
    }



    public static function getNameById($id){

        $info = self::find($id);
        return $info ? $info->toArray() : ['ID'=>0,'NAME'=>''];

//        $res = collect::make($data)->filter(function($t) use($id){
//            if($t['ID'] == $id){
//                return $t;
//            }
//        })->shift();
//
//        return $res ? : ['ID'=>0,'NAME'=>''];
    }

}