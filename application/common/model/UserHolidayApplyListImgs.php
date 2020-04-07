<?php

namespace app\common\model;

use think\Model;

class UserHolidayApplyListImgs extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_HOLIDAY_APPLY_LIST_IMGS';


    public function saveData($uhal_id,$images){
        if(!$uhal_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'UHAL_ID'=>$uhal_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }

}
