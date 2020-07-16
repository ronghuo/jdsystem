<?php

namespace app\htsystem\controller;

use app\common\model\Subareas;
use app\common\model\TroubleshootingAssignment;
use app\common\model\TroubleshootingPersonExtension;
use app\common\validate\TroubleshootPersonVer;
use app\common\validate\TroubleshootTemplateFieldVer;
use Carbon\Carbon;
use think\Request;
use app\common\model\TroubleshootingTemplateField as TroubleshootingTemplateFieldModel;
use app\common\model\TroubleshootingTemplate as TroubleshootingTemplateModel;
use app\common\model\TroubleshootingPerson as TroubleshootingPersonModel;

class TroubleshootingPerson extends Common
{

    const WIDGET_LIST = [
        'TEXT' => '文本框',
        'TEXTAREA' => '文本域',
        'IMAGE' => '图片',
        'AUDIO' => '语音',
        'VIDEO' => '视频'
    ];

    public function index(Request $request) {
        $is_so = false;
        $templateId = $request->param('TEMPLATE_ID', '', 'int');
        $executeStatus = $request->param('EXECUTE_STATUS', '', 'trim');
        $keywords = $request->param('keywords', '', 'trim');
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.ID_CODE',
            'A.DOMICILE_PROVINCE_NAME',
            'A.DOMICILE_CITY_NAME',
            'A.DOMICILE_COUNTY_NAME',
            'A.DOMICILE_STREET_NAME',
            'A.DOMICILE_COMMUNITY_NAME',
            'A.DOMICILE_ADDRESS',
            'A.EXECUTOR_MOBILE',
            'A.EXECUTOR_NAME',
            'A.EXECUTE_TIME',
            'A.EXECUTE_STATUS',
            'A.REMARK',
            'B.NAME TEMPLATE_NAME'
        ];
        $assignmentSql = TroubleshootingAssignment::order('CREATE_TIME DESC')->buildSql();
        $assignmentSql = db()->table("$assignmentSql A")->group('PERSON_ID')->buildSql();
        $query = TroubleshootingPersonModel::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->leftJoin("$assignmentSql C", 'A.ID = C.PERSON_ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->where(function ($query) {
                $where = $this->getManageWhere();
                if (!empty($where)) {
                    foreach ($where as $fd => $wh) {
                        $query->where("C.$fd", $wh);
                    }
                }
            })
            ->field($queryFields);
        if (!empty($templateId)) {
            $is_so = true;
            $query->where('B.ID', $templateId);
        }
        if (!empty($executeStatus)) {
            $is_so = true;
            $query->where('A.EXECUTE_STATUS', $executeStatus);
        }
        if (!empty($keywords)) {
            $is_so = true;
            $fields = ['A.NAME', 'A.ID_CODE', 'A.REMARK'];
            $query->whereLike(implode('|', $fields), "%$keywords%");
        }
        $rows = $query->paginate(self::PAGE_SIZE, false, [
            'query' => request()->param()
        ])->each(function($item) {
            $item->DOMICILE_PLACE = $item->DOMICILE_PROVINCE_NAME . $item->DOMICILE_CITY_NAME . $item->DOMICILE_COUNTY_NAME
                . $item->DOMICILE_STREET_NAME . $item->DOMICILE_COMMUNITY_NAME . $item->DOMICILE_ADDRESS;
            $item->EXECUTE_STATUS = in_array($item->EXECUTE_STATUS, array_keys(TroubleshootingPersonModel::EXECUTE_STATUS_LIST)) ? TroubleshootingPersonModel::EXECUTE_STATUS_LIST[$item->EXECUTE_STATUS] : '未知';
            return $item;
        });
        $templateList = $this->getTemplateList();
        $this->assign('templateList', $templateList);
        $this->assign('executeStatusList', TroubleshootingPersonModel::EXECUTE_STATUS_LIST);
        $this->assign('list', $rows);
        $this->assign('page', $rows->render());
        $this->assign('total', $rows->total());
        $this->assign('is_so', $is_so);
        $this->assign('keywords', $keywords);
        $this->assign('templateId', $templateId);
        $this->assign('executeStatus', $executeStatus);
        $js = $this->loadJsCss(array('troubleshooting_person_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        return $this->fetch();
    }

    public function read(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error('非法操作');
        }
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.ID_CODE',
            'A.DOMICILE_PROVINCE_NAME',
            'A.DOMICILE_CITY_NAME',
            'A.DOMICILE_COUNTY_NAME',
            'A.DOMICILE_STREET_NAME',
            'A.DOMICILE_COMMUNITY_NAME',
            'A.DOMICILE_ADDRESS',
            'A.EXECUTOR_MOBILE',
            'A.EXECUTOR_NAME',
            'A.EXECUTE_TIME',
            'A.EXECUTE_STATUS',
            'A.CREATE_USER_NAME',
            'A.CREATE_TIME',
            'A.UPDATE_USER_NAME',
            'A.UPDATE_TIME',
            'A.REMARK',
            'B.NAME TEMPLATE_NAME'
        ];
        $info = TroubleshootingPersonModel::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->find($id);
        if (empty($info)) {
            $this->error("排查人员信息已删除或不存在");
        }
        $queryFields = [
            'A.ID',
            'A.FIELD_ID',
            'B.NAME FIELD_NAME',
            'B.WIDGET FIELD_WIDGET',
            'A.FIELD_VALUE'
        ];
        $extension = TroubleshootingPersonExtension::alias('A')
            ->leftJoin('troubleshoot_template_field B', 'A.FIELD_ID = B.ID')
            ->where('A.PERSON_ID', $id)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->select();
        $info->extension = $extension;
        foreach ($info->extension as $item) {
            if (empty($item->FIELD_VALUE)) {
                continue;
            }
            if (in_array($item->FIELD_WIDGET, TroubleshootingTemplateFieldModel::WIDGET_MULTI_MEDIA)) {
                $item->IS_MULTI_MEDIA = true;
                $value4View = [];
                $values = json_decode($item->FIELD_VALUE);
                foreach ($values as $value) {
                    $value4View[] = build_http_img_url($value->URL);
                }
                $item->FIELD_VALUE = $value4View;
            }
        }

        $info->DOMICILE_PLACE = $info->DOMICILE_PROVINCE_NAME . $info->DOMICILE_CITY_NAME . $info->DOMICILE_COUNTY_NAME
            . $info->DOMICILE_STREET_NAME . $info->DOMICILE_COMMUNITY_NAME . $info->DOMICILE_ADDRESS;
        $info->EXECUTE_STATUS = in_array($info->EXECUTE_STATUS, array_keys(TroubleshootingPersonModel::EXECUTE_STATUS_LIST)) ? TroubleshootingPersonModel::EXECUTE_STATUS_LIST[$info->EXECUTE_STATUS] : '未知';

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function create(Request $request) {
        if ($request->isPost()) {
            $data = [
                'TEMPLATE_ID' => $request->param('TEMPLATE_ID'),
                'CODE' => $request->param('CODE'),
                'NAME' => $request->param('NAME'),
                'WIDGET' => $request->param('WIDGET'),
                'NULLABLE' => $request->param('NULLABLE'),
                'SORT' => $request->param('SORT'),
                'DESC' => $request->param('DESC'),
                'CREATE_USER_ID' => session('user_id'),
                'CREATE_USER_NAME' => session('name'),
                'CREATE_TIME' => Carbon::now(),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now(),
                'EFFECTIVE' => EFFECTIVE
            ];
            $ver = new TroubleshootTemplateFieldVer();
            if (!$ver->scene('create')->check($data)) {
                $this->error($ver->getError());
            }
            $field = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE)
                ->where('TEMPLATE_ID', $data['TEMPLATE_ID'])
                ->where(function ($query) use ($data) {
                    $query->where('CODE', $data['CODE'])->whereOr('NAME', $data['NAME']);
                })->find();
            if (!empty($field)) {
                if ($field->CODE == $data['CODE']) {
                    $this->error('字段代码不能重复');
                }
                elseif ($field->NAME == $data['NAME']) {
                    $this->error('字段名称不能重复');
                }
            }
            TroubleshootingTemplateFieldModel::create($data);
            return $this->success('安保排查模板字段新增成功', url('TroubleshootingTemplateField/index'));
        }
        $templateList = $this->getTemplateList();
        $js = $this->loadJsCss(array('troubleshooting_template_field_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('templateList', $templateList);
        $this->assign('widgetList', self::WIDGET_LIST);
        return $this->fetch();
    }

    public function modify(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error('非法操作');
        }
        $info = TroubleshootingPersonModel::find($id);
        if (empty($info)) {
            $this->error("排查人员已删除或不存在");
        }
        if ($request->isPost()) {
            $data = [
                'NAME' => $request->param('NAME'),
                'ID_CODE' => $request->param('ID_CODE'),
                'DOMICILE_COUNTY_CODE' => $request->param('DOMICILE_COUNTY_CODE'),
                'DOMICILE_STREET_CODE' => $request->param('DOMICILE_STREET_CODE'),
                'DOMICILE_COMMUNITY_CODE' => $request->param('DOMICILE_COMMUNITY_CODE'),
                'DOMICILE_ADDRESS' => $request->param('DOMICILE_ADDRESS'),
                'REMARK' => $request->param('REMARK'),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now(),
                'UPDATE_TERMINAL' => TERMINAL_WEB
            ];
            $ver = new TroubleshootPersonVer();
            if (!$ver->scene('web.modify')->check($data)) {
                $this->error($ver->getError());
            }
            $personCount = TroubleshootingPersonModel::where('EFFECTIVE', EFFECTIVE)
                ->where('ID_CODE', $data['ID_CODE'])
                ->where('ID', '<>', $id)
                ->count();
            if ($personCount > 0) {
                $this->error("重复的被排除人员身份证号码");
            }
            $areaNames = Subareas::where('CODE12', 'in', [$data['DOMICILE_COUNTY_CODE'], $data['DOMICILE_STREET_CODE'], $data['DOMICILE_COMMUNITY_CODE']])
                ->order('CODE12 ASC')
                ->column('NAME');
            $data['DOMICILE_COUNTY_NAME'] = count($areaNames) >= 1 ? $areaNames[0] : '';
            $data['DOMICILE_STREET_NAME'] = count($areaNames) >= 2 ? $areaNames[1] : '';
            $data['DOMICILE_COMMUNITY_NAME'] = count($areaNames) >= 3 ? $areaNames[2] : '';
            $info->save($data);
            return $this->success('安保排查人员信息修改成功', url('TroubleshootingPerson/index'));
        }
        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'troubleshooting_person_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        return $this->fetch('create');
    }

    private function getTemplateList() {
        return create_kv(TroubleshootingTemplateModel::where('EFFECTIVE', EFFECTIVE)->all(), 'ID', 'NAME');
    }

    public function delete(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error("非法操作");
        }
        $info = TroubleshootingPersonModel::find($id);
        if (empty($info)) {
            $this->error("排查人员信息已删除或不存在");
        }
        $info->EFFECTIVE = INEFFECTIVE;
        $info->save();
        $this->success('安保排查人员信息删除成功');
    }

    public function assigns(Request $request){
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error("非法操作");
        }
        $info = TroubleshootingPersonModel::find($id);
        if (!$info) {
            $this->error('排查人员已删除或不存在');
        }
        $powerLevel = $this->getPowerLevel();
        if ($request->isPost()) {
            $countyCode = $request->param('COUNTY_CODE', '', 'trim');
            $streetCode = $request->param('STREET_CODE', '', 'trim');
            $communityCode = $request->param('COMMUNITY_CODE', '', 'trim');

            $areaNames = Subareas::where('CODE12', 'in', [$countyCode, $streetCode, $communityCode])
                ->order('CODE12 ASC')
                ->column('NAME');
            $countyName = count($areaNames) >= 1 ? $areaNames[0] : '';
            $streetName = count($areaNames) >= 2 ? $areaNames[1] : '';
            $communityName = count($areaNames) >= 3 ? $areaNames[2] : '';

            $reason = $request->param('REASON', '', 'trim');
            $action = $request->param('ACTION', TroubleshootingAssignment::ACTION_ASSIGN, 'trim');
            $assignment = new TroubleshootingAssignment();
            $assignment->PERSON_ID = $id;
            $assignment->COUNTY_CODE = $countyCode;
            $assignment->COUNTY_NAME = $countyName;
            $assignment->STREET_CODE = $streetCode;
            $assignment->STREET_NAME = $streetName;
            $assignment->COMMUNITY_CODE = $communityCode;
            $assignment->COMMUNITY_NAME = $communityName;
            $assignment->REASON = $reason;
            $assignment->ACTION = $action;
            $assignment->CREATE_USER_ID = session('user_id');
            $assignment->CREATE_USER_NAME = session('name');
            $assignment->CREATE_TIME = Carbon::now();
            $assignment->save();
            $this->jsalert("排查人员指派成功",3);
        }
        $assignment = TroubleshootingAssignment::where(['PERSON_ID' => $id])->order("CREATE_TIME DESC")->limit(1)->select();
        if ($assignment->isEmpty()) {
            $assignment = new TroubleshootingAssignment();
            $assignment->COUNTY_CODE = $info->DOMICILE_COUNTY_CODE;
            $assignment->STREET_CODE = $info->DOMICILE_STREET_CODE;
            $assignment->COMMUNITY_CODE = $info->DOMICILE_COMMUNITY_CODE;
        } else {
            $assignment = $assignment[0];
        }
        $info->DOMICILE_PLACE = $info->DOMICILE_PROVINCE_NAME . $info->DOMICILE_CITY_NAME . $info->DOMICILE_COUNTY_NAME
            . $info->DOMICILE_STREET_NAME . $info->DOMICILE_COMMUNITY_NAME . $info->DOMICILE_ADDRESS;
        $js = $this->loadJsCss(array('p:cate/jquery.cate','troubleshooting_person_assign'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        $this->assign('assignment', $assignment);
        $this->assign('powerLevel', $powerLevel);
        return $this->fetch('assign');
    }

    protected function getManageWhere() {
        $where = [];
        $superadmin = session('superadmin');
        if ($superadmin) {
            return $where;
        }
        $power_level = session('power_level');
        $admin = session('info');
        // 市级
        if ($power_level == self::POWER_LEVEL_CITY) {
            return $where;
        }
        // 县级
        elseif ($power_level == self::POWER_LEVEL_COUNTY) {
            $where['COUNTY_CODE'] = $admin['POWER_COUNTY_ID_12'];
        }
        // 乡级
        elseif ($power_level == self::POWER_LEVEL_STREET) {
            $where['STREET_CODE'] = $admin['POWER_STREET_ID'];
        }
        // 村级
        elseif ($power_level == self::POWER_LEVEL_COMMUNITY) {
            $where['COMMUNITY_CODE'] = $admin['POWER_COMMUNITY_ID'];
        }
        return $where;
    }

}
