<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use think\Collection;
use app\common\library\Jpush;
use app\common\model\UserManagers,
    app\common\model\NbAuthDept;
//    app\common\model\Dmmcs;
use app\common\model\SystemMessages as SystemMessageModel,
    app\common\model\SystemMessageRead;

class User extends Common{

    public function index(Request $request){

        $messageCount = SystemMessageModel::getClientMessage(
            $request->User->ID,
            self::MANAGE_TAG,
            [self::MANAGE_TAG,self::ALL_TAG]
        )->count();

        $readCount = SystemMessageRead::where('CLIENT_TAG',self::MANAGE_TAG)
            ->where('CLIENT_UID',$request->User->ID)
            ->where('ISREAD',1)
            ->count();


        return $this->ok('ok',[
            'unread_message_count'=>$messageCount - $readCount,
            'info'=>[
                'ID'=>$request->User->ID,
                'UCODE'=>$request->User->UCODE,
                'NAME'=>$request->User->NAME,
                'MOBILE'=>$request->User->MOBILE,
                'ID_NUMBER'=>$request->User->ID_NUMBER,
                'UNIT_NAME'=>$request->User->UNIT_NAME,
                'UNIT_ADDRESS'=>$request->User->UNIT_ADDRESS,
                'LIVE_ADDRESS'=>$request->User->ADDRESS,
                'HEAD_IMG_URL'=>build_http_img_url($request->User->HEAD_IMG),
                'GENDER_TEXT'=>$request->User->gender_text,
                'NATIONALITY'=>'中国',
                'QQ'=>$request->User->QQ,
                'WECHAT'=>$request->User->WECHAT,
                'REMARK'=>$request->User->MARK,
                'IS_WORK'=>$request->User->STATUS,
                'IS_WORK_TEXT'=>$request->User->STATUS==1 ? '有效' : '无效',
            ],
            'push_alias'=>Jpush::createManageAlias($request->UUID),
        ]);
    }

    public function index_bak(Request $request){
        $request->User->HEAD_IMG_URL = build_http_img_url($request->User->HEAD_IMG);
        $request->User->GENDER_TEXT = $request->User->gender_text;
        return $this->ok('ok',[
            'info'=>$request->User
        ]);
    }


    public function update(Request $request){

        //可修改现住址,单位名称,单位地址,手机号码,QQ号码,微信号码
        $allow_fields = [
            'HEAD_IMG','MOBILE','QQ','WECHAT','DMM_ID','UNIT_ADDRESS','ADDRESS','PWSD'
        ];

        //$user = UserManagers::find($request->User->ID);
        $update = [];

        Collection::make($request->post())->each(function($t,$k) use ($allow_fields,&$update){
            if(in_array($k,$allow_fields) && $t){
                $update[$k] = $t;
            }
        });
        $res = $this->uploadImages($request,['manager/']);

        if($res && isset($res['images'])){
            $update['HEAD_IMG'] = $res['images'][0];
        }

        if(isset($update['MOBILE'])){
            $exist = UserManagers::where('ISDEL',0)
                ->where('ID','neq',$request->User->ID)
                ->where('MOBILE',$update['MOBILE'])
                ->count();
            if($exist){
                return $this->fail('该手机号已经存在，请换一个');
            }
        }

        if(isset($update['DMM_ID'])){
            $dmmc = NbAuthDept::find($update['DMM_ID']);
            if(!$dmmc){
                return $this->fail('该单位不存在');
            }
//            $dmmc_ids = [];
            $update['UNIT_NAME'] = $dmmc->DEPTNAME;
            /*$pdmmc = Dmmcs::where('DM',$dmmc->PDM)->find();
            if($pdmmc){

            }*/
        }

        if(isset($update['PWSD'])){
            $stat = \think\helper\Str::random(6);
            $update['PWSD'] = create_pwd($update['PWSD'],$stat);
            $update['SALT'] = $stat;
        }

        if(!empty($update)){

            UserManagers::where('ID',$request->User->ID)->update($update);
        }

        return $this->ok('ok',[
            //'update'=>$update
        ]);
    }
}