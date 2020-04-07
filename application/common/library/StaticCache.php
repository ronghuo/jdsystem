<?php
namespace app\common\library;


class StaticCache{

    const CACHE_KEY = 'StaticCache';

    public static function getVer($file_path){

        $data = cache(self::CACHE_KEY);
        if($data){
            return $data;
        }

        if(!file_exists($file_path)){
            $data = self::refresh($file_path);
            return $data;
        }

        $json = file_get_contents($file_path);

        if(!$json){
            $data = self::refresh($file_path);
            return $data;
        }
        $data = \json_decode($json, 1);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $data = self::refresh($file_path);
            return $data;
        }

        return $data;

    }

    public static function refresh($file_path){
        $data = [
            'version'=>time()
        ];

//        if(!file_exists($file_path)){
//            mkdir($file_path, 0777);
//        }

        if(file_exists($file_path) && !is_readable($file_path)){
            unlink($file_path);
            //mkdir($file_path, 0777);
        }
//        $json = json_encode($data);
        file_put_contents($file_path, json_encode($data));

        cache(self::CACHE_KEY, $data);
        return $data;
    }


}