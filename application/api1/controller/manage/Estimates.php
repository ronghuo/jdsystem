<?php
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use app\common\model\UserEstimates;
use app\common\model\BaseUserDangerLevel;

class Estimates extends  Common{


    public function listByUser(Request $request, $uuid = 0){

        $dangerLevels = create_kv(BaseUserDangerLevel::all()->toArray(), 'ID', 'NAME');

        $list = UserEstimates::field('UPDATE_TIME', true)->where('UUID', $uuid)
            ->order('ADD_TIME', 'desc')->select()
            ->map(function($item) use($dangerLevels){
                $item->danger_level = $dangerLevels[$item->DANGER_LEVEL_ID] ?? '';
                return $item;
        });


        return $this->ok('',[
            'list'=>$list->toArray()
        ]);

    }


}