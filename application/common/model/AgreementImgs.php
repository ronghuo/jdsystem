<?php

namespace app\common\model;

class AgreementImgs extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'AGREEMENT_IMGS';


    public function saveData($agreement_id, $images) {
        if (!$agreement_id || empty($images)) {
            return false;
        }
        $inserts = [];
        foreach($images as $img) {
            $inserts[] = [
                'AGREEMENT_ID' => $agreement_id,
                'SRC_PATH' => $img
            ];
        }
        if (empty($inserts)) {
            return false;
        }

        return $this->insertAll($inserts);
    }
}
