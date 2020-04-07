<?php

namespace app\common\model;

use think\Model;
use Carbon\Carbon;
use think\model\Collection;
use think\Db;

class HelperDiaryImgs extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'HELPER_DIARY_IMGS';

    public function saveData($hd_id,$images,$media_type=0){
        if(!$hd_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'MEDIA_TYPE'=>$media_type,
                'HD_ID'=>$hd_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }
}
