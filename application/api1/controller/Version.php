<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller;

use app\common\model\AppVersion;

class Version extends Common {

    public function index() {
        $info = AppVersion::order('ID DESC')->limit(1)->find();
        return $this->ok('ok', $info);
    }

}