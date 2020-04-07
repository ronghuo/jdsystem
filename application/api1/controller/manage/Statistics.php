<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/5/9
 */

namespace app\api1\controller\manage;

use think\Db;
use app\api1\controller\Common;
use think\Request;
use Carbon\Carbon;
use //app\common\model\Areas,
    //app\common\model\AreasSubs,
    app\common\model\Upareatable,
    app\common\model\Subareas,
    app\common\model\UserUsers,
    app\common\model\UserHolidayApplyLists,
    app\common\model\UserHolidayApplies,
    app\common\model\Urans,
    app\common\model\HelperDiarys;


class Statistics extends Common{

    public function index(Request $request){
        $type = $request->post('TYPE');

        switch($type){
            case 'holiday':
                return $this->holiday($request);
                break;
            case 'userusers':
                return $this->userUsers($request);
                break;
            case 'uran':
                return $this->uran($request);
                break;
            case 'mhelper':
                return $this->mhelper($request);
                break;
            default:

                break;


        }

    }

    public function holiday(Request $request){

        $params = $this->getParams($request);

        $time = Carbon::parse($params['datetime']);

        $userids = UserUsers::field('ID')
            ->where('ISDEL',0)
            ->where('COMMUNITY_ID',$params['community_id'])
            ->select()->column('ID');

        if(!$userids){
            return $this->ok('ok',[
                'title'=>$params['title'],
                'total'=>0,
                'agreed'=>0,
                'rejected'=>0,
                'total_continue'=>0,
                'agreed_continue'=>0,
                'rejected_continue'=>0,
                'wait_check'=>0,
                'on_time_cancel'=>0,
                'timeout_un_cancel'=>0,
                'timeout_cancel'=>0,
                'userids'=>0
            ]);
        }


        $countQuery = UserHolidayApplyLists::where('ISDEL',0)
            ->whereIn('UUID',$userids)
            ->where('ADD_TIME','>=',$time->firstOfMonth()->toDateTimeString())
            ->where('ADD_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString());

        //总请假申请数
        $total = $countQuery->where('STATUS','>',-1)->count();

        //被同意的请假数
        $agreed = $countQuery->where('STATUS',1)->count();

        //被拒绝的请假数
        $rejected = $countQuery->where('STATUS',2)->count();

        //总续假申请数
        $total_continue = UserHolidayApplies::with([
            'lists'=>function($query) use($userids,$time){
                $query->where('ISDEL',0)
                    ->where('STATUS','>',-1)
                    ->whereIn('UUID',$userids)
                    ->where('ADD_TIME','>=',$time->firstOfMonth()->toDateTimeString())
                    ->where('ADD_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString());
            }
        ])->where('ISDEL',0)
            ->where('STATUS',3)->count();

        //被同意的续假数
        $agreed_continue = UserHolidayApplies::with([
            'lists'=>function($query) use($userids,$time){
                $query->where('ISDEL',0)
                    ->where('STATUS',1)
                    ->whereIn('UUID',$userids)
                    ->where('ADD_TIME','>=',$time->firstOfMonth()->toDateTimeString())
                    ->where('ADD_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString());
            }
        ])->where('ISDEL',0)
            ->where('STATUS',3)->count();;

        //被拒绝的续假数
        $rejected_continue = UserHolidayApplies::with([
            'lists'=>function($query) use($userids,$time){
                $query->where('ISDEL',0)
                    ->where('STATUS',2)
                    ->whereIn('UUID',$userids)
                    ->where('ADD_TIME','>=',$time->firstOfMonth()->toDateTimeString())
                    ->where('ADD_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString());
            }
        ])->where('ISDEL',0)
            ->where('STATUS',3)->count();;

        //待审批的总请、续假数
        $wait_check = $countQuery->where('STATUS',0)->count();

        //按时销假报到数
        $on_time_cancel = $countQuery->where('STATUS',4)
            ->where('BACK_TIME','>=',Db::raw('date(COMPLETE_TIME)'))
            ->count();

        //过期未报道数
        $timeout_un_cancel = $countQuery->where('STATUS',1)
            ->where('BACK_TIME','<',Carbon::now()->toDateString())
            ->count();

        //超期报道数
        $timeout_cancel = $countQuery->where('STATUS',4)
            ->where('BACK_TIME','<',Db::raw('date(COMPLETE_TIME)'))
            ->count();

        return $this->ok('ok',[
            'title'=>$params['title'],
            //'total'=>$total,//总请假申请数
            'total_text'=>$total.' 总请假申请数',
            //'agreed'=>$agreed,//被同意的请假数
            'agreed_text'=>$agreed.' 被同意的请假数',//
            //'rejected'=>$rejected,//被拒绝的请假数
            'rejected_text'=>$rejected.' 被拒绝的请假数',//
            //'total_continue'=>$total_continue,//总续假申请数
            'total_continue_text'=>$total_continue.' 总续假申请数',//
            //'agreed_continue'=>$agreed_continue,//被同意的续假数
            'agreed_continue_text'=>$agreed_continue.' 被同意的续假数',//
            //'rejected_continue'=>$rejected_continue,//被拒绝的续假数
            'rejected_continue_text'=>$rejected_continue.' 被拒绝的续假数',//
            //'wait_check'=>$wait_check,//待审批的总请、续假数
            'wait_check_text'=>$wait_check.' 待审批的总请+续假数',//
            //'on_time_cancel'=>$on_time_cancel,//按时销假报到数
            'on_time_cancel_text'=>$on_time_cancel.' 按时销假报到数',//
            //'timeout_un_cancel'=>$timeout_un_cancel,//过期未报道数
            'timeout_un_cancel_text'=>$timeout_un_cancel.' 过期未报道数',//
            //'timeout_cancel'=>$timeout_cancel,//超期报道数
            'timeout_cancel_text'=>$timeout_cancel.' 超期报道数',//超期报道数
        ]);
    }

    public function userUsers(Request $request){

        $params = $this->getParams($request);

        $time = Carbon::parse($params['datetime']);

        $total = UserUsers::where('ISDEL',0)
            ->where('COMMUNITY_ID',$params['community_id'])
            ->where('ADD_TIME','>=',$time->firstOfMonth()->toDateTimeString())
            ->where('ADD_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString())
            ->count();


        return $this->ok('ok',[
            'title'=>$params['title'],
//            'total'=>$total,
            'total_text'=>'康复人员数量 '.$total,
        ]);
    }

    public function uran(Request $request){

        $params = $this->getParams($request);

        $time = Carbon::parse($params['datetime']);

        $userids = UserUsers::field('ID')
            ->where('ISDEL',0)
            ->where('COMMUNITY_ID',$params['community_id'])
            ->select()->column('ID');


        if(!$userids){

            return $this->ok('ok',[
                'title'=>$params['title'],
                //'on_time'=>'0%',
                'on_time_text'=>'按时尿检占比 0%',
            ]);
        }

        $urans = Urans::field('UUID')
            ->whereIn('UUID',$userids)
            ->where('CHECK_TIME','>=',$time->firstOfMonth()->toDateTimeString())
            ->where('CHECK_TIME','<=',$time->lastOfMonth()->addSeconds(24*3600 -1)->toDateTimeString())
            ->group('UUID')
            ->select();

        $p = (int)(count($urans) *100 / count($userids)) ;

        return $this->ok('ok',[
            'title'=>$params['title'],
            //'on_time'=>'33%',
            'on_time_text'=>'按时尿检占比 '.$p.'%',
//            'c_count'=>$urans,
//            'count'=>$userids
        ]);
    }

    public function mhelper(Request $request){

        $params = $this->getParams($request);

        $time = Carbon::parse($params['datetime']);

        $userids = UserUsers::field('ID')
            ->where('ISDEL',0)
            ->where('COMMUNITY_ID',$params['community_id'])
            ->select()->column('ID');


        if(!$userids){

            return $this->ok('ok',[
                'title'=>$params['title'],
//                'completed'=>'0%',
                'completed_text'=>'帮扶走访完成率 0%',
//                'uncompleted'=>'0%',
                'uncompleted_text'=>'帮扶走访未完成率 0%',
            ]);
        }
        $help_diary_need = config('app.help_diary_need');

        $completeds = HelperDiarys::field('UUID,count(*) as c')
            ->where('ADD_YEAR',$time->year)
            ->where('ADD_MONTH',$time->month)
            ->group('UUID')
            ->having('c >='.$help_diary_need)
            ->select();

        $completed = (int) (count($completeds) * 100 / count($userids));


        return $this->ok('ok',[
            'title'=>$params['title'],
//            'completed'=>'33%',
            'completed_text'=>'帮扶走访完成率 '.$completed.'%',
//            'uncompleted'=>'66%',
            'uncompleted_text'=>'帮扶走访未完成率 '.(100 - $completed).'%',
        ]);
    }

    protected function getParams(Request $request){

        $datetime = $request->post('DATE_TIME');
        $county_id = $request->post('COUNTY_ID');
        $street_id = $request->post('STREET_ID');
        $community_id = $request->post('COMMUNITY_ID');

        if(!$datetime || !$county_id || !$street_id || !$community_id){
            return $this->fail('请输入筛选参数');
        }

        $county = Upareatable::where('UPAREAID', $county_id)->find();//Areas::find($county_id);
        $street = Subareas::where('CODE12', $street_id)->find();//AreasSubs::find($street_id);
        $community = Subareas::where('CODE12', $community_id)->find();//AreasSubs::find($community_id);

        return [
            'title'=>implode('',[
                date('Y年m月',strtotime($datetime)).' ',
                $county? $county->NAME .'/' : '',
                $street? $street->NAME.'/' : '',
                $community? $community->NAME : ''
            ]),
            'datetime'=>$datetime,
            'county_id'=>$county_id,
            'street_id'=>$street_id,
            'community_id'=>$community_id,
        ];
    }

}