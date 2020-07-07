<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/8
 */
namespace app\common\library;

use think\Request;
use app\common\library\Mylog;
use app\common\model\Uploads as UploadModel;

class Uploads{

    const TYPES = [0=>'',1=>'apply',2=>'report',3=>'drug'];

    // 视频上传
    public function videos(Request $request,$save_path='', $field_name = 'videos'){


        $type = $request->param('type',0,'int');

        $config = [
            'field_name'=>$field_name,
            'media_type'=>2,
            'type'=>$type,
            'dir'=>'videos',
            'size_limit'=>100*1024*1024,//100M
            'ext_limit'=>'mp4,3gp',
            'save2db'=>true,
            'img_thumb'=>'',
            'save_path'=>$save_path
        ];

        return $this->uploadFile($request,$config);
    }
    // 音频上传
    public function audios(Request $request,$save_path='', $field_name = 'audios'){

        $type = $request->param('type',0,'int');

        $config = [
            'field_name'=>$field_name,
            'media_type'=>1,
            'type'=>$type,
            'dir'=>'audios',
            'size_limit'=>50*1024*1024,//50M
            'ext_limit'=>'mp3',
            'save2db'=>true,
            'img_thumb'=>'',
            'save_path'=>$save_path
        ];

        return $this->uploadFile($request,$config);

    }

    public function images(Request $request,$save_path='', $field_name = 'images'){

        $type = $request->param('type',0,'int');

        $config = [
            'field_name' => $field_name,
            'media_type' => 0,
            'type' => $type,
            'dir' => self::TYPES[$type],
            'size_limit' => 10*1024*1024,//10M
            'ext_limit' => 'jpg,jpeg,png,gif',
            'img_thumb' => '600',
            'save2db' => true,
            'save_path' => rtrim($save_path,'/')
        ];

        return $this->uploadFile($request, $config);
    }

    protected function uploadFile(Request $request,$config=[]){
        $files = [];
        try{
            $files = $request->file($config['field_name']);
            if (empty($files)) {
                return [
                    'success'=>false,
                    $config['field_name']=> [],
                    'save_files'=>[],
                    'errors'=>[
                        '没有找到可上传的文件'
                    ]
                ];
            }
            $save_files = [];
            $errors = [];
            $returns = [];
            if(isset($config['save_path']) && $config['save_path']){
                $save_path = $config['save_path'];
            }else{
                $save_path = './uploads/'.$config['dir'];
            }

            foreach($files as $file) {
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->rule('buildUploadFileName')->validate(['size'=>$config['size_limit'],'ext'=>$config['ext_limit']])->move($save_path);
                if($info){
                    // 成功上传后 获取上传信息
                    // 输出 jpg
                    //echo $info->getExtension();
                    // 输出 42a79759f284b767dfcb2a0197904287.jpg
                    $file_path = str_replace("\\",'/',$save_path.'/'.$info->getSaveName());
                    $save_files[] = $file_path;
                    $returns[] = build_http_img_url($file_path);
//                    $thumb_path = build_thumb_img_path($save_path.'/'.$info->getSaveName(), 600);
//                    $returns[] = build_http_img_url(str_replace("\\",'/',$thumb_path));
//                echo $info->getFilename();
                }else{
                    // 上传失败获取错误信息
                    $errors[] = $file->getError();
                }
            }

            if (!empty($save_files) && isset($config['save2db']) && $config['save2db']) {
                $this->saveToDb($save_files, $config['type'],$config['media_type']);
            }


            if(!empty($errors)){
                Mylog::write([
                    'save_files'=>$save_files,
                    'errors'=>$errors,
                    '$_FILES'=>$_FILES
                ],'error_upload');
            }

            return [
                'success'=>count($returns)>0 ? true : false,
                $config['field_name']=> $returns,
                'save_files'=>$save_files,
                'errors'=>$errors
            ];

        }catch (\Exception $e){

            Mylog::write([
                'files'=>$files,
                '$_FILES'=>$_FILES,
                'config'=>$config,
                'errors'=>[$e->getMessage()],
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine()
            ],'error_upload');


            return [
                'success'=>false,
                $config['field_name']=> [],
                'save_files'=>[],
                'errors'=>[$e->getMessage()]
            ];
        }
    }

    protected function saveToDb($files,$type,$media_type=0){
        $data = [];
        foreach($files as $file){
            $data[] = [
                'TYPE'=>$type,
                'MEDIA_TYPE'=>$media_type,
                'SRC_PATH'=>ltrim($file,'.')
            ];
        }

        (new UploadModel())->insertAll($data);
    }
}