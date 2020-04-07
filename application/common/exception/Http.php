<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/19
 */
namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use app\common\library\Mylog;
use app\common\library\EmailHelper;

class Http extends Handle
{
    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json([
                'code'=>'400',
                'msg'=>$e->getError()
            ], 422);
        }

        // 请求异常
//        if ($e instanceof HttpException && request()->isAjax()) {
//            return response($e->getMessage(), $e->getStatusCode());
//        }
        $traces = [
            'message'=>$e->getMessage(),
            'fileline'=>$e->getFile().'='.$e->getLine(),
            'traces'=>$e->getTraceAsString(),
            'servers'=>$_SERVER,
            'post'=>$_POST
        ];

        Mylog::write($traces,'exception');
        if(strpos($_SERVER['HTTP_HOST'], '220.169.110.109') !== false){
            EmailHelper::sendErrorMessage($traces);
        }


        return json([
            'code'=>'500',//(string)$e->getStatusCode(),
            'msg'=>'Something Went Wrong.',
            'traces'=>config('app.app_debug') ? $traces : ''
        ],500);
    }

}