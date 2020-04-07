<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use think\Request;
use app\common\model\Urans;
//use app\common\model\UserManagers;
//use app\common\model\Dmmcs;


class Uran extends Common{

//    protected $middleware = ['Api1UUAuth'];

    public function test(Request $request){

    }

    public function index(Request $request){

        $page = $request->param('page',1,'int');

        $list = Urans::field(['ID','CHECK_TIME','RESULT'])
            ->where(['UUID'=>$request->UUID])
            ->where('ISDEL','=',0)
            ->order('CHECK_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select();

        return $this->ok('',[
            'list'=>!empty($list) ? $list : []
        ]);

    }

    public function info(Request $request){
        $info = Urans::where([
            'ID'=>$request->param('id',0),
            'UUID'=>$request->UUID
        ])->with([
            'dmmc'=>function($query){
                $query->field('ID,DEPTCODE as DM,DEPTNAME as DMMC');
            },
            'uuser'=>function($query){
                $query->field('ID,UUCODE,NAME,ID_NUMBER');
            },
            'muser'=>function($query){
                $query->field('ID,NAME,UCODE');
            }
        ])->where('ISDEL','=',0)->find();

        if(!$info){
            $this->ok('',[
                'info'=>new \stdClass()
            ]);
        }
        $info->imgs->map(function($tt){
            $tt->IMG_URL = build_http_img_url($tt->SRC_PATH);
            return $tt;
        });

        if(!$info->dmmc){
            $info->dmmc = new \stdClass();
        }
//        $muser = UserManagers::field(['ID','NAME','UCODE'])->find($info->UMID);
//        $dmmc = Dmmcs::field(['ID','DM','DMMC'])->find($info->DMM_ID);

        return $this->ok('',[
            'uuser'=>$info->uuser,
            'muser'=>$info->muser,
            'dmmc'=>$info->dmmc,
            'info'=>$info->toArray()
        ]);
    }
}