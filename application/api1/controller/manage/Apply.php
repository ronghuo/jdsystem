<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use app\common\model\UserApplies;

class Apply extends Common{


    public function index(Request $request){
        $page = $request->param('page',1,'int');
        $status = $request->param('status',0,'int');
        // 加上当前人员的管辖范围条件
        $list = UserApplies::where(function($st) use ($status){
            if($status==0){
                $st->where('STATUS','=',0);
            }else{
                $st->where('STATUS','in',[1,2]);
            }
            return $st;
        })->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if($request->User->isXCPower){
                    $query->whereIn('UUID',$this->getManageUserIds($request->MUID));
                }
            })
            //->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->order('ID','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select()
            ->map(function($t){
                $t->ADD_TIME = \Carbon\Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i');
                $t->STATUS_TEXT = $t->status_text;
                return $t;
            });


        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);

    }
    public function info(Request $request){
        // 加上当前人员的管辖范围条件
        $info = UserApplies::where(['ID'=>$request->param('id',0)])->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if($request->User->isXCPower){
                    $query->whereIn('UUID',$this->getManageUserIds($request->MUID));
                }
            })
            //->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->find();
        if($info){
            $info->STATUS_TEXT = $info->status_text;
            $info->ADD_TIME = \Carbon\Carbon::parse($info->ADD_TIME)->format('Y-m-d H:i');
            if($info->CHECK_TIME){
                $info->CHECK_TIME = \Carbon\Carbon::parse($info->CHECK_TIME)->format('Y-m-d H:i');
            }
            // 将图片，音频，视频分开来
            $medias = [];
            $info->IMGS->map(function ($t) use (&$medias){
                $t->IMG_URL = build_http_img_url($t->SRC_PATH);
                if($t->MEDIA_TYPE==1){
                    $medias['audios'][] = $t->toArray();
                }elseif($t->MEDIA_TYPE==2){
                    $medias['videos'][] = $t->toArray();
                }else{
                    $medias['images'][] = $t->toArray();
                }
                //return $t;
            });
            unset($info->IMGS);
            $info->IMGS = isset($medias['images']) ? $medias['images'] : [];
            $info->AUDIOS = isset($medias['audios']) ? $medias['audios'] : [];
            $info->VIDEOS = isset($medias['videos']) ? $medias['videos'] : [];
        }
        return $this->ok('',[
            'info'=>!$info ? new \stdClass() : $info->toArray()
        ]);

    }
    public function check(Request $request){
        $id = $request->param('ID',0);
        $check_result = $request->param('CHECK_RESULT','','trim');
        $check_mark = $request->param('CHECK_MARK','','trim');

        $results = ['ok'=>1,'fail'=>2];

        if(!$id || !isset($results[$check_result])){
            return $this->fail('参数有误');
        }
        // 加上当前人员的管辖范围条件
        $info = UserApplies::where('ISDEL','=',0)
            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->find($id);
        // [msg] => in_array() expects parameter 2 to be array, null given
        if(!$info){//|| !in_array($info->UUID,$request->UUserids)
            return $this->fail('该申请事项异常');
        }
        $info->STATUS = $results[$check_result];
        $info->CHECKER_UID = $request->MUID;
        $info->CHECKER_NAME = $request->User->NAME;
        $info->CHECK_TIME = \Carbon\Carbon::now()->toDateTimeString();
        $info->CHECK_MARK = $check_mark;
        $res = $info->save();

        if($res){
            return $this->ok('审核操作成功');
        }

        return $this->fail('审核操作失败');
    }

}