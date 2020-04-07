<?php

namespace app\http\middleware;

use app\common\model\UserUsers;
use think\Request;

class Api1UUserAuth
{
    public function handle(Request $request, \Closure $next)
    {
        //print_r($request);
        $authorization = $request->header('Authorization','');
        if(!$authorization){
            return json(['code'=>'401','msg'=>'禁止访问'],401);
        }
        //echo $authorization;
        // check user token
        try{
            $decode = \Firebase\JWT\JWT::decode(
                $authorization,
                config('app.jwt_api_uuser_key'),
                config('app.jwt_api_algorithm')
            );
        }catch (\Exception $e){

            return json(['code'=>'401','msg'=>'禁止访问','data'=>$e->getMessage()],401);
        }



        if(empty($decode) || !$decode || !$decode->user_id){
            return json(['code'=>'401','msg'=>'禁止访问.'],401);
        }

        /*if(time() - $decode->iat > config('app.jwt_token_expiry')){

            return json(['code'=>'401','msg'=>'禁止访问..'],401);
        }*/

        $request->UUID = $decode->user_id;
        $user = UserUsers::field('PWSD,SALT,ISDEL,DEL_TIME', true)->where('ISDEL', 0)->find($decode->user_id);

        if(!$user){
            return json(['code'=>'401','msg'=>'.禁止访问.'],401);
        }

        if($request->isPost() &&  $user->JD_ZHI_PAI_ID == 2){
            return json(['code'=>'401','msg'=>'当前已是解除社戒社康状态'],401);
        }

        $request->User = $user;

        //print_r($request);exit;
        return $next($request);
    }
}
