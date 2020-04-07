<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\http\middleware;


use think\Request;
use app\common\model\UserManagers,
    app\common\model\UserManagerPower,
    app\common\model\UserUsers;

class Api1ManageAuth{
    public function handle(Request $request, \Closure $next)
    {
        $authorization = $request->header('Authorization','');
        if(!$authorization){
            return json(['code'=>'401','msg'=>'禁止访问'],401);
        }
        //echo $authorization;
        // check user token
        try{
            $decode = \Firebase\JWT\JWT::decode(
                $authorization,
                config('app.jwt_api_muser_key'),
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

        $request->MUID = $decode->user_id;
        $user = UserManagers::where('ISDEL', 0)->find($decode->user_id);

        if(!$user){
            return json(['code'=>'401','msg'=>'.禁止访问.'],401);
        }
        //查管辖权限级别
        $isXS = UserManagerPower::where('UMID', $user->ID)->whereIn('LEVEL', '1,2')->count();

        $user->isXSPower = false;//市，县级权限
        $user->isXCPower = false;//乡，村级权限
        if($isXS > 0){
            $user->isXSPower = true;
        }else{
            $isXC = UserManagerPower::where('UMID', $user->ID)->whereIn('LEVEL', '3,4')->count();
            $user->isXCPower = $isXC > 0 ? true : false;
        }

        $request->User = $user;
        //print_r($request);exit;
        return $next($request);
    }
}