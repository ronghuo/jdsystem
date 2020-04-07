<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/17
 */
namespace app\common\model;

use think\Model;

class WaitDeleteFiles extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'WAIT_DELETE_FILES';


    public static function addOne($data){
        return self::insert([
            'STABLE'=>$data['table'],
            'SID'=>$data['id'],
            'PATH'=>$data['path']
        ]);
    }

}