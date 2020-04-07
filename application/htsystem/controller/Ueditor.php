<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-18
 * Time: 17:33
 */
namespace app\htsystem\controller;

class Ueditor extends Common {
    public function index(){
        \app\common\library\Uedit::img();
    }
}