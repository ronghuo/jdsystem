<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\model\WaitDeleteFiles;
use app\common\validate\UransVer;
use Carbon\Carbon;
use think\Request;
use app\common\model\Urans,
    app\common\model\UranImgs,
//    app\common\model\Dmmcs,
    app\common\model\NbAuthDept,
    app\common\model\UserUsers;

class Uran extends Common {


    public function index(Request $request) {

        $page = $request->param('page',1,'int');
        $user_id = $request->param('userid',0,'int');
        $uran_id = $request->param('id', 0, 'int');


        // 加上当前人员的管辖范围条件
        $list = Urans::
            with([
                'dmmc'=>function($query){
                    $query->field('ID,DEPTCODE as DM,DEPTNAME as DMMC');
                },
                'uuser'=>function($query){
                    $query->field('ID,UUCODE,NAME,ID_NUMBER');
                },
                'muser'=>function($query){
                    $query->field('ID,NAME,UCODE');
                }
            ])
            ->where('ISDEL','=',0)
            ->where(function($st) use($user_id, $uran_id, $request) {
                if (!$request->User->isTopPower) {
                    $st->whereIn('UUID', $this->getManageUserIds($request->MUID));
                }
                if ($user_id > 0) {
                    $st->where('UUID', '=', $user_id);
                }
                if ($uran_id > 0) {
                    $st->where('ID', '=', $uran_id);
                }
            })
            ->order('CHECK_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select()->map(function($t){
                //$t->imgs;
                $t->imgs->map(function($tt){
                    $tt->IMG_URL = build_http_img_url($tt->SRC_PATH);
                    return $tt;
                });
                if(!$t->dmmc){
                    $t->dmmc = new \stdClass();
                }

                return $t;
            });


        return $this->ok('',[
            'list'=>!empty($list) ? $list->toArray() : []
        ]);
    }

    // 尿检提醒列表
    public function notify(Request $request) {
        $datetime = $request->param('datetime','');

        if(!$datetime){
            $time = Carbon::now();
        }else{
            $time = Carbon::parse($datetime);
        }

        $users = UserUsers::field('ID,NAME,HEAD_IMG,UTYPE_ID,UTYPE_ID_218')
            ->where('JD_ZHI_PAI_ID', '<', 2)//排除解除社区康复/戒毒
            ->where(function($query)use($request){
                if (!$request->User->isTopPower) {
                    $query->whereIn('ID',$this->getManageUserIds($request->MUID));
                }
            })
            //->whereIn('ID',$this->getManageUserIds($request->MUID))
//            ->whereIn('ID',[1,2])
            ->select()->map(function($st) use($time){
                $st->HEAD_IMG_URL = build_http_img_url($st->HEAD_IMG);

                $urancheck = $st->uranCheck();

                $st->URAN_RATE = $urancheck['rate'];
                $st->URAN_COUNT = $urancheck['finish_count'];
                $st->IS_COMPLETED = $urancheck['is_completed'];
                $st->URAN_RATE = $urancheck['rate'];
                //1-1次/月，
//                if($st->URAN_RATE == 1){
//                    $count = Urans::where('ISDEL',0)
//                        ->where('UUID',$st->ID)
//                        ->where('CHECK_TIME','>=',$time->firstOfMonth()->toDateTimeString())
//                        ->where('CHECK_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString())
//                        ->count();
//                    $st->URAN_COUNT = $count;
//                    $st->IS_COMPLETED = $count>=1 ? 1 : 0;
//
//                }else{
//                    $count = Urans::where('ISDEL',0)
//                        ->where('UUID',$st->ID)
//                        ->where('CHECK_TIME','>=',$time->firstOfYear()->toDateTimeString())
//                        ->where('CHECK_TIME','<=',$time->lastOfYear()->addSeconds(24*3600 -1)->toDateTimeString())
//                        ->count();
//                    //2-2月/1次，3-3月/1次，4-2次/年
//                    $st->URAN_COUNT = $count;
//                    $rate = $time->month/12;
//                    if($st->URAN_RATE == 2){
//                        $st->IS_COMPLETED = ($count >= (int) $rate * 6) ? 1: 0;
//                    }elseif($st->URAN_RATE == 3){
//                        $st->IS_COMPLETED = ($count >= (int) $rate * 4) ? 1: 0;
//                    }elseif($st->URAN_RATE == 4){
//                        $st->IS_COMPLETED = ($count >= (int) $rate * 2) ? 1: 0;
//                    }
//                }

                return $st;
            });


        return $this->ok('',[
            'list'=>!empty($users) ? $users->toArray() : [],
            //'ids'=>$this->getManageUserIds($request->MUID)
        ]);

    }

    public function index_bak(Request $request){

        // 加上当前人员的管辖范围条件
        $list = Urans::field(['ID','UUID','CHECK_TIME','RESULT'])
            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
           ->where('ISDEL','=',0)
            ->order('CHECK_TIME','DESC')
            ->select()->map(function($t){
                $t->NAME = UserUsers::field('NAME')->find($t->UUID)->NAME;
                return $t;
            });

        return $this->ok('',[
            'list'=>!empty($list) ? $list : []
        ]);
    }

    public function save(Request $request) {
        $dmmid = $request->param('DMM_ID',0,'int');
        $dmm = NbAuthDept::find($dmmid);

        if (!$dmm) {
            return $this->fail('登记单位信息有误');
        }

        $ur = new Urans();

        $code = $ur->createNewUUCode();

        $data = [
            'URAN_CODE' => $code,
            'UUID' => $request->param('UUID','','int'),
            'CHECK_TIME' => $request->param('CHECK_TIME','','trim'),
            'PROVINCE_ID' => $request->param('PROVINCE_ID',0,'int'),
            'CITY_ID' => $request->param('CITY_ID',0,'int'),
            'COUNTY_ID' => $request->param('COUNTY_ID',0,'int'),
            'COUNTY_NAME' => $request->param('COUNTY_NAME', '', 'trim'),
            'ADDRESS' => $request->param('ADDRESS','','trim'),
            'UMID' => $request->MUID,
            'DMM_ID' => $dmm->ID,
            'UNIT_NAME' => $dmm->DEPTNAME,
            'RESULT' => $request->param('RESULT','','trim'),
            'REMARK' => $request->param('REMARK','','trim'),
            'CHECK_TYPE' => $request->param('CHECK_TYPE', 0, 'int')
        ];
        $v = new UransVer();
        if (!$v->scene("add")->check($data)) {
            return $this->fail($v->getError());
        }

        $uran_id = $ur->insertGetId($data);
        if(!$uran_id){
            return $this->fail('尿检信息保存失败');
        }

        $res = $this->uploadImages($request,['urans/']);

        if($res && isset($res['images'])){
            (new UranImgs())->saveData($uran_id,$res['images']);
        }

        return $this->ok('提交尿检信息成功');
    }

    public function edit(Request $request) {
        $dmmid = $request->param('DMM_ID',0,'int');
        $dmm = NbAuthDept::find($dmmid);
        if (!$dmm) {
            return $this->fail('登记单位信息有误');
        }

        $urineId = $request->param('ID',0,'int');
        if (empty($urineId)) {
            return $this->fail('缺少尿检记录ID');
        }
        $urine = Urans::find($urineId);
        if (empty($urine)) {
            return $this->fail('尿检记录已删除或不存在');
        }

        $data = [
            'CHECK_TIME' => $request->param('CHECK_TIME','','trim'),
            'PROVINCE_ID' => $request->param('PROVINCE_ID',0,'int'),
            'CITY_ID' => $request->param('CITY_ID',0,'int'),
            'COUNTY_ID' => $request->param('COUNTY_ID',0,'int'),
            'COUNTY_NAME' => $request->param('COUNTY_NAME', '', 'trim'),
            'ADDRESS' => $request->param('ADDRESS','','trim'),
            'UMID' => $request->MUID,
            'DMM_ID' => $dmm->ID,
            'UNIT_NAME' => $dmm->DEPTNAME,
            'RESULT' => $request->param('RESULT','','trim'),
            'REMARK' => $request->param('REMARK','','trim'),
            'CHECK_TYPE' => $request->param('CHECK_TYPE', 0, 'int')
        ];
        $v = new UransVer();
        if (!$v->scene("edit")->check($data)) {
            return $this->fail($v->getError());
        }

        $urine->save($data);

        $res = $this->uploadImages($request, ['urans/']);
        if ($res && isset($res['images'])) {
            (new UranImgs())->saveData($urineId, $res['images']);
        }

        return $this->ok('提交尿检信息成功');
    }

    public function deletePicture(Request $request) {
        $id = $request->param('PICTURE_ID', 0, 'int');
        if (empty($id)) {
            return $this->fail('尿检图片不存在');
        }
        $img = UranImgs::find($id);
        if (empty($img)) {
            return $this->fail('尿检图片不存在');
        }
        WaitDeleteFiles::addOne([
            'table' => 'urans',
            'id' => $img->URAN_ID,
            'path' => $img->SRC_PATH
        ]);
        $img->delete();
        return $this->ok('尿检图片删除成功');
    }

}