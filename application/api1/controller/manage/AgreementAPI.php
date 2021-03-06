<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\library\AppLogHelper;
use app\common\model\Agreement;
use app\common\model\AgreementImgs;
use app\common\model\NbAuthDept;
use app\common\validate\UserAgreementVer;
use think\Request;

class AgreementAPI extends Common {


    public function index(Request $request) {

        $uuid = $request->param('UUID', 0, 'int');
        $list = Agreement::where(function($query) use($request, $uuid) {
                if (!$request->User->isTopPower) {
                    $query->whereIn('UUID', $this->getManageUserIds($request->MUID));
                }
                if ($uuid > 0) {
                    $query->where('UUID', $uuid);
                }
            })
            ->order('ADD_TIME','DESC')
            ->select()
            ->map(function ($item) {
                $item->images->map(function($image) {
                    $image->SRC_PATH = build_http_img_url($image->SRC_PATH);
                    return $image;
                });
                return $item;
            });

        AppLogHelper::logManager($request, AppLogHelper::ACTION_ID_M_AGREEMENT_QUERY, $uuid, [
            'UUID' => $uuid
        ]);

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
            'CONTENT' => $request->param('CONTENT','','trim'),
            'ADD_USER_MOBILE' => $request->User->MOBILE,
            'ADD_USER_NAME' => $request->User->NAME,
            'ADD_DEPT_CODE' => $dmm->DEPTCODE,
            'ADD_DEPT_NAME' => $dmm->DEPTNAME,
            'ADD_TERMINAL' => TERMINAL_APP,
            'ADD_TIME' => date('Y-m-d H:i:s'),
            'UPDATE_TIME' => date('Y-m-d H:i:s')
        ];
        $v = new UserAgreementVer();
        if (!$v->check($data)) {
            return $this->fail($v->getError());
        }

        $agreement_id = (new Agreement())->insertGetId($data);
        if (!$agreement_id) {
            return $this->fail('社戒社康协议保存失败');
        }

        $res = $this->uploadImages($request, ['agreements/']);

        if ($res && !empty($res['images'])) {
            (new AgreementImgs())->saveData($agreement_id, $res['images']);
            $data['IMAGES'] = $res['images'];
        }

        AppLogHelper::logManager($request, AppLogHelper::ACTION_ID_M_AGREEMENT_ADD, $data['UUID'], $data);

        return $this->ok('社戒社康协议保存成功');
    }

}