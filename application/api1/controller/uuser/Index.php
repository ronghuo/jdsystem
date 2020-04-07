<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/8
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use app\common\model\UserReports,
    app\common\model\UserApplies,
    app\common\model\UserHolidayApplyLists,
    app\common\model\MessageBoardAnswers,
    app\common\model\MessageBoardAsks;
use think\Request;
use app\common\model\Carousels,
    app\common\library\Carousel as CarouselLib;


class Index extends Common{


    public function index(Request $request){


        //5 todo 系统消息
        $system_message_count = 0;

        $sliders = Carousels::field('ID,TITLE,STABLE,SID,JUMP_LINK,IMG,CLIENT_TAG')
            ->where('ISDEL',0)
            ->where('POS',1)
            ->where('TYPE',2)
            ->whereIn('CLIENT_TAG',[self::UUSER_TAG,self::ALL_TAG])
            ->order('ORDERID','ASC')
            ->select()->map(function($t){
                $t = CarouselLib::parseRow($t);
                unset($t->STABLE,$t->SID);
                return $t;
            })->toArray();

        //轮播
        /*$sliders = [
            [
                'ID'=>1,
                'TITLE'=>'轮播测试',
                'JUMP_LINK'=>'http://jd.appasd.com/',
                'IMG_URL'=>'https://p1.ssl.qhmsg.com/dmtfd/654_654_/t0142dfbb95d5a479da.jpg'
            ],
            [
                'ID'=>2,
                'TITLE'=>'轮播测试222',
                'JUMP_LINK'=>'http://jd.appasd.com/',
                'IMG_URL'=>'https://p1.ssl.qhmsg.com/dmtfd/654_654_/t015276082617b55b7c.jpg'
            ]
        ];*/


        return $this->ok('ok',[
            'system_message_count'=>$system_message_count,
            'sliders'=>$sliders
        ]);
    }

}