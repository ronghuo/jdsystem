<?php
namespace app\common\job;

use think\queue\Job;
use app\common\library\Mylog;
use app\common\library\Jpush as PN;
use app\common\model\UserUsers;
use app\common\model\UserManagerPower;

class PushToManager{
    protected $log_file = 'queue_jpush_manager';

    public function fire(Job $job, $data){

        Mylog::write($data,$this->log_file);

        try{

            $user = UserUsers::where('ISDEL',0)->where('ID',$data['uuid'])->find();

            if(!$user){
                Mylog::write('not found user',$this->log_file);
            }
            $user_manage_id = 0;

            //level 4
            $levels4 = UserManagerPower::where('STREET_ID',$user->STREET_ID)->where('LEVEL',4)->select();
            if(!$levels4){
                foreach($levels4 as $lv){
                    if(in_array($user->COMMUNITY_ID,explode(',',$lv->AREA_IDS))){
                        $user_manage_id = $lv->UMID;

                        break;
                    }
                }
            }

            if(!$user_manage_id){
                //level 3
                $levels3 = UserManagerPower::where('COUNTY_ID',$user->COUNTY_ID)->where('LEVEL',3)->select();
                if(!$levels3){
                    foreach($levels3 as $lv){
                        if(in_array($user->STREET_ID,explode(',',$lv->AREA_IDS))){
                            $user_manage_id = $lv->UMID;

                            break;
                        }
                    }
                }
            }
            if(!$user_manage_id){
                Mylog::write('not found manage user',$this->log_file);
            }


            $res = PN::sendToManage(
                $user_manage_id,
                $data['message'],
                $data['metas']
            );


            $job->delete();
            Mylog::write('success',$this->log_file);

        }catch (\Exception $e){
            $job->delete();
            Mylog::write('Error : '
                . $e->getFile() . '-' . $e->getLine() . PHP_EOL
                . $e->getMessage(),
                $this->log_file);
        }

    }

    public function failed($data){
        Mylog::write('failed',$this->log_file);
    }

}