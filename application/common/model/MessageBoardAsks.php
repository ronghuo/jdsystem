<?php

namespace app\common\model;

use think\Model;
use think\Db;

class MessageBoardAsks extends BaseModel
{

    protected $pk = 'ID';
    public $table = 'MESSAGE_BOARD_ASKS';


    public function ANSWERS(){
        return $this->hasMany('MessageBoardAnswers','ASKID');
    }

    public function getQAs(){

    }

}
