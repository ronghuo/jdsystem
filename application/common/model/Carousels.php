<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/17
 */
namespace app\common\model;

class Carousels extends BaseModel{

    protected $pk = 'ID';
    public $table = 'CAROUSELS';
    protected $field = [
        'ID',
        'CLIENT_TAG',
          'POS',
          'STABLE' ,
          'SID' ,
          'TYPE',
          'TITLE',
          'IMG',
          'JUMP_LINK',
          'ORDERID',
          'BEGIN_TIME',
          'END_TIME',
          'ADD_TIME',
          'ISDEL',
          'DEL_TIME',

    ];
}