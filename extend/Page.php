<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------
namespace extend;
use THink\Request;
class Page {

    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
    // 默认分页变量名
    protected $varPage;
    protected $request = null;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   'p';//C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->request = request();
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        //$this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        $this->nowPage      =   $this->request->get($this->varPage,$this->request->route($this->varPage,1));


        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }
    
    public function replaceConfig($config){
        $this->config = $config;
    }


    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        //$p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);
        $parameter = $this->request->param();
        if(!isset($parameter[$this->varPage])){
            $parameter[$this->varPage] = '__page__';
        }
        $parameter[$this->varPage] = '__page__';
        //print_r($parameter);
        $url =   url('',$parameter);
        //echo $url;
        // 分析分页参数
        /*if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__page__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__page__';
            $url            =   U('',$parameter);
        }*/
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__page__',$upRow,$url)."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__page__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        $nowCoolPage;
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__page__',$preRow,$url)."' >上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".str_replace('__page__',1,$url)."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__page__',$nextRow,$url)."' >下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".str_replace('__page__',$theEndRow,$url)."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".str_replace('__page__',$page,$url)."'>".$page."</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<span class='thispage'>".$page."</span>";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%downPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$downPage),$this->config['theme']);
        return $pageStr;
    }
    /**
     * 分页显示输出 样式2
     * @access public
     */
    public function show2() {
        if(0 == $this->totalRows) return '';
        //$p              =   $this->varPage;
        //$nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        /*if($this->url){
            $depr       =   config('pathinfo_depr');
            $url        =   rtrim(Url('/'.$this->url,'',false),$depr).$depr.'__page__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                //unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__page__';
            //print_r($parameter);
            $url            =   Url('',$parameter);
            $url = str_replace($_SERVER["SCRIPT_NAME"],'',$url);
        }*/
        //$p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);
        $parameter = $this->request->param();
        if(!isset($parameter[$this->varPage])){
            $parameter[$this->varPage] = '__page__';
        }
        $parameter[$this->varPage] = '__page__';
        //print_r($parameter);
        $url =   url('',$parameter);
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<li class='prev'><a href='".str_replace('__page__',$upRow,$url)."'>".$this->config['prev']."</a></li>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<li class='next'><a href='".str_replace('__page__',$downRow,$url)."'>".$this->config['next']."</a></li>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        //$nowCoolPage;
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<li><a href='".str_replace('__page__',$preRow,$url)."' >上".$this->rollPage."页</a></li>";
            $theFirst   =   "<li><a href='".str_replace('__page__',1,$url)."' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<li><a href='".str_replace('__page__',$nextRow,$url)."' >下".$this->rollPage."页</a></li>";
            $theEnd     =   "<li><a href='".str_replace('__page__',$theEndRow,$url)."' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li><a href='".str_replace('__page__',$page,$url)."'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li class='active'><a href='javascript:;'>".$page."</a></li>";
                }
            }
        }
        //print_r($this->config);exit;
        $pageStr     =   str_replace(
            array('%nowPage%','%totalRow%','%totalPage%','%upPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%downPage%'),
            array($this->nowPage,$this->totalRows,$this->totalPages,$upPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$downPage),$this->config['theme']);
        return $pageStr;
    }


    public function getHeader(){
        return '共' . $this->totalRows . '条 '.$this->nowPage.'/'.$this->totalPages.' 页';
    }

}
