<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/8
 */
namespace app\api1\controller\manage;

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

        //1 当"申请事项"有未审批，主页显示红点提示。
        $apply_count = UserApplies::where('STATUS',0)
            ->where('ISDEL',0)
            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->count();

        //2 当"报告事项”有未读过的，主页显示红点。
        $report_count = UserReports::where('ISNEW',1)
            ->where('ISDEL',0)
            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->count();

        //3 当线上服务，接收到管理端的新留言，主页显示红点。
        $answer_count = MessageBoardAnswers::where(function($t) use($request){
            $askids = MessageBoardAsks::where('ISDEL',0)->where('ASKER_UID',$request->MUID)->column('ID');
            $t->whereIn('ASKID',$askids);
            return $t;
        })->where('ISNEW',1)->count();

        //4 有假期未审批
        $holiday_count = UserHolidayApplyLists::where('STATUS',0)
            //->where('ISDEL',0)
            ->whereIn('UUID',$this->getManageUserIds($request->MUID))
            ->count();

        //4 todo 帮扶走访
        $help_count = 0;

        //5 todo 系统消息
        $system_message_count = 0;

        //轮播
        $sliders = Carousels::field('ID,TITLE,STABLE,SID,JUMP_LINK,IMG,CLIENT_TAG')
            ->where('ISDEL',0)
            ->where('POS',1)
            ->where('TYPE',2)
            ->whereIn('CLIENT_TAG',[self::MANAGE_TAG,self::ALL_TAG])
            ->order('ORDERID','ASC')
            ->select()->map(function($t){
                $t = CarouselLib::parseRow($t);
                unset($t->STABLE,$t->SID);
                return $t;
            })->toArray();

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
            'apply_count'=>$apply_count,
            'report_count'=>$report_count,
            'answer_count'=>$answer_count,
            'holiday_count'=>$holiday_count,
            'help_count'=>$help_count,
            'system_message_count'=>$system_message_count,
            'sliders'=>$sliders
        ]);
    }

}