<?php

namespace app\common\model;

use think\Model;

class UserHolidayApplies extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'USER_HOLIDAY_APPLIES';


    public function lists(){
        return $this->hasMany('UserHolidayApplyLists','UHA_ID');
    }



    public function getList($where, $offset=0, $limit=20){
        $sql = 'select uha.`STATUS` as uha_status,uhal.* from USER_HOLIDAY_APPLIES as uha 
left join USER_HOLIDAY_APPLY_LISTS as uhal on uhal.UHA_ID=uha.ID 
join (select UHA_ID,max(ID) as maxId from USER_HOLIDAY_APPLY_LISTS group by UHA_ID) as t1 on t1.maxId= uhal.ID
where uha.ISDEL=0 order by ADD_TIME desc limit ?,?';






    }

}
