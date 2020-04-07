<?php
namespace app\common\job;

use think\queue\Job;
use app\common\library\Mylog;
use app\common\library\UserPhoneData;

class SavePhoneData{

    protected $log_file = 'queue_phone_data';


    public function fire(Job $job, $data){

        Mylog::write([
            'runed',
            $data
        ],$this->log_file);

        try{

            if(!isset($data['cache_key']) || !isset($data['type']) || !isset($data['uuid'])){
                Mylog::write('params error',$this->log_file);
                $this->delCache($data);
                $job->delete();
                return;
            }

            $pdata = cache($data['cache_key']);
            if(!$pdata){
                Mylog::write('empty p data',$this->log_file);
                $this->delCache($data);
                $job->delete();
                return;
            }

            $upd = new UserPhoneData();
            //$res = [];
            switch($data['type']){
                case 'sms':
                    $res = $upd->saveSms($data['uuid'],$pdata);
                    break;
                case 'call':
                case 'calls':
                    $res = $upd->saveCalls($data['uuid'],$pdata);
                    break;
                case 'address':
                    $res = $upd->saveAddress($data['uuid'],$pdata);
                    break;
                default:
                    $res = ['err'=>1,'msg'=>'unknow type'];
                    break;
            }

            Mylog::write($res,$this->log_file);


            $this->delCache($data);
            $job->delete();

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

    public function delCache($data){
        if(isset($data['cache_key'])){
            cache($data['cache_key'],null);
        }
    }
}

