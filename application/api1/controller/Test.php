<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller;

use think\Request;
use app\common\library\Ulogs;

class Test extends Common {


    public function t10(){
        $str = '';
        return $str;
    }
    public function t9(){

        \app\common\library\Credit::updateManagerCredit(1,1);

    }

    public function t8(){
        $file = './static/images/empty.png';
        echo 'data:image/png;base64,'.chunk_split(base64_encode(file_get_contents($file)));
    }

    public function t7(){
//        $name = \app\common\model\Options::instance()->getClueStatus(1);
        $name = \app\common\model\Areas::getAName(0);
        return $name;
    }

    public function t1(){

        return 'Hello xiaoHui';
    }

    public function t2(){
        echo \Carbon\Carbon::parse('2019-02-12 12:22:32')->format('Y-m-d');
    }

    public function t3(Request $request){

//        print_r($request);exit;
        Ulogs::uUser($request,1,Ulogs::UUSER_LOGIN);
    }

    public function t4(){
        $this->fail('ok',['data'=>'fsdfsd']);
    }

    public function t5(Request $request){
        $userid = $request->param('userid',8470,'int');
        $JWT_SECRET="uy4WE7xbJkag";

        return[
            'token'=>\Firebase\JWT\JWT::encode([
                'client_id'=>$userid,
                'iat'=>time()
            ],$JWT_SECRET)
        ];


    }

    public function t6(){
        $return = [
            'PROVINCE_ID'=>[43],
            'CITY_ID'=>[],
            'COUNTY_ID'=>[],
            'STREET_ID'=>[],
            'COMMUNITY_ID'=>[],
        ];
        $uuser = new \app\common\model\UserUsers();
        return $uuser->getManageUserIds($return);

    }
}