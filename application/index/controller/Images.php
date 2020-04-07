<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/7
 */
namespace app\index\controller;
use think\Controller;
use think\Request;

class Images extends Controller{


    public function show(Request $request,$name){

        $request_uri = $request->server('REQUEST_URI');
        if(strpos($request_uri,'?')!==false){
            $request_uri = substr($request_uri,0,strpos($request_uri,'?'));
        }
        $res = parse_img_path($request_uri);
        $ext = pathinfo($request_uri,PATHINFO_EXTENSION);
//
//        $paths = explode('.',$request_uri);
//
        $file = './'.ltrim($request_uri,'/');

//        print_r([
//            $request_uri,
//            $file,
//            $res
//        ]);
//        exit;
//        if(!file_exists($file)){
//            header('Content-type:image/png');
//            echo file_get_contents('./static/images/empty.png');
//            exit;
//        }
        try{
            if(empty($res['sizes'])) {
                header('Content-type:image/'.$ext);
                echo file_get_contents($file);
                exit;
            }
        }catch (\Exception $e){
            header('Content-type:image/png');
            echo file_get_contents('./static/images/empty.png');
            exit;
        }

//        $sizes = explode('_',$paths[2]);
//        print_r([
//            $ext,
//            $paths
//        ]);
        //$w = $sizes[0];
//        $h = $h ? : $w;
//        $src = '.'.implode('.',[$paths[0],$paths[1]]);
        $src = '.'.$res['src_path'];
        $thumb = '.' . build_thumb_img_path($res['src_path'], $res['sizes'][0], $res['sizes'][1]);
//        $thumb = '.'.implode('.',[$paths[0].'_'.$w.'_'.$h, $paths[1]]);

//        print_r([
//            $src,
//            $thumb
//        ]);
//        exit;
        try{
            $img = \think\Image::open($src);
            $img->thumb($res['sizes'][0], $res['sizes'][1], 6)->save($thumb);
            header('Content-type:image/'.$ext);

            echo file_get_contents($thumb);

        }catch (\Exception $e){
            header('Content-type:image/png');
            echo file_get_contents('./static/images/empty.png');
        }


        exit;
    }

}