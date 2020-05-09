<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-18
 * Time: 17:24
 */
namespace app\common\library;


use think\facade\Cache;

class Uedit{
    //配置文件
    const CONFIG_FILE = './static/plugin/ueditor/php/config.json';
    //图片最大宽度
    const MAX_WIDTH = 1000;
    const MAX_HEIGHT = 1500;
    //
    const IMG_DIR = './uploads/ueditor/';

    // UEditor图片路径缓存键
    const CACHE_UEDITOR_IMAGE = 'UEditor::Images';

    static public function img() {
        $action = input('get.action');
        switch ($action) {
            case 'config':
                $result = json_encode(self::getConfig());
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = self::imgUpload($action);
                break;
            //历史图库
            case 'listimage':
                /*$start = input('get.start', 0);
                $size = input('get.size', 20);
                $P = M('UeditImgs');

                $total = $P->count();

                $list = $P->field('src as  url')->order('id desc')->limit($start . ',' . $size)->select();
                //设置缓存的标记
                foreach ($list as $k => $lt) {
                    $list[$k]['url'] = self::getSrc(self::IMG_DIR.$lt['url']) . '?' . date('Ym');
                }
                $result = json_encode(array('state' => 'SUCCESS', 'list' => $list, 'start' => $start, 'total' => $total));*/

                break;
            default:
                $result = json_encode(array('state' => '请求地址出错'));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            echo $_GET["callback"] . '(' . $result . ')';
        } else {
            echo $result;
        }
    }

    //读取编辑器配置
    static public function getConfig() {
        return json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(self::CONFIG_FILE)), true);
    }

    //图片上传
    static public function imgUpload($action) {
        /* 上传配置 */
        $base64 = "upload";
        $CONFIG = self::getConfig();
        switch ($action) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new \extend\Ueuploader($fieldName, $config, $base64);

        $res = $up->getFileInfo();
        //print_r($res);
        if ($action == 'uploadimage' && $res['state']=='SUCCESS') {

            $img = \think\Image::open($res['url']);

            //图片最大宽度为1000px
            if ($img->type() != 'gif' && $img->width() > self::MAX_WIDTH) {
                $img->thumb(self::MAX_WIDTH, self::MAX_HEIGHT, 1)->save($res['url']);
            }
            //self::savepic($res['url']);
            $res['url'] = self::getSrc($res['url']);

            // 将UEditor编辑器上传的图片文件路径临时缓存到Redis
            if (Cache::has(self::CACHE_UEDITOR_IMAGE)) {
                $value = json_decode(Cache::get(self::CACHE_UEDITOR_IMAGE));
                array_push($value, $res['url']);
            } else {
                $value = [$res['url']];
            }
            cache(self::CACHE_UEDITOR_IMAGE, json_encode($value), 1800);
        }

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */
        /* 返回数据 */
        return json_encode($res);
    }

    static public function getSrc($url){
        $httpHost = getCurHttpHost();

        return $httpHost.ltrim($url,'.');
    }

    static public function savepic($src) {
        /*$PU = M('UeditImgs');
        $src = str_replace(self::IMG_DIR,'',$src);

        $md5 = md5($src);

        if ($PU->where(array('md5' => $md5))->count()) {
            return '';
        }
        $data = array('md5' => $md5, 'src' => $src, 'dateline' => time());


        $PU->add($data);*/
    }
}