<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\http\middleware;


use app\common\model\UserManagerPower;
use app\common\model\UserManagers;
use Firebase\JWT\JWT;
use think\Request;

class Api1ManageAuth{

    // 顶级权限
    const TOP_LEVEL_POWER = 1;

    public function handle(Request $request, \Closure $next)
    {
        $authorization = $request->header('Authorization','');
        if(!$authorization){
            return json(['code'=>'401','msg'=>'禁止访问'],401);
        }
        //echo $authorization;
        // check user token
        try{
            $decode = JWT::decode(
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

        $isTopPower = UserManagerPower::where('UMID', $user->ID)->where('LEVEL', self::TOP_LEVEL_POWER)->count();
        $user->isTopPower = $isTopPower > 0;

        $request->User = $user;
        return $next($request);
    }
}