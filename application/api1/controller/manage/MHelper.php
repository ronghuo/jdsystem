<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/13
 */
namespace app\api1\controller\manage;

use app\common\model\NbAuthDept;
use Carbon\Carbon;
use app\api1\controller\Common;
use app\common\validate\HelperDiarysVer;
use app\common\model\HelperDiarys,
    app\common\model\HelperDiaryImgs,
    app\common\model\HelperAreas,
    app\common\model\UserUsers;
use think\model\Collection;
use think\Request;

class MHelper extends Common{

    protected $need_times = 2;

    public function index(Request $request){

        // 处理年，月搜索

        $year = $request->get('year',0,'int') ? : date('Y');
        $month = $request->get('month',0,'int') ? : date('n');

        $umid = $request->MUID;
        $ha = new HelperAreas();
        $hd = new HelperDiarys();
        $completed = [];
        $completed_uuids = [];
        $help_diary_need = config('app.help_diary_need');



        $userids = $ha->getUserIdsInAreas($umid);
        //$userids = [1,2];

        $group_count = $hd->getCountsGroupByUUID($umid,$year,$month);

        //print_r($group_count);exit;

        Collection::make(
            $group_count
        )->map(function($t) use($help_diary_need,&$completed,&$completed_uuids){
//            if($t->tt >= $this->need_times){
            if($t['tt'] >= $help_diary_need){
                $completed[] = $t;
                $completed_uuids[] = $t['UUID'];
            }else{
                return $t;
            }
        });


        //未完成的康复人员ids
        $un_complete_uuids = Collection::make($userids)->diff($completed_uuids)->toArray();

        $un_complete_areas= [
            'countrys'=>[],
            'streets'=>[],
            'communtys'=>[],
        ];

        UserUsers::field('ID,COUNTY_ID_12,STREET_ID,COMMUNITY_ID')
            ->where('ID','in',implode(",",$un_complete_uuids))
            ->where('ISDEL',0)->select()
            ->map(function($t) use(&$un_complete_areas){

                $un_complete_areas['countrys'][] = $t->COUNTY_ID_12;
                $un_complete_areas['streets'][] = $t->STREET_ID;
                $un_complete_areas['communtys'][] = $t->COMMUNITY_ID;
                //print_r($un_complete_areas);
            });

        $un_complete_areas['countrys'] = count(array_unique($un_complete_areas['countrys']));
        $un_complete_areas['streets'] = count(array_unique($un_complete_areas['streets']));
        $un_complete_areas['communtys'] = count(array_unique($un_complete_areas['communtys']));

        return $this->ok('ok',[
//                'year'=>$year,
//                'month'=>$month,
//            'completed'=>$completed,
//            'userids'=>$userids,
//            'completed_uuids'=>$completed_uuids,
//            'users'=>$list,
//            'un_complete_uuids'=>$un_complete_uuids,
            'un_complete_areas'=>$un_complete_areas,
            'un_complete_count'=>count($un_complete_uuids)
        ]);
    }


    public function userList(Request $request){

        // 处理年，月搜索


        $county_id = $request->get('county_id',0,'int');
        $street_id = $request->get('street_id',0,'int');
        $community_id = $request->get('community_id',0,'int');
        $year = $request->get('year',0,'int') ? : date('Y');
        $month = $request->get('month',0,'int') ? : date('n');

        $umid = $request->MUID;
        $ha = new HelperAreas();

        $help_diary_need = config('app.help_diary_need');

        $list = UserUsers::field('ID,NAME,HEAD_IMG')
            ->where(function($t)use($county_id,$street_id,$community_id){
                if($county_id>0){
                    $t->where('COUNTY_ID_12',$county_id);
                }
                if($street_id>0){
                    $t->where('STREET_ID',$street_id);
                }
                if($community_id>0){
                    $t->where('COMMUNITY_ID',$community_id);
                }
            })
            ->where('ISDEL',0)
            ->where(function($query)use($request, $ha){
                if (!$request->User->isTopPower) {
                    $query->whereIn('ID',$ha->getUserIdsInAreas($request->MUID));
                }
            })
            //->whereIn('ID',$ha->getUserIdsInAreas($umid))
            ->select()->map(function($t) use($umid,$help_diary_need,$year,$month){
                $t->HEAD_IMG_URL = build_http_img_url($t->HEAD_IMG);

                $count = HelperDiarys::where('UMID',$umid)
                    ->where('UUID',$t->ID)
                    ->where('ADD_YEAR',$year)
                    ->where('ADD_MONTH',$month)
                    ->where('ISDEL',0)
                    ->count();

                $t->DIARY_COUNT = $count;
                $t->IS_COMPLETED = $count>=$help_diary_need? 1 : 0;

                return $t;
            });


        return $this->ok('ok',[
            //'umid'=>$request->MUID,
            'list'=>$list->toArray()
        ]);
    }

    public function diaryList(Request $request){

        $page = $request->param('page',1,'int');

        $uuid = $request->param('uuid',0,'int');

        $list = HelperDiarys::field('ID,TITLE,ADD_TIME')
            ->where(function($t)use($uuid){
                if($uuid>0){
                    $t->where('UUID',$uuid);
                }

            })
            ->where('ISDEL',0)
            ->order('ADD_TIME','desc')
            ->page($page,self::PAGE_SIZE)
            ->select()->map(function($t){
                $t->H5_URL =  get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>self::MANAGE_TAG,'type'=>3,'id'=>$t->ID]);
                return $t;
            });

        return $this->ok('ok',[
            //'debug'=>
            'list'=>$list->toArray()
        ]);

    }

    public function deleteDiary(Request $request){

        $id = $request->post('ID',0,'int');
        if(!$id){
            return $this->fail('参数有误');
        }
        $info = HelperDiarys::where('ID',$id)->where('ISDEL',0)->find($id);
        if(!$info){
            return $this->fail('该记录不存在');
        }

        if($info->UMID != $request->MUID){
            return $this->fail('无权限操作');
        }

        $info->ISDEL = 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        return $this->ok('删除成功');

    }

    public function saveDiary(Request $request){

        $uuid = $request->post('UUID',0,'int');

        if(!$uuid){
            return $this->fail('缺少帮扶人员信息');
        }

        // 检查是否在当前管理员的管辖区

        if(!(new HelperAreas())->isInMyAreas($uuid,$request->MUID)){
            return $this->fail('该人员不在你的管辖区');
        }

        $dmmid = $request->User->DMM_ID;
        $dmm = NbAuthDept::find($dmmid);

        if (!$dmm) {
            return $this->fail('登记单位信息有误');
        }

        $data = [
            'UMID'=>$request->MUID,
            'UUID'=>$uuid,
            'ADD_YEAR' => date('Y'),
            'ADD_MONTH' => date('n'),
            'ADD_DAY' => date('j'),
            'CONTENT' => $request->param('CONTENT','','trim'),
            'ASSIST_NEXT_PLAN' => $request->param('ASSIST_NEXT_PLAN','','trim'),
            'ASSIST_TIME' => $request->param('ASSIST_TIME','','trim'),
            'ASSIST_PLACE' => $request->param('ASSIST_PLACE','','trim'),
            'INTERVIEW_EVIDENCE' => $request->param('INTERVIEW_EVIDENCE','','trim'),
            'INTERVIEW_PERSON' => $request->param('INTERVIEW_PERSON','','trim'),
            'RECOVERYUSER_RELATION' => $request->param('RECOVERYUSER_RELATION', '', 'trim'),
            'INTERVIEW_WSTAFF_NAME' => $request->param('INTERVIEW_WSTAFF_NAME', '', 'trim'),
            'INTERVIEW_WSTAFF_DEPT' => $request->param('INTERVIEW_WSTAFF_DEPT', '', 'trim'),
            'ADD_DEPT_CODE' => $dmm->DEPTCODE,
            'ADD_DEPT_NAME' => $dmm->DEPTNAME,
            'ADD_USER_NAME' => $request->User->NAME
        ];

        $v = new HelperDiarysVer();

        if(!$v->check($data)){
            return $this->fail($v->getError());
        }

        $hd_id = (new HelperDiarys())->insertGetId($data);
        if(!$hd_id){
            return $this->fail('记录保存失败');

        }

        $res = $this->uploadImages($request,['helperdiarys/']);

        if($res && isset($res['images'])){
            (new HelperDiaryImgs())->saveData($hd_id,$res['images']);
        }


        return $this->ok('记录保存成功');
    }
}