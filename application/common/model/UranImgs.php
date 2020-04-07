<?php

namespace app\common\model;

use think\Model;

class UranImgs extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'URAN_IMGS';


    public function saveData($uran_id,$images,$media_type=0){
        if(!$uran_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'MEDIA_TYPE'=>$media_type,
                'URAN_ID'=>$uran_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }
}
