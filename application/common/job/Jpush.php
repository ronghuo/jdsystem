<?php
namespace app\common\job;


use think\queue\Job;
use app\common\library\Mylog;
use app\common\library\Jpush as PN;

class Jpush{

    protected $log_file = 'queue_jpush';


    public function fire(Job $job, $data){
//       $data = [
//           'type'=>'u|m',
//           'user_id'=>31,
//           'message'=>'message...',
//           'metas'=>['url'=>'url...']
//       ];

        Mylog::write($data,$this->log_file);

        try{
            if($data['type']=='u'){
                $res = PN::sendToUser(
                    $data['user_id'],
                    $data['message'],
                    $data['metas']
                );
            }elseif($data['type']=='m'){

                $res = PN::sendToManage(
                    $data['user_id'],
                    $data['message'],
                    $data['metas']
                );
            }
            //$job->release(30);
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