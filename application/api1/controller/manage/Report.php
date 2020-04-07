<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\model\UserManagers;
use app\common\model\UserReports;
use think\Request;

class Report extends Common{


    public function index(Request $request){
        $page = $request->param('page',1,'int');
        // 加上当前人员的管辖范围条件
        $list = UserReports::where('ISDEL','=',0)
            ->where(function($query)use($request){
                if($request->User->isXCPower){
                    $query->whereIn('UUID',$this->getManageUserIds($request->MUID));
                }
            })
//            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->order('ID','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select();



        $this->ok('',[
//            'userids'=>$request->UUserids,
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    public function info(Request $request){

        // 加上当前人员的管辖范围条件
        $info = UserReports::where(['ID'=>$request->param('id',0)])->where('ISDEL','=',0)
            ->where(function($query)use($request){
                if($request->User->isXCPower){
                    $query->whereIn('UUID',$this->getManageUserIds($request->MUID));
                }
            })
//            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->find();

        if($info){

            $info->IMGS->map(function ($t){
                $t->IMG_URL = build_http_img_url($t->SRC_PATH);
                return $t;
            });

            //set read
            $info->ISNEW = 0;
            $info->save();
        }



        return $this->ok('',[
            'info'=>!$info ? new \stdClass() : $info->toArray()
        ]);
    }

}