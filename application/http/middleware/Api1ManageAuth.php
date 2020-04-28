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

    // 顶级权限
    const TOP_LEVEL_POWER = 1;

    public function handle(Request $request, \Closure $next)
    {
        /*$authorization = $request->header('Authorization','');
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
        }*/

        /*if(time() - $decode->iat > config('app.jwt_token_expiry')){

            return json(['code'=>'401','msg'=>'禁止访问..'],401);
        }*/

//        $request->MUID = $decode->user_id;
//        $user = UserManagers::where('ISDEL', 0)->find($decode->user_id);
        $request->MUID = 11;
        $user = UserManagers::where('ISDEL', 0)->find(11);

        if(!$user){
            return json(['code'=>'401','msg'=>'.禁止访问.'],401);
        }

        $isTopPower = UserManagerPower::where('UMID', $user->ID)->where('LEVEL', self::TOP_LEVEL_POWER)->count();
        $user->isTopPower = $isTopPower > 0;

        $isXC = UserManagerPower::where('UMID', $user->ID)->whereIn('LEVEL', '3,4')->count();
        $user->isXCPower = $isXC > 0 ? true : false;

        $request->User = $user;
        //print_r($request);exit;
        return $next($request);
    }
}