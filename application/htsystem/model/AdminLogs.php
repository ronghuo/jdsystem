<?php
/**
 * Created by PhpStorm.
 * User: ronghuo
 * Date: 2020/3/3
 */
namespace app\htsystem\model;

use app\common\model\BaseModel;

class AdminLogs extends BaseModel{

    protected $pk = 'ID';
    public $table = 'ADMIN_LOGS';
}