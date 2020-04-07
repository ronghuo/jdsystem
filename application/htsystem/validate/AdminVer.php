<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-13
 * Time: 19:47
 */
namespace app\htsystem\validate;
use think\Validate;

class AdminVer extends Validate{

    const SCENE_ADD = 'add';
    const SCENE_EDIT = 'edit';
    const SCENE_EDIT_LOG = 'edit_log';

    protected $rule = [
        'LOG'  =>  'require|alphaDash|unique:\\app\\htsystem\\model\\Admins',//|unique:admin
        'PWD' =>  'require',
        'NAME' =>  'require',
        'ROLE_ID' =>  'require',
        'DMMC_ID' =>  'require',
        'MOBILE'=>'require' //|unique:\\app\\htsystem\\model\\Admins
    ];

    protected $message = [
        'LOG.require'  =>  '账号必须',
        'LOG.alphaDash'  =>  '账号只能为字母和数字，下划线_及破折号-',
        'LOG.unique'  =>  '该账号已使用',
        'PWD.require' =>  '请设置密码',
        'NAME.require' =>  '请填写姓名',
        'ROLE_ID.require' =>  '请选择管理权限角色',
        'DMMC_ID.require' =>  '请选择单位',
        'MOBILE.require' =>  '请填写手机号码',
        'MOBILE.unique' =>  '该手机号码已存在',
    ];

    protected $scene = [
        'add'=>['LOG','PWD','NAME','ROLE_ID','DMMC_ID','MOBILE'],
        //'edit'  =>  ['LOG','NAME','MOBILE'],
        'edit'  =>  ['NAME','ROLE_ID','DMMC_ID'],
        'edit_log' => ['LOG', 'NAME', 'ROLE_ID', 'DMMC_ID']
    ];
}
