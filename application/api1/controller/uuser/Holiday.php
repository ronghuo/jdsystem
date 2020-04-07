<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use app\common\model\UserHolidayApplies,
    app\common\model\UserHolidayApplyLists,
    app\common\model\UserHolidayApplyListImgs;
use app\common\validate\UserHolidayApplyListsVer;
use app\common\library\Mylog;
use think\Request;
use Carbon\Carbon;

class Holiday extends Common{


    public function index(Request $request){
        $page = $request->param('page',1,'int');

        $list = UserHolidayApplyLists::where('UUID','=',$request->UUID)
            ->where('STATUS','>=',0)
            ->order(['ADD_TIME'=>'DESC'])
            ->page($page,self::PAGE_SIZE)
            ->select();

        if(empty($list)){
            return $this->ok('',[
                'list'=>[]
            ]);

        }
//        echo count($list);
        $data = [];
        foreach($list as $k=>$v){
            //todo 判断是否超期未报道
//            if(Carbon::parse($v['BACK_TIME'])->isPast() && $v->STATUS != 5){
//                UserHolidayApplyLists::setTimeOut($v->ID,$v->UHA_ID);
//            }

            $v->HEAD_IMG_URL = build_http_img_url($request->User->HEAD_IMG);
            $v['STATUS_TEXT'] = $v->status_text;
            $v['DAYS'] = (new Carbon($v['OUT_TIME']))->diffInDays(new Carbon($v['BACK_TIME']));
            $v['OUT_TIME'] = $v['OUT_TIME'].' '.$v['OUT_TIME_AT'];
            $v['BACK_TIME'] = $v['BACK_TIME'].' '.$v['BACK_TIME_AT'];

            $v->IMGS->map(function($t){
                return $t->IMG_URL = build_http_img_url($t->SRC_PATH);
            });
            $v->CTEXT = '';

            $data[$v['UHA_ID']][] = $v;
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
    //撤回申请，status=-1
    public function cancel(Request $request){
        $id = $request->param('ID',0,'int');
        $this->log([
            'cancel',
            $request->post()
        ],'holiday');
        if(!$id){
            return $this->fail('参数有误');
        }

        $info = UserHolidayApplyLists::find($id);
        if(!$info){
            return $this->fail('撤回失败');
        }

        if($info['STATUS']==-1){
            return $this->ok('撤回申请成功');
        }

        if($info['STATUS'] == 0){

            $info->STATUS = -1;
            $info->CANCEL_TIME = \Carbon\Carbon::now()->toDateTimeString();
            $res = $info->save();
            //
            UserHolidayApplies::where('ID','=',$info->UHA_ID)->update(['STATUS'=>-1]);
            return $this->ok('撤回申请成功',['result'=>$res]);
        }


        return $this->fail('该状态【'.$info->status_text.'】不能撤回申请');

    }
    //申请或重新申请
    public function save(Request $request){
        $uha_id = $request->param('UHA_ID',0,'int');
        //echo $uha_id;exit;
        if(!$uha_id){
            $uha_id = (new UserHolidayApplies())->insertGetId([
                'UUID'=>$request->UUID
            ]);
        }

        if(!$uha_id){
            return $this->fail('提交失败，请稍后再试');
        }

        Mylog::write([
                $request->post(),
                $request->header()
        ]
            ,'holiday');


        /**
         * (
        [MOBILE] => 13355554444
        [OUT_TIME] => 上午
        [OUT_TIME_AT,] => 2019-05-15
        [BACK_TIME] => 晚上
        [BACK_TIME_AT] => 2019-05-18
        [REASON] => 回家吃酒
        )
         */
        /*if($request->header('device-system') == 'android'){
            $data = [
                'UHA_ID'=>$uha_id,
                'UUID'=>$request->UUID,
                'UNAME'=>$request->User->NAME,
                'MOBILE'=>$request->param('MOBILE','','trim'),
                'OUT_TIME'=>$request->param('OUT_TIME_AT,','','trim'),
                'OUT_TIME_AT'=>$request->param('OUT_TIME','','trim'),
                'BACK_TIME'=>$request->param('BACK_TIME_AT','','trim'),
                'BACK_TIME_AT'=>$request->param('BACK_TIME','','trim'),
                'REASON'=>$request->param('REASON','','trim'),
            ];
        }else{
            $data = [
                'UHA_ID'=>$uha_id,
                'UUID'=>$request->UUID,
                'UNAME'=>$request->User->NAME,
                'MOBILE'=>$request->param('MOBILE','','trim'),
                'OUT_TIME'=>$request->param('OUT_TIME','','trim'),
                'OUT_TIME_AT'=>$request->param('OUT_TIME_AT','','trim'),
                'BACK_TIME'=>$request->param('BACK_TIME','','trim'),
                'BACK_TIME_AT'=>$request->param('BACK_TIME_AT','','trim'),
                'REASON'=>$request->param('REASON','','trim'),
            ];
        }*/

        $data = [
            'UHA_ID'=>$uha_id,
            'UUID'=>$request->UUID,
            'UNAME'=>$request->User->NAME,
            'MOBILE'=>$request->param('MOBILE','','trim'),
            'OUT_TIME'=>$request->param('OUT_TIME','','trim'),
            'OUT_TIME_AT'=>$request->param('OUT_TIME_AT','','trim'),
            'BACK_TIME'=>$request->param('BACK_TIME','','trim'),
            'BACK_TIME_AT'=>$request->param('BACK_TIME_AT','','trim'),
            'REASON'=>$request->param('REASON','','trim'),
        ];


        $v = new UserHolidayApplyListsVer();

        if(!$v->check($data)){
            return $this->fail($v->getError());
        }

        $uhal_id = (new UserHolidayApplyLists())->insertGetId($data);
        if(!$uha_id){
            return $this->fail('提交失败，请稍后再试');
        }

        $this->saveHolidayImages($request,$uhal_id);

        // 通知相关管理人员
        \think\Queue::later(2,'\app\common\job\PushToManager',
            [
                'uuid'=>$request->UUID,
                'message'=> '有新的请假申请',
                'metas'=>['url'=>'jd://com.aysd.jd/type=2&id='.$uha_id],
            ]);


        return $this->ok('提交申请成功');
    }
    //销假报道 status=4
    public function complete(Request $request)
    {
        $id = $request->param('ID', 0, 'int');

        if (!$id) {
            return $this->fail('参数有误');
        }

        $info = UserHolidayApplyLists::find($id);
        if (!$info) {
            return $this->fail('销假报道失败');
        }

        if ($info['STATUS'] == 4) {
            return $this->ok('销假报道成功');
        }

        if (in_array($info['STATUS'], [1])) {

            $info->STATUS = 4;
            $info->COMPLETE_TIME = \Carbon\Carbon::now()->toDateTimeString();
            $res = $info->save();
            //
            UserHolidayApplies::where('ID','=',$info->UHA_ID)->update(['STATUS'=>4]);

            return $this->ok('销假报道成功', ['result' => $res]);
        }
        return $this->fail('该状态【' . $info->status_text . '】不能销假');

    }
    //续假status=3
    public function addMore(Request $request){
        $id = $request->param('ID',0,'int');
        $this->log([
            'addMore',
            $request->post()
        ],'holiday');
        if(!$id){
            return $this->fail('参数有误');
        }

        $info = UserHolidayApplyLists::find($id);
        if(!$info){
            return $this->fail('续假失败');
        }

        //只有申请被同意状态（且假期没结束），才有续假
        if($info['STATUS']!=1){
            return $this->fail('只有申请被同意才能续假');
        }
        $dt = \Carbon\Carbon::now();
        $diff = $dt->diffInDays(new \Carbon\Carbon($info['BACK_TIME']));
        if($diff<0){
            return $this->fail('假期已结束不能续假');
        }
        //echo $diff;exit;

//        $info->STATUS = 3;
        $info->CONTINUE_TIME = \Carbon\Carbon::now()->toDateTimeString();
        $res = $info->save();
//        if(!$res){
//            return $this->fail('续假失败');
//        }
        UserHolidayApplies::where('ID','=',$info->UHA_ID)->update(['STATUS'=>3]);
        $request->UHA_ID = $info->UHA_ID;
        return $this->save($request);
    }

    protected function saveHolidayImages(Request $request,$uhal_id){
        $res = $this->uploadImages($request,['holiday/']);

        if(!$res){
            return false;
        }
        return (new UserHolidayApplyListImgs())->saveData($uhal_id,$res['images']);
//        return $this->saveHolidayImages2DB($uhal_id,$res['images']);
    }

}