<?php

namespace app\htsystem\controller;

use think\facade\App;
use think\Request;

class Index extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $host = getCurHttpHost();
        //qrcode/jquery.qrcode
        $user_app_link = $host.'/html/app.html?f=client';
        $manager_app_link = $host.'/html/app.html';

        $this->assign('user_app_link', $user_app_link);
        $this->assign('manager_app_link', $manager_app_link);

        $js = $this->loadJsCss(array('p:qrcode/jquery.qrcode.min', 'index_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        return $this->fetch();
    }

    public function downloadInstruction()
    {
        $filePath = '后台常用操作手册.docx';
        $file_dir =  App::getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'doc' . DIRECTORY_SEPARATOR . "$filePath";    // 下载文件存放目录

        // 检查文件是否存在
        if (! file_exists($file_dir) ) {
            $this->error('文件未找到');
        }else{
            // 打开文件
            $file = fopen($file_dir, "r");
            // 输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".filesize($file_dir));
            Header("Content-Disposition: attachment;filename=" . $filePath);
            ob_clean();     // 重点！！！
            flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
            //输出文件内容
            //读取文件内容并直接输出到浏览器
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit();
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
