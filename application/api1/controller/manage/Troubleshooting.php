<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\library\AppLogHelper;
use app\common\model\DecisionImgs;
use app\common\model\NbAuthDept;
use app\common\model\Subareas;
use app\common\model\TroubleshootingAssignment;
use app\common\model\TroubleshootingPerson;
use app\common\model\TroubleshootingPersonExtension;
use app\common\model\TroubleshootingTemplate;
use app\common\model\TroubleshootingTemplateField;
use app\common\model\Upareatable;
use app\common\model\UserDecisions;
use app\common\validate\TroubleshootPersonVer;
use app\common\validate\UserDecisionsVer;
use Carbon\Carbon;
use think\Request;

class Troubleshooting extends Common {

    public function getTemplateList() {
        $rows = TroubleshootingTemplate::where('EFFECTIVE', EFFECTIVE)
            ->field('ID, NAME, REMARK')->all();
        return $this->ok('Success', [
            'list' => $rows
        ]);
    }

    public function getTemplateFieldList(Request $request) {
        $templateId = $request->param('TEMPLATE_ID');
        if (empty($templateId)) {
            $this->fail("参数错误");
        }
        $rows = TroubleshootingTemplateField::where('EFFECTIVE', EFFECTIVE)
            ->where('TEMPLATE_ID', $templateId)
            ->field('ID, CODE, NAME, WIDGET, SORT, NULLABLE, DESC')
            ->order('SORT ASC')
            ->select();
        return $this->ok('Success', [
            'list' => $rows
        ]);
    }

    public function addPerson(Request $request) {
        $templateId = $request->param('TEMPLATE_ID', 0, 'int');
        $name = $request->param('NAME', '', 'trim');
        $idCode = $request->param('ID_CODE', '', 'trim');
        $domicileProvinceCode = $request->param('DOMICILE_PROVINCE_CODE', DEFAULT_PROVINCE_ID, 'trim');
        $domicileCityCode = $request->param('DOMICILE_CITY_CODE', DEFAULT_CITY_ID, 'trim');
        $domicileCountyCode = $request->param('DOMICILE_COUNTY_CODE', '', 'trim');
        $domicileStreetCode = $request->param('DOMICILE_STREET_CODE', '', 'trim');
        $domicileCommunityCode = $request->param('DOMICILE_COMMUNITY_CODE', '', 'trim');
        $domicileAddress = $request->param('DOMICILE_ADDRESS', '', 'trim');
        $effective = $request->param('EFFECTIVE', EFFECTIVE, 'trim');
        $remark = $request->param('REMARK', '', 'trim');
        $data = [
            'TEMPLATE_ID' => $templateId,
            'NAME' => $name,
            'ID_CODE' => $idCode,
            'DOMICILE_PROVINCE_CODE' => $domicileProvinceCode,
            'DOMICILE_CITY_CODE' => $domicileCityCode,
            'DOMICILE_COUNTY_CODE' => $domicileCountyCode,
            'DOMICILE_STREET_CODE' => $domicileStreetCode,
            'DOMICILE_COMMUNITY_CODE' => $domicileCommunityCode,
            'DOMICILE_ADDRESS' => $domicileAddress,
            'EXECUTE_STATUS' => TroubleshootingPerson::EXECUTE_STATUS_UNHANDLED,
            'EFFECTIVE' => $effective,
            'REMARK' => $remark
        ];
        $cnt = TroubleshootingPerson::where(['EFFECTIVE' => EFFECTIVE, 'ID_CODE' => $idCode])->count();
        if ($cnt > 0) {
            return $this->fail("重复的身份证号码");
        }
        $ver = new TroubleshootPersonVer();
        if (!$ver->scene('create')->check($data)) {
            return $this->fail($ver->getError());
        }
        $domicileUpAreas = Upareatable::where('UPAREAID','in', [$domicileProvinceCode, $domicileCityCode])->order('UPAREAID','asc')->select()->column('NAME');
        $domicileSubAreas = Subareas::where('CODE12','in', [$domicileCountyCode, $domicileStreetCode, $domicileCommunityCode])->order('CODE12','asc')->select()->column('NAME');
        $domicileProvinceName = $domicileUpAreas[0];
        $domicileCityName = $domicileUpAreas[1];
        $domicileCountyName = $domicileSubAreas[0];
        $domicileStreetName = $domicileSubAreas[1];
        $domicileCommunityName = $domicileSubAreas[2];
        $data = array_merge($data, [
            'DOMICILE_PROVINCE_NAME' => $domicileProvinceName,
            'DOMICILE_CITY_NAME' => $domicileCityName,
            'DOMICILE_COUNTY_NAME' => $domicileCountyName,
            'DOMICILE_STREET_NAME' => $domicileStreetName,
            'DOMICILE_COMMUNITY_NAME' => $domicileCommunityName
        ]);
        $fields = TroubleshootingTemplateField::where(['EFFECTIVE' => EFFECTIVE, 'TEMPLATE_ID' => $templateId])->all();
        $extensions = [];
        foreach ($fields as $field) {
            $code = $field->CODE;
            $name = $field->NAME;
            $widget = $field->WIDGET;
            if ($widget == TroubleshootingTemplateField::WIDGET_TEXT) {
                $value = $request->param($code);
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_TEXTAREA) {
                $value = $request->param($code);
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_IMAGE) {
                $result = $this->uploadImages($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['images'])) {
                    $value = implode(",", $result['images']);
                }
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_AUDIO) {
                $result = $this->uploadAudios($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['audios'])) {
                    $value = implode(",", $result['audios']);
                }
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_VIDEO) {
                $result = $this->uploadAudios($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['videos'])) {
                    $value = implode(",", $result['videos']);
                }
            }
            else {
                $value = $request->param($code);
            }
            if ($field->NULLABLE == WHETHER_YES && empty($value)) {
                return $this->fail("缺少$name");
            }
            $extension = new TroubleshootingPersonExtension();
            $extension->TEMPLATE_ID = $templateId;
            $extension->FIELD_ID = $field->ID;
            $extension->FIELD_VALUE = $value;
            $extension->CREATE_TIME = Carbon::now();
            $extension->UPDATE_TIME = Carbon::now();
            array_push($extensions, $extension);
        }
        $user = $request->User;
        $person = new TroubleshootingPerson();
        $data = array_merge($data, [
            'CREATE_USER_ID' => $user->ID,
            'CREATE_USER_NAME' => $user->NAME,
            'CREATE_TIME' => Carbon::now(),
            'CREATE_TERMINAL' => TERMINAL_APP,
            'UPDATE_USER_ID' => $user->ID,
            'UPDATE_USER_NAME' => $user->NAME,
            'UPDATE_TIME' => Carbon::now(),
            'UPDATE_TERMINAL' => TERMINAL_APP
        ]);
        $person->save($data);
        if (empty($person->ID)) {
            return $this->fail("排查人员信息添加失败");
        }
        foreach ($extensions as $extension) {
            $extension->PERSON_ID = $person->ID;
            $extension->save();
        }
        $assignment = new TroubleshootingAssignment();
        $assignment->COUNTY_CODE = $domicileCountyCode;
        $assignment->COUNTY_NAME = $domicileCountyName;
        $assignment->STREET_CODE = $domicileStreetCode;
        $assignment->STREET_NAME = $domicileStreetName;
        $assignment->COMMUNITY_CODE = $domicileCommunityCode;
        $assignment->COMMUNITY_NAME = $domicileCommunityName;
        $assignment->REASON = "系统根据户籍地信息自动指派";
        $assignment->ACTION = TroubleshootingAssignment::ACTION_ASSIGN;
        $assignment->CREATE_USER_ID = $user->ID;
        $assignment->CREATE_USER_NAME = $user->NAME;
        $assignment->CREATE_TIME = Carbon::now();
        $assignment->save();
        return $this->ok('排查人员信息添加成功');
    }

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

        AppLogHelper::logManager($request, AppLogHelper::ACTION_ID_M_DECISION_QUERY, $user_id, [
            'ID' => $decision_id,
            'UUID' => $user_id
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
            $data['IMAGES'] = $res['images'];
        }

        AppLogHelper::logManager($request, AppLogHelper::ACTION_ID_M_DECISION_ADD, $data['UUID'], $data);

        return $this->ok('决定书信息保存成功');
    }

}