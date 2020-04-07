<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/23
 */
namespace app\common\model;

class DrugMessageReportImgs extends BaseModel{

    protected $pk = 'ID';
    public $table = 'DRUG_MESSAGE_REPORT_IMGS';


    public function saveData($dmr_id,$images,$media_type=0){
        if(!$dmr_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'MEDIA_TYPE'=>$media_type,
                'DMR_ID'=>$dmr_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }

}