<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/17
 */
namespace app\common\library;

use app\common\model\Carousels as CarouselModel;
use app\common\model\Articles;

class Carousel{


    public static function parseRow($row){
        switch($row['STABLE']){
            //
            case 'news':
                $info = Articles::field('ID,TITLE,COVER_IMG as IMG')->find($row['SID']);
                $row->TITLE = $info->TITLE;
                if(!$row->IMG){
                    $row->IMG = $info->IMG;
                }
                $row->IMG_URL = build_http_img_url($row->IMG);

                if(!$row->JUMP_LINK){
                    $row->JUMP_LINK = get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>$row['CLIENT_TAG'],'type'=>1,'id'=>$info->ID]);
                }

                break;
            default:
                $row->IMG_URL = build_http_img_url($row->IMG);

                break;
        }


        return $row;
    }


    public static function getStableInfo($stable,$sid){
        switch($stable){
            //
            case 'news':
                $info = Articles::field('ID,TITLE,COVER_IMG as IMG')->find($sid);
                $info->IMG_URL = build_http_img_url($info->IMG);
                $info->admin_info_url = url('Articles/read',['id'=>$sid]);

                break;
            default:
                $info = null;

                break;
        }


        return $info;
    }


    public static function updateStable($stable,$sid,$carousel_id){
        switch($stable){
            //
            case 'news':
                return Articles::where('ID',$sid)->update(['CAROUSEL_ID'=>$carousel_id]);
                break;
        }

    }

}