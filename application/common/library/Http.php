<?php
/**
 *
 * User: XiaoHui
 * Date: 2016/5/25 17:46
 */
namespace app\common\library;
class Http{
    /**
     * GET 请求
     * @param string $url
     */
    public static function curl_get($url,$header=array()){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        if(!empty($header)){
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    public static function curl_post($url,$param=array(),$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            if(!empty($param)){
                foreach($param as $key=>$val){
                    $aPOST[] = $key."=".urlencode($val);
                }
                $strPOST =  join("&", $aPOST);
            }
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        if(!empty($param)){
            curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        }
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * 拉取远程图片
     * @return mixed
     */
    public static function save_remote($file_url,$save_path,$save_name) {
        //set_time_limit(10) ;
        if(!$save_path || !$save_name){
            return false;
        }
        $imgUrl = htmlspecialchars($file_url);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);
        //echo $imgUrl;
        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            return false;
        }
        //exit;
        //获取请求头并检测死链
        $heads = get_headers($imgUrl);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            return false;
        }
        //$allowFiles = array();
        //格式验证(扩展名验证和Content-Type验证)
        /*$fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, $this->config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }*/
        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);
        //检查文件大小是否超出限制
        /*if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }*/
        $filePath = $save_path.'/'.$save_name;
        //移动文件
        if (!(file_put_contents($filePath, $img) && file_exists($filePath))) { //移动失败
            return false;
        } else { //移动成功
            return array('path'=>$save_path,'file_name'=>$save_name);
        }
    }
    public static function wget_down($file_url,$save_path,$save_name){
        $save = $save_path.'/'.$save_name;
        //echo $save;exit;
        unset($output);
        exec('wget -q -O '.$save.' '.$file_url,$output,$return_var);
        if($return_var==0){
            return array('path'=>$save_path,'file_name'=>$save_name);
        }
        return false;
    }
}