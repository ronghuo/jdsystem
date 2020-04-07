<?php

namespace app\http\middleware;

use think\Request;
use think\exception\HttpResponseException;
use think\Response;

class SystemAuth
{
    protected $skip_controllers = [
        'login','helper','ueditor'
    ];

    public function handle(Request $request, \Closure $next)
    {
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());

        if(in_array($controller,$this->skip_controllers)){
            return $next($request);
        }

        $user_id = session('user_id');
        //echo $user_id;
        if(!$user_id){
            return redirect(url('htsystem/Login/index'));
        }

        if(session('superadmin') === true){
            return $next($request);
        }
        // 检查对当前操作是否有权限

        $acc_key = strtolower($controller.'_'.$action);

        if(in_array($acc_key,config('app.rbac.no_auth_node'))){
            return $next($request);
        }

        return $next($request);

        $acc = session('_ACCESS_LIST.'.$acc_key);

        if(!$acc || empty($acc)){
//        if(false){
            if($request->isAjax()){
                $result = ['err'=>1,'mesg'=>'权限不足'];
                $response = Response::create($result, 'json');
                throw new HttpResponseException($response);
            }else{
                $result = [
                    'code' => 0,
                    'msg'  => '权限不足',
                    'url'  => 'javascript:history.back(-1);'
                ];
                $response = Response::create($result, 'jump')->options(['jump_template' => config('app.dispatch_error_tmpl')]);

                throw new HttpResponseException($response);
            }
        }

        return $next($request);
    }
}
