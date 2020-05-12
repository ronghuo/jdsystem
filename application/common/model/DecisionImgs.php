<?php

namespace app\common\model;

class DecisionImgs extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_DECISIONS_IMGS';


    public function saveData($decision_id, $images, $media_type = 0) {
        if (!$decision_id || empty($images)) {
            return false;
        }
        $inserts = [];
        foreach($images as $img) {
            $inserts[] = [
                'MEDIA_TYPE'=>$media_type,
                'USER_DECISIONS_ID' => $decision_id,
                'SRC_PATH' => ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }
}
