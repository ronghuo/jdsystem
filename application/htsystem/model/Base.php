<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-13
 * Time: 16:37
 */
namespace app\htsystem\model;
use think\Model;
use think\Db;
class Base extends Model{


    protected function _get_count($table,$where){
        return Db::table($table)->where($where)->count();
    }

    protected function _get_info($table,$where,$field='*'){
        return Db::table($table)->field($field)->where($where)->find();
    }

    protected function _get_list($table,$where,$order,$field='*',$limit=15){
        return Db::table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    protected function _del_row($table,$where){
        return Db::table($table)->where($where)->delete();
    }

    protected function _update_data($table,$where,$update){
        return Db::table($table)->where($where)->update($update);
    }

    protected function _insert_data($table,$data,$return_id=false){
        if($return_id){
            return Db::table($table)->insertGetId($data);
        }else{
            return Db::table($table)->insert($data);
        }
    }

    protected function _insert_all($table,$data){
        return Db::table($table)->insertAll($data);
    }

    protected function _query($sql,$bind=[]){
        return Db::query($sql,$bind);

    }
    protected function _execute($sql,$bind=[]){
        return Db::execute($sql,$bind);
    }
}