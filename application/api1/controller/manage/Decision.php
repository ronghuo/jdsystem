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

class Decision extends Common {


    public function index(Request $request) {

        $page = $request->param('page',1,'int');
        $user_id = $request->param('userid',0,'int');
        $decision_id = $request->param('id', 0, 'int');

        $list = UserDecisions::where(function($st) use($user_id, $decision_id, $request) {
                if (!$request->User->isTopPower) {
                    $st->whereIn('UUID', $this->getManageUserIds($request->MUID));
                }
                if ($user_id > 0) {
                    $st->where('UUID', '=', $user_id);
                }
                if ($decision_id > 0) {
                    $st->where('ID', '=', $decision_id);
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
            'TITLE' => $request->param('TITLE','','trim'),
            'CONTENT' => $request->param('CONTENT','','trim'),
            'ADD_USER_MOBILE' => $request->User->MOBILE,
            'ADD_USER_NAME' => $request->User->NAME,
            'ADD_DEPT_CODE' => $dmm->DEPTCODE,
            'ADD_DEPT_NAME' => $dmm->DEPTNAME,
            'ADD_TERMINAL' => TERMINAL_APP,
            'ADD_TIME' => date('Y-m-d H:i:s'),
            'UPDATE_TIME' => date('Y-m-d H:i:s'),
            'BEGIN_TIME' => $request->param('BEGIN_TIME', '', 'trim')
        ];
        $v = new UserDecisionsVer();
        if (!$v->check($data)) {
            return $this->fail($v->getError());
        }

        $decision_id = (new UserDecisions())->insertGetId($data);
        if (!$decision_id) {
            return $this->fail('决定书信息保存失败');
        }

        $res = $this->uploadImages($request, ['decisions/']);

        if ($res && !empty($res['images'])) {
            (new DecisionImgs())->saveData($decision_id, $res['images']);
        }

        return $this->ok('决定书信息保存成功');
    }

}