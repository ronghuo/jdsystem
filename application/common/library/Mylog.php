<?php
/**
 * Created by PhpStorm.
 * User: chenxh
 * Date: 2017/6/27
 * Time: 21:15
 */
namespace app\common\library;

class Mylog{


    public static function write($mseg, $fname='test', $needTimeLine = true){
        if(is_array($mseg)){
            $mseg = print_r($mseg,1);
        }
        if(!is_string($mseg)){
            $mseg = json_encode($mseg);
        }
        $path = Env('runtime_path').'log/'.$fname;
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }
        $file_name = $path.'/'.date('Y-m-d').'.log';
        if($needTimeLine){
            $message = date('Y-m-d H:i:s').PHP_EOL
                .$mseg.PHP_EOL
                .'-------------------------------------------------------'.PHP_EOL;
        }else{
            $message = $mseg.PHP_EOL;
        }

        error_log(
            $message,
            3,
            $file_name
        );
    }

}