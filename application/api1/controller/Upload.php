<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller;

use think\Request;
use app\common\library\Uploads as UploadHelper;

class Upload extends Common{



    // 视频上传
    public function videos(Request $request){

        $res = (new UploadHelper())->videos($request);
        if($res['success']){
            return $this->ok('ok',$res);
        }

        return $this->fail($res['errors'][0]);


    }
    // 音频上传
    public function audios(Request $request){

        $res = (new UploadHelper())->audios($request);
        if($res['success']){
            return $this->ok('ok',$res);
        }

        return $this->fail($res['errors'][0]);

    }

    public function images(Request $request){

        $res = (new UploadHelper())->images($request);
        if($res['success']){
            return $this->ok('ok',$res);
        }

        return $this->fail($res['errors'][0]);
    }




}