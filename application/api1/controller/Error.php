<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller;

class Error extends Common{

    public function index(){

        return  $this->fail('not found,controller','',404);


    }
}