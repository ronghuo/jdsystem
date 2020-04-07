<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/22
 */
namespace app\common\model;

use think\Model;

class UserReportImgs extends BaseModel
{
    public $NAME;
    protected $pk = 'ID';
    public $table = 'USER_REPORT_IMGS';



    public function saveData($ur_id,$images){
        if(!$ur_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'UR_ID'=>$ur_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }
}
