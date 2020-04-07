<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\api1\controller\manage;



use app\api1\controller\Common;
use app\common\model\UserManagerPower;
use app\common\model\UserUsers;
use think\Request;

class Test extends Common{


    public function power(){
        $ids = $this->getManageUserIds(1);

        return $ids;
    }

    public function t1(){
        $dmr = new \app\common\model\DrugMessageReports();

        $DMR_CODE = $dmr->createNewDMRCode(431202);
        echo $DMR_CODE;
    }

    public function jwt12(){

        $uid = input('get.uid', 1);
        $payload = [
            'user_id'=>$uid,
            'iat'=>time()
        ];

        $key = config('app.jwt_api_muser_key');

        $token = \Firebase\JWT\JWT::encode($payload,$key);

//        $decode = \Firebase\JWT\JWT::decode($token,$key,config('app.jwt_api_algorithm'));

        return [
//            'payload'=>$payload,
            'token'=>$token,
//            'decode'=>$decode
        ];

    }

    public function answer(){
        $data = [
            'ASKID'=>1,
            'ANSWERER_UID'=>2,
            'ANSWERER_NAME'=>'张三',
            'CONTENT'=>'有人引诱那些控制力差的人'
        ];

        (new \app\common\model\MessageBoardAnswers())->insert($data);
        return $data;
    }

    public function addMuser(){

        $mu = new \app\common\model\UserManagers();
        $dmmid = 355;
        $code = $mu->createNewUCode($dmmid);
        //echo $code;exit;
        $data = [
            'UCODE'=>$code,
            'MOBILE'=>'17769282696',
            'NAME'=>'贺春伶',
            'GENDER'=>1,
            'ID_NUMBER'=>'431200000000000',
            'JOB'=>'禁毒专干',
            'PROVINCE_ID'=>43,
            'CITY_ID'=>4312,
            'COUNTY_ID'=>431227,
            'ADDRESS'=>'新晃县 桥南社区',
            'DMM_ID'=>$dmmid,
            'UNIT_NAME'=>'新晃县禁毒办',
            'SPECIAL_ABILITY'=>'test',
        ];

//        return $data;
        $v = new \app\common\validate\UserManagersVer();
        if(!$v->scene('add')->check($data)){
            return [
                'msg'=>$v->getError(),
                'data'=>$data
            ];
        }
        $mu->save($data);
        return $data;
    }

    public function addUran(Request $request){

        $ur = new \app\common\model\Urans();

        $code = $ur->createNewUUCode();

        $data = [
            'URAN_CODE'=>$code,
            'UUID'=> 1,//$request->UUID,
            'CHECK_TIME'=> \Carbon\Carbon::now()->toDateTimeString(),
            'PROVINCE_ID'=>20,
            'CITY_ID'=>342,
            'COUNTY_ID'=>2422,
            'STREET_ID'=>0,
            'COMMUNITY_ID'=>0,
            'ADDRESS'=>'XXXXX医院',
            'UMID'=>2,
            'UNIT_NAME'=>'XXX登记单位',
            'RESULT'=>'阴性',
        ];
        return $data;
        $v = new \app\common\validate\UransVer();
        if(!$v->check($data)){
            return [
                'code'=>400,
                'msg'=>$v->getError()
            ];
        }

        $res = $ur->insert($data);

        return [
            'code'=>200,
            'msg'=>'ok',
            'res'=>$res
        ];
    }

    public function addNew(){
        $data = [
            'CLIENT_TAG'=>2,
            'CATE_ID'=>3,
            'POSTER_UID'=>1,
            'TITLE'=>'每天就知道屏幕上看新闻图片的人，智商真得会因此而降低吗',
            'COVER_IMG'=>'http://www.jd.local/uploads/holiday/20190324/2ffa03e641b4b2771d0619c74bb76ccf.jpg',
            'CONTENT'=>'事实上，一种有趣生活的取得，并不在于你究竟拥有外在多少东西，而看你的思维是否丰盈与鲜活。没有这一点作为保证，不论一个人的名气有多大，身上的光环有多耀眼，当潮水退去后，显出原形的他一样是具只能裸泳的臭行囊。'
        ];

        (new \app\common\model\Articles())->insert($data);

    }
}