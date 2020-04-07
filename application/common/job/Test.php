<?php

namespace app\common\job;

use think\queue\Job;
use app\common\library\Mylog;

class Test{

    protected $log_file = 'queue_test';


    public function fire(Job $job, $data){

        Mylog::write([
            'runed',
            $data
        ],$this->log_file);

        $job->delete();

        //$job->release(30);

    }

    public function failed($data){

    }
}

