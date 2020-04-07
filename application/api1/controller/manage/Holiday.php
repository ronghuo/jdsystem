<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use \Carbon\Carbon;
use app\common\library\Jpush;
use app\common\model\UserUsers,
    app\common\model\UserHolidayApplies,
    app\common\model\UserHolidayApplyLists,
    app\common\model\UserHolidayApplyListImgs;

class Holiday extends Common{

    public function index(Request $request,$status){
        $page = $request->param('page',1,'int');
        $status_maps = [
            'wait'=>[0],
            'continue'=>[3],
            'cancel'=>[4,6,7],
            'timeout'=>[5]
        ];
        if(!isset($status_maps[$status])){
            $status = 'wait';
        }
        // 加上当前人员的管辖范围条件
        $ids = UserHolidayApplies::where('STATUS','in',$status_maps[$status])
            ->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if($request->User->isXCPower){
                    $query->whereIn('UUID',$this->getManageUserIds($request->MUID));
                }
            })
            //->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->page($page,self::PAGE_SIZE)
            ->select()
            ->column('ID');

        if(empty($ids)){
            return $this->ok('',[
                'list'=>[]
            ]);
        }

        $list = UserHolidayApplyLists::where('UHA_ID','in',$ids)
            //->where('UUID','in',$request->UUserids)
            //->where('STATUS','in',$status_maps[$status])
            ->order(['ADD_TIME'=>'DESC'])->select();


        $data = [];
        foreach($list as $k=>$v){
            //todo 判断是否超期未报道
//            if(Carbon::parse($v['BACK_TIME'])->isPast() && $v->STATUS != 5){
//                UserHolidayApplyLists::setTimeOut($v->ID,$v->UHA_ID);
//            }

            $v->HEAD_IMG_URL = '';
            $user = UserUsers::find($v->UUID);
            if($user){
                $v->HEAD_IMG_URL = build_http_img_url($user->HEAD_IMG);
            }
           //
            $v['STATUS_TEXT'] = $v->status_text;
            $v['DAYS'] = (new Carbon($v['OUT_TIME']))->diffInDays(new Carbon($v['BACK_TIME']));
            $v['OUT_TIME'] = $v['OUT_TIME'].' '.$v['OUT_TIME_AT'];
            $v['BACK_TIME'] = $v['BACK_TIME'].' '.$v['BACK_TIME_AT'];
//            $v['IMGS'] = $v->imgs;
            $v->IMGS->map(function($t){
                return $t->IMG_URL = build_http_img_url($t->SRC_PATH);
            });
            $v->CTEXT = '';

            $data[$v['UHA_ID']][] = $v->toArray();
        }

        $continues =  UserHolidayApplies::where('STATUS','=',3)->select();
        foreach($continues as $c){
            if(!isset($data[$c->ID])){
                continue;
            }
            $len = count($data[$c->ID]);
            if($len<=0){
                continue;
            }
            for($i=0;$i<$len-1;$i++){
                $data[$c->ID][$i]['CTEXT'] = '续假';
            }
        }

        return $this->ok('',[
            'list'=>array_values($data)
        ]);
    }
    public function info(Request $request){

    }
    public function check(Request $request){

        $id = $request->param('ID',0,'int');
        $result = $request->param('RESULTID',0,'int');
        $mark = $request->param('MARK','','trim');

        if(!$id || !in_array($result,[1,2])){
            return $this->fail('参数有误');
        }
        // 加上当前人员的管辖范围条件
        $info = UserHolidayApplyLists::where('UUID','in',$this->getManageUserIds($request->MUID))
            ->find($id);

        if(!$info){
            return $this->fail('请假信息有误');
        }
        $pinfo = UserHolidayApplies::where('ISDEL','=',0)->find($info->UHA_ID);

        if(!$pinfo){
            return $this->fail('请假信息有误');
        }

        $check_status_map = [
            //同意
            1=>[
                //现状态=>更新的状态
                0=>1,
//                3=>1,
                4=>6
            ],
            //拒绝
            2=>[
                0=>2,
//                3=>2,
                4=>7
            ]

        ];
        if(!isset($check_status_map[$result][$info->STATUS])){
            return $this->fail('审批操作失败');
        }

        $info->STATUS = $check_status_map[$result][$info->STATUS];
        $info->CHECKER_UID = $request->MUID;
        $info->CHECKER_NAME = $request->User->NAME;
        $info->CHECK_TIME = Carbon::now()->toDateTimeString();
        $info->CHECK_MARK = $mark;

        if($info->save()){
            if($pinfo->STATUS!=3){
                $pinfo->STATUS = $check_status_map[$result][$pinfo->STATUS];
                $pinfo->save();
            }
            // 推送
            //Jpush::sendToUser($info->UUID,'',['url'])
            \think\Queue::later(2,'\app\common\job\Jpush',
                [
                    'type'=>'u',
                    'user_id'=>$info->UUID,
                    'message'=> $result==1? '你的请假已审核通过了' : '你的请假没有通过',
                    'metas'=>['url'=>'jd://com.aysd.jd/type=3&id='.$info->UHA_ID],
                ]);

            return $this->ok('审批操作成功');
        }
        return $this->fail('审批操作失败');
    }
}