<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/28
 */
namespace app\htsystem\model;

use app\common\model\BaseModel;

class Admins extends BaseModel{

    protected $pk = 'ID';
    public $table = 'ADMINS';

    public function getGenderTextAttr($value,$data){
        $maps = [0=>'未知',1=>'男',2=>'女'];
        
        return isset($maps[$data['GENDER']]) ? $maps[$data['GENDER']] : $maps[0];
    }

    public function getStatusTextAttr($value,$data){
        $maps = [0=>'<span class="label label-important">禁止登录</span>',1=>'<span class="label label-success">正常</span>'];

        return isset($maps[$data['STATUS']]) ? $maps[$data['STATUS']] : $maps[0];
    }

    public function getWorkTextAttr($value,$data){
        $maps = [0=>'<span class="label">离职</span>',1=>'<span class="label label-success">在职</span>'];

        return isset($maps[$data['IS_WORK']]) ? $maps[$data['IS_WORK']] : $maps[0];
    }

    public function changePwdByMobile($mobile, $encryptedPwd, $salt) {
        $admin = $this->where(['MOBILE' => $mobile, 'ISDEL' => 0])->find();
        if (empty($admin)) {
            return;
        }
        $admin->PWD = $encryptedPwd;
        $admin->STAT = $salt;
        $admin->save();
    }
}