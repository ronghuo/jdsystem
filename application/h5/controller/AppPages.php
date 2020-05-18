<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/15
 */
namespace app\h5\controller;

use app\common\model\LoginAgreement;
use think\Controller;
use think\Request;
use Carbon\Carbon;
use app\common\model\Articles;
use app\common\model\HelperDiarys;
use app\common\model\Agreement;
use app\common\model\SystemMessages,
    app\common\model\SystemMessageRead;

class AppPages extends Controller {


    public function info(Request $request){
        $uid = $request->route('uid',0,'int');
        $tag = $request->route('tag',0,'int');
        $type = $request->route('type',0,'int');
        $id = $request->route('id',0,'int');

        $info = [];
        $images = [];

        switch ($type){
            //资讯
            case 1:
                $info = Articles::field(['ID','TITLE','CONTENT','ADD_TIME'])
                    ->where(['ID'=>$id])
                    ->where('ISDEL','=',0)
                    ->find();

                if($info){
                    $info['ADD_TIME'] = Carbon::parse($info['ADD_TIME'])->format('Y-m-d');
                    //阅读量 +1
                    Articles::where('ID','=',$id)->setInc('VIEW_NUM');
                }
                break;
            //系统消息
            case 2:
                $info = SystemMessages::field('ID,TITLE,CONTENT,ADD_TIME')
                    ->where('ID',$id)
                    ->where('ISDEL',0)->find();

                if($info && $uid){
                    SystemMessageRead::saveRead([
                        'MSGID'=>$info->ID,
                        'CLIENT_TAG'=>$tag,
                        'CLIENT_UID'=>$uid,
                    ]);
                }
                break;
            //帮扶日记
            case 3:
                $info = HelperDiarys::field('ID,TITLE,CONTENT,ADD_TIME')
                    ->where('ID',$id)
                    ->where('ISDEL',0)
                    ->find();
                //todo 图片
                $images = $info->IMGS->map(function($t){
                    $t->IMG_URL = build_http_img_url($t->SRC_PATH);
                    return $t;
                })->toArray();
                break;
            //康复协议
            case 4:
                $info = Agreement::where('UUID',$id)->find();
                break;
            case 5:
                $info = LoginAgreement::find();
                break;
        }

        $this->assign('info',$info);
        $this->assign('images',$images);
        return $this->fetch();
    }

}