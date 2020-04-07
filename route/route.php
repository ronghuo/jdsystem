<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::get('think', function () {
    return '<img src="http://jd.appasd.com/uploads/20190318/c5cafc2208189b0204a61d5026ffe5b2.png"/>';
});

/*Route::get('/jdsyem/login','app\\htsystem\\controller\\Login@index');

Route::get('/jdsyem','app\\htsystem\\controller\\Index@index')->middleware('SystemAuth');*/

//
//default empty image
Route::get('uploads/:name','index/Images/show');
//Route::get('uploads/:name',function(){
//    header('Content-type:image/png');
//    echo file_get_contents('./static/images/empty.png');
//    exit;
//});


Route::any('*',function(){
    return json(['code'=>404,'msg'=>'not found'],404);
});
