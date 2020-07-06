<?php

namespace app\htsystem\controller;

use app\common\model\TroubleshootingPersonExtension;
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
        $query = TroubleshootingPersonModel::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
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
            ->find();
        if (empty($info)) {
            $this->error("排查人员信息已删除或不存在");
        }
        $queryFields = [
            'A.ID',
            'A.FIELD_ID',
            'B.NAME FIELD_NAME',
            'A.FIELD_VALUE'
        ];
        $extension = TroubleshootingPersonExtension::alias('A')
            ->leftJoin('troubleshoot_template_field B', 'A.FIELD_ID = B.ID')
            ->where('A.PERSON_ID', $id)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->select();
        $info->extension = $extension;

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
        $info = TroubleshootingTemplateFieldModel::find($id);
        if (empty($info)) {
            $this->error("字段已删除或不存在");
        }
        if ($request->isPost()) {
            $data = [
                'CODE' => $request->param('CODE'),
                'NAME' => $request->param('NAME'),
                'WIDGET' => $request->param('WIDGET'),
                'NULLABLE' => $request->param('NULLABLE'),
                'SORT' => $request->param('SORT'),
                'DESC' => $request->param('DESC'),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now()
            ];
            $ver = new TroubleshootTemplateFieldVer();
            if (!$ver->scene('modify')->check($data)) {
                $this->error($ver->getError());
            }
            $field = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE)
                ->where('TEMPLATE_ID', $info->TEMPLATE_ID)
                ->where('ID', '<>', $id)
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
            $info->save($data);
            return $this->success('安保排查模板字段修改成功', url('TroubleshootingTemplateField/index'));
        }
        $templateList = $this->getTemplateList();
        $js = $this->loadJsCss(array('troubleshooting_template_field_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('templateList', $templateList);
        $this->assign('info', $info);
        $this->assign('widgetList', self::WIDGET_LIST);
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

}
