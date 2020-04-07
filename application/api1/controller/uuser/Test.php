<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\uuser;
use app\api1\controller\Common;
use think\Request;

class Test extends Common{

//    protected $middleware = ['Api1Auth'];


    public function dd(Request $request){
        $hday = controller('app\\api1\\controller\\uuser\\Holiday');
        $hday->uploadImages($request,1);
        return '';
    }

    public function addUser(){
//        $uucode = build_order_no('UC');
        $user = new \app\common\model\UserUsers();
        $uucode = $user->createNewUUCode();


        $data = [
            'UUCODE'=>$uucode,
            'NAME'=>'测试男',
            'ALIAS_NAME'=>'TestMan',
            'GENDER'=>'1',
            'NATIONALITY'=>'中国',
            'NATION_ID'=>1,
            'NATION'=>'汉族',
            'ID_NUMBER'=>'1234567898765432',
            'HEIGHT'=>'175',
            'EDUCATION_ID'=>3,
            'EDUCATION'=>'大专',
            'JOB_STATUS_ID'=>3,
            'JOB_STATUS'=>'已就业',
            'MARITAL_STATUS_ID'=>3,
            'MARITAL_STATUS'=>'离异',
            'JOB_UNIT'=>'XXXXX有限公司',
            'MOBILE'=>'13344445555',
            'DOMICILE_PLACE'=>'原藉XXX',
            'DOMICILE_ADDRESS'=>'原藉地址XXX',
            'DOMICILE_POLICE_STATION'=>'XXXXX派出所',
            'DOMICILE_POLICE_STATION_CODE'=>'XP000002',
            'LIVE_PLACE'=>'现居YYY',
            'LIVE_ADDRESS'=>'现居地址YYY',
            'LIVE_POLICE_STATION'=>'YYY派出所',
            'LIVE_POLICE_STATION_CODE'=>'YP00023',
            'DRUG_TYPE_ID'=>1,
            'DRUG_TYPE'=>'吸毒方式1',
            'NARCOTICS_TYPE_ID'=>2,
            'NARCOTICS_TYPE'=>'毒品2',
            'MANAGE_POLICE_AREA_CODE'=>'XQP02343',
            'MANAGE_POLICE_AREA_NAME'=>'QQQ警务室',
            'MANAGE_COMMUNITY'=>'QQQ社区',
            'POLICE_LIABLE_CODE'=>'MLP23901',
            'POLICE_LIABLE_NAME'=>'付责任',
            'POLICE_LIABLE_MOBILE'=>'13322221111'
        ];
        return $data;
        $v = new \app\common\validate\UserUsersVer();
        if(!$v->check($data)){
            return [
                'code'=>400,
                'msg'=>$v->getError()
            ];
        }



        $res = $user->insert($data);

        return [
            'code'=>200,
            'msg'=>'ok',
            'data'=>$res
        ];
    }

    public function rejwt(){
        $token='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJpYXQiOjE1NzU0Mjc4MTV9.wO3IWtTXsZ3VghBHC-wZbNZa9jcwUWgHuShopvgVuxA';

        $key = config('app.jwt_api_uuser_key');
        $decode = \Firebase\JWT\JWT::decode($token,$key,config('app.jwt_api_algorithm'));

        return [
            'decode'=>$decode,
            'exp'=>date('Y-m-d H:i:s', $decode->iat)
        ];
    }

    public function jwt(){

        $payload = [
            'user_id'=>1,
            'iat'=>time()
        ];

        $key = config('app.jwt_api_uuser_key');

        $token = \Firebase\JWT\JWT::encode($payload,$key);

        $decode = \Firebase\JWT\JWT::decode($token,$key,config('app.jwt_api_algorithm'));

        return [
            'payload'=>$payload,
            'token'=>$token,
            'decode'=>$decode
        ];

    }
}