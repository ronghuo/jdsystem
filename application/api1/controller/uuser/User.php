<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use app\common\model\UserUsers;
use think\Request;
use app\common\library\Jpush;
use app\common\library\Mylog;
use think\Collection;
use app\common\model\SystemMessages as SystemMessageModel,
    app\common\model\SystemMessageRead;
use app\common\model\BaseUserDangerLevel,
    app\common\model\BaseUserStatus,
    app\common\model\Subareas;
use Carbon\Carbon;

class User extends Common{


    public function index(Request $request){

        $request->User->HEAD_IMG_URL = build_http_img_url($request->User->HEAD_IMG);
        $request->User->GENDER_TEXT = $request->User->gender_text;

        $messageCount = SystemMessageModel::getClientMessage(
            $request->UUID,
            self::UUSER_TAG,
            [self::UUSER_TAG,self::ALL_TAG]
        )->count();

        $readCount = SystemMessageRead::where('CLIENT_TAG',self::UUSER_TAG)
            ->where('CLIENT_UID',$request->UUID)
            ->where('ISREAD',1)
            ->count();

        $userStatus = BaseUserStatus::find($request->User->USER_STATUS_ID);
        $request->User->USER_STATUS = $userStatus ? $userStatus->NAME : '';
        $dangerLevel = BaseUserDangerLevel::find($request->User->DANGER_LEVEL_ID);
        $request->User->DANGER_LEVEL = $dangerLevel ? $dangerLevel->NAME : '';

//        $request->User->JD_START_TIME = '康复起始时间';
//        $request->User->JD_END_TIME = '康复截止时间';

        $request->User->JD_REST_TIME = '-';

        if($request->User->JD_START_TIME && $request->User->JD_END_TIME){

            $end = Carbon::parse($request->User->JD_END_TIME);
            $request->User->JD_REST_TIME = $end->diffInDays($request->User->JD_START_TIME);
        }

        $uran = $request->User->uranCheck();

        $request->User->JD_FINISH_URAN_COUNT = $uran['finish_count'];
        $request->User->JD_MIN_REST_URAN_COUNT = $uran['rest_count'];
        $request->User->JD_NEXT_URAN_TIME = $uran['next_uran_time'];

        $request->User->JD_STREET = '-';
        if($request->User->STREET_ID > 0){
            $street = Subareas::where('CODE12', $request->User->STREET_ID)->find();
            $request->User->JD_STREET = $street ? $street->NAME : '-';
        }

//        $request->User->JD_ZHUANGAN = '负责专干';
//        $request->User->JD_ZHUANGAN_MOBILE = '负责专干 电话';



        return $this->ok('ok',[
            'unread_message_count'=>$messageCount - $readCount,
            'info'=>$request->User->toArray(),
            'push_alias'=>Jpush::createUserAlias($request->UUID),
            'agreement_url'=> get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>self::UUSER_TAG,'type'=>4,'id'=>$request->UUID])
        ]);

    }

    public function phoneData(Request $request){
        $content = $request->getContent();
        Mylog::write([
            'userid'=>$request->UUID,
            'data'=>$content
        ],'phone_datas');

        $content = str_replace(["\r","\r\n","\n","'"],'',$content);
        $content =  preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($content));


        //去队不可见字符
        for($i=0;$i<31;++$i){
            $content = str_replace(chr($i),"",$content);
        }
        $content = str_replace(chr(127), "", $content);
        //去bom头
        if(0 === strpos(bin2hex($content), 'efbbbf')){
            $content = substr($content, 3);
        }
        //echo $content;exit;

        $data = json_decode($content,1);

        if(!$data){

            Mylog::write([
                'userid'=>$request->UUID,
                'res'=>json_last_error(),
                'data'=>$content
            ],'phone_datas_error');

            return $this->ok('ok');
        }

        // Invalid argument supplied for foreach()
        foreach($data as $key =>$da){
            $cache_key = implode('',[
                'phone_data_',
                $key,
                ':',
                $request->UUID
            ]);
            cache($cache_key,$da,3600);

            \think\Queue::later(
                2,
                '\app\common\job\SavePhoneData',
                [
                    'cache_key'=>$cache_key,
                    'type'=>$key,
                    'uuid'=>$request->UUID
                ]
            );
        }


        return $this->ok('ok');
    }

    public function update(Request $request){
        //头像 、工作单位、 居住地 、居住地派出所名称
        $allow_fields = [
            'HEAD_IMG','JOB_UNIT','LIVE_ADDRESS','LIVE_POLICE_STATION','PWSD','MOBILE'
        ];

        $update = [];

        Collection::make($request->post())->each(function($t,$k) use ($allow_fields,&$update){
            if(in_array($k,$allow_fields) && $t){
                $update[$k] = $t;
            }
        });
        $res = $this->uploadImages($request,['userusers/']);

        if($res && isset($res['images'])){
            $update['HEAD_IMG'] = $res['images'][0];
        }

        if(isset($update['MOBILE']) && $update['MOBILE']){
            $exist = UserUsers::where('ISDEL',0)
                ->where('ID','neq',$request->User->ID)
                ->where('MOBILE',$update['MOBILE'])
                ->count();
            if($exist){
                return $this->fail('该手机号已经存在，请换一个');
            }
        }

        if(isset($update['PWSD'])){
            $stat = \think\helper\Str::random(6);
            $update['PWSD'] = create_pwd($update['PWSD'],$stat);
            $update['SALT'] = $stat;
        }

        if(!empty($update)){

            UserUsers::where('ID',$request->User->ID)->update($update);
        }

        return $this->ok('ok',[
            //'update'=>$update，
            'uploadRes'=>$res
        ]);

    }

}