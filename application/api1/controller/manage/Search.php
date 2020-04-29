<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use app\common\model\UserUsers;
use think\Collection;
use Carbon\Carbon;
use app\common\model\BaseUserDangerLevel,
    app\common\model\BaseUserStatus,
    app\common\model\Subareas;

class Search extends Common{


    public function uuserByAreas(Request $request){

        $COMMUNITY_ID = $request->param('COMMUNITY_ID',0,'int');
        if(!$COMMUNITY_ID){
            $this->fail('缺少社区信息');
        }
        // 加上当前人员的管辖范围条件
        $list =UserUsers::field('ID,NAME,HEAD_IMG')
            ->where('COMMUNITY_ID','=',$COMMUNITY_ID)
            ->where('STATUS','=',1)
            ->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if (!$request->User->isTopPower) {
                    $query->whereIn('ID', $this->getManageUserIds($request->MUID));
                }
            })
            //->whereIn('ID',$this->getManageUserIds($request->MUID))
            ->select()->map(function($t){
                $t->HEAD_IMG_URL = build_http_img_url($t->HEAD_IMG);
                return $t;
            });

        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    public function uuserByName(Request $request){
        $name = $request->param('NAME','','trim');
        if(!$name){
            return $this->fail('请输入要搜索的姓名');
        }
        // 加上当前人员的管辖范围条件
        $list = UserUsers::field(['ID','UUCODE','NAME','ALIAS_NAME','GENDER',
            'DOMICILE_PLACE','MANAGE_POLICE_AREA_NAME','HEAD_IMG'])
            ->where('NAME','like','%'.$name.'%')
            ->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if (!$request->User->isTopPower) {
                    $query->whereIn('ID',$this->getManageUserIds($request->MUID));
                }
            })
//            ->whereIn('ID',$this->getManageUserIds($request->MUID))
            ->select()->map(function($t){
                $t->GENDER_TEXT = $t->gender_text;
                $t->HEAD_IMG_URL = build_http_img_url($t->HEAD_IMG);
                return $t;
            });


        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    public function uuserInfo(Request $request){
        $id = $request->param('id',0,'int');
        if(!$id){
            return $this->fail('缺少参数');
        }
//        if(!in_array($id,$request->UUserids)){
//            return $this->fail('无权操作');
//        }
        // 加上当前人员的管辖范围条件
        $info = UserUsers::field('PWSD,SALT,ISDEL,DEL_TIME', true)->where('ISDEL','=',0)
            //->whereIn('ID',$this->getManageUserIds($request->MUID))
            ->find($id);

        $agreement_url = '';
        if($info){


            $info->GENDER_TEXT = $info->gender_text;
            $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);


            $userStatus = BaseUserStatus::find($info->USER_STATUS_ID);
            $info->USER_STATUS = $userStatus ? $userStatus->NAME : '';
            $dangerLevel = BaseUserDangerLevel::find($info->DANGER_LEVEL_ID);
            $info->DANGER_LEVEL = $dangerLevel ? $dangerLevel->NAME : '';
            
//            $info->JD_START_TIME = '康复起始时间';
//            $info->JD_END_TIME = '康复截止时间';
            $info->JD_REST_TIME = '-';
            if($info->JD_START_TIME && $info->JD_END_TIME){

                $end = Carbon::parse($info->JD_END_TIME);
                $info->JD_REST_TIME = $end->diffInDays($info->JD_START_TIME);
            }

            $uran = $info->uranCheck();
            $info->JD_FINISH_URAN_COUNT = $uran['finish_count'];
            $info->JD_MIN_REST_URAN_COUNT = $uran['rest_count'];
            $info->JD_NEXT_URAN_TIME = $uran['next_uran_time'];


            $info->JD_STREET = '-';
            if($info->STREET_ID > 0){
                $street = Subareas::where('CODE12', $info->STREET_ID)->find();
                $info->JD_STREET = $street ? $street->NAME : '-';
            }

//            $info->JD_STREET = '所属乡镇街道';
//            $info->JD_ZHUANGAN = '负责专干';
//            $info->JD_ZHUANGAN_MOBILE = '负责专干 电话';
            //协议链接
            $agreement_url = get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>self::MANAGE_TAG,'type'=>4,'id'=>$request->UUID]);
        }

        return $this->ok('ok',[
            'info'=>$info ? $info->toArray() : new \stdClass(),
            'agreement_url'=>$agreement_url
        ]);
    }

    public function updateUuser(Request $request){
        $id = $request->param('id',0,'int');
        if(!$id){
            return $this->fail('缺少参数');
        }
        $allow_fields = [
            'MOBILE'
        ];
        $update = [];

        $info = UserUsers::where('ISDEL','=',0)
            ->whereIn('ID',$this->getManageUserIds($request->MUID))
            ->find($id);
        if($info){


            Collection::make($request->post())->each(function($t,$k) use ($allow_fields,&$update){
                if(in_array($k,$allow_fields) && $t){
                    $update[$k] = $t;
                }
            });


            if(isset($update['MOBILE'])){
                $exist = UserUsers::where('ISDEL',0)
                    ->where('ID','neq',$info->ID)
                    ->where('MOBILE',$update['MOBILE'])
                    ->count();
                if($exist){
                    return $this->fail('该手机号已经存在，请换一个');
                }
            }

            if(!empty($update)){

                UserUsers::where('ID',$info->ID)->update($update);
            }
        }



        return $this->ok('ok',[
            //'update'=>$update
        ]);
    }

}