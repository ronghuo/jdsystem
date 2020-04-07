<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use think\Request;
use app\common\validate\UserAppliesVer;
use app\common\model\UserApplies,
    app\common\model\UserApplyImgs;

class Apply extends Common{


    public function index(Request $request){
        $page = $request->param('page',1,'int');
        $list = UserApplies::where(['UUID'=>$request->UUID])->where('ISDEL','=',0)
            //->field(['ID',''])
            ->order('ADD_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select()->map(function($t){
                $t->ADD_TIME = \Carbon\Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i');
                $t->STATUS_TEXT = $t->status_text;
                return $t;
            });


        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);

    }

    public function info(Request $request){
        $info = UserApplies::where(['ID'=>$request->param('id',0)])->where('ISDEL','=',0)
            ->where(['UUID'=>$request->UUID])
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

    public function save(Request $request){


        $data = [
            'UUID'=>$request->UUID,
            'UNAME'=>$request->User->NAME,
            'TITLE'=>$request->param('TITLE',''),
            'CONTENT'=>$request->param('CONTENT',''),
        ];

        $v = new UserAppliesVer();
        if(!$v->check($data)){
            return $this->fail($v->getError());

        }

        $ua_id = (new UserApplies())->insertGetId($data);


        if($ua_id>0){
            $this->saveImages($request,$ua_id);


            // 支持音频
            $audios = $this->uploadAudios($request);

            if(isset($audios['audios']) && !empty($audios['audios'])){
                (new UserApplyImgs())->saveData($ua_id,$audios['audios'],1);
            }
        }



        return $this->ok('申请提交成功',[
            'result'=>$ua_id
        ]);

    }

    protected function saveImages(Request $request,$ua_id){

//        \app\common\library\Mylog::write([
//            $_POST,
//            $_FILES,
//            $_SERVER
//        ],'reports');

        $res = $this->uploadImages($request,['apply/']);

        if(!$res){
            return false;
        }
        return (new UserApplyImgs())->saveData($ua_id,$res['images']);
//        return $this->saveHolidayImages2DB($uhal_id,$res['images']);
    }


}