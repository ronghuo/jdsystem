<?php
/**
 * Created by PhpStorm.
 * User: chenxh
 * Date: 2018/9/26
 * Time: 10:51
 */
namespace app\common\library;

class Shorturl{


    static public function sina_create($url){
        $source = '954427186';
        $api_url = 'http://api.t.sina.com.cn/short_url/shorten.json?source='.$source.'&url_long='.$url;
        $res = Http::curl_get($api_url);
        $res = json_decode($res,1);
        if(isset($res[0]) && isset($res[0]['url_short'])){
            return $res[0]['url_short'];
        }
        return false;
    }


    static public function dwz_create($url){
        $res = Http::curl_post('http://dwz.cn/admin/create',json_encode(['url'=>$url]));
        //"{"Code":-2,"ShortUrl":"","LongUrl":"","ErrMsg":"get body failed"}"
        return json_decode($res,1);
    }


}