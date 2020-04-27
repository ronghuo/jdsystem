<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\model\DecisionImgs;
use app\common\model\NbAuthDept;
use app\common\model\UserDecisions;
use app\common\validate\UserDecisionsVer;
use think\Request;

class RecoveryPlan extends Common {


    public function index(Request $request) {

        $page = $request->param('page',1,'int');
        $user_id = $request->param('userid',0,'int');
        $plan_id = $request->param('id', 0, 'int');

        $list = UserDecisions::where('ISDEL','=',0)
            ->where(function($st) use($user_id, $plan_id, $request) {
                if (!$request->User->isTopPower) {
                    $st->whereIn('UUID', $this->getManageUserIds($request->MUID));
                }
                if ($user_id > 0) {
                    $st->where('UUID', '=', $user_id);
                }
                if ($plan_id > 0) {
                    $st->where('ID', '=', $plan_id);
                }
            })
            ->order('ADD_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select()->map(function($t) {
                $t->imgs->map(function($tt) {
                    $tt->IMG_URL = build_http_img_url($tt->SRC_PATH);
                    return $tt;
                });
                return $t;
            });

        return $this->ok('',[
            'list' => !empty($list) ? $list->toArray() : []
        ]);
    }

    public function save(Request $request) {
        $dmmid = $request->User->DMM_ID;
        $dmm = NbAuthDept::find($dmmid);

        if (!$dmm) {
            return $this->fail('登记单位信息有误');
        }

        $data = [
            'UUID' => $request->param('UUID','','int'),
            'NAME' => $request->param('NAME','','trim'),
            'GENDER' => $request->param('GENDER','','trim'),
            'ID_NUMBER' => $request->param('ID_NUMBER','','trim'),
            'MOBILE' => $request->param('MOBILE','','trim'),
            'DOMICILE_PLACE' => $request->param('DOMICILE_PLACE','','trim'),
            'LIVE_PLACE' => $request->param('LIVE_PLACE','','trim'),
            'BEGIN_DATE' => $request->param('BEGIN_DATE','','trim'),
            'END_DATE' => $request->param('END_DATE','','trim'),
            'FAMILY_MEMBERS' => $request->param('FAMILY_MEMBERS','','trim'),
            'DRUG_HISTORY_AND_TREATMENT' => $request->param('DRUG_HISTORY_AND_TREATMENT','','trim'),
            'CURRENT_STATUS' => $request->param('CURRENT_STATUS','','trim'),
            'CURE_MEASURES' => $request->param('CURE_MEASURES','','trim'),
            'WHETHER_MEDICHINE_ENCOURAGED' => $request->param('WHETHER_MEDICHINE_ENCOURAGED',null,'int'),
            'WHETHER_DETOXIFICATION_REQUIRED' => $request->param('WHETHER_DETOXIFICATION_REQUIRED',null,'trim'),
            'PSYCHOLOGICAL_CONSULTING_PLAN' => $request->param('PSYCHOLOGICAL_CONSULTING_PLAN','','trim'),
            'ASSISTANCE_MEASURES' => $request->param('ASSISTANCE_MEASURES','','trim'),
            'COMMUNITY_NAME' => $request->param('COMMUNITY_NAME','','trim'),
            'SIGN_DATE' => $request->param('SIGN_DATE','','trim'),
            'CREATE_USER_ID' => $request->MUID,
            'CREATE_USER_NAME' => $request->User->NAME,
            'CREATE_DEPT_CODE' => $dmm->DEPTCODE,
            'CREATE_DEPT_NAME' => $dmm->DEPTNAME,
            'CREATE_TIME' => date('Y-m-d H:i:s'),
            'UPDATE_TIME' => date('Y-m-d H:i:s')
        ];
        $v = new UserDecisionsVer();
        if (!$v->check($data)) {
            return $this->fail($v->getError());
        }

        $decision_id = (new UserDecisions())->insertGetId($data);
        if (!$decision_id) {
            return $this->fail('康复计划信息保存失败');
        }

        return $this->ok('康复计划信息保存成功');
    }

}