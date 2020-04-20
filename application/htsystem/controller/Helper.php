<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/28
 */
namespace app\htsystem\controller;



use app\common\library\JqueryCateData;


class Helper extends Common {

    public function refreshCache(){
        $file_path = config('cache_version_file');

        $res = \app\common\library\StaticCache::refresh($file_path);

        return \GuzzleHttp\json_encode($res);
    }

    public function show(){


        $urls =  [
            'createHHLevelAreasJson'=>url('Helper/createHHLevelAreasJson'),
            'createAreasJson'=>url('Helper/createAreasJson'),
            'createDmmcJson'=>url('Helper/createDmmcJson'),
            'createAreaSubJson'=>url('Helper/createAreaSubJson'),
        ];
        foreach($urls as $key=>$v){
            echo '<a href="'.$v.'" target="_blank">'.$key.'</a><br/><br/>';
        }
        exit();
    }

    //20190822 更新
    public function createHHLevelAreasJson(){

       JqueryCateData::createHHLevelAreasJson();
       $this->refreshCache();
        return 'ok';
    }
    //20190822 更新
    public function createAreasJson(){

       JqueryCateData::createAreasJson();
        $this->refreshCache();
        return 'ok';
    }
    //20190822 更新
    public function createDmmcJson(){

        JqueryCateData::createDmmcJson();
        $this->refreshCache();
        return 'ok';
    }
    //20190822 更新
    public function createAreaSubJson(){
       JqueryCateData::createAreaSubJson();
        $this->refreshCache();
        return 'ok';
    }


}