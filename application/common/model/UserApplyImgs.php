<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/22
 */
namespace app\common\model;

use think\Model;

class UserApplyImgs extends BaseModel
{
    public $NAME;
    protected $pk = 'ID';
    public $table = 'USER_APPLY_IMGS';


    public function saveData($ua_id,$images,$media_type=0){
        if(!$ua_id || empty($images)){
            return false;
        }
        $inserts = [];
        foreach($images as $img){
            if(!file_exists($img)){
                continue;
            }
            $inserts[] = [
                'MEDIA_TYPE'=>$media_type,
                'UA_ID'=>$ua_id,
                'SRC_PATH'=>ltrim($img,'.')
            ];
        }
        if(empty($inserts)){
            return false;
        }

        return $this->insertAll($inserts);
    }

}
