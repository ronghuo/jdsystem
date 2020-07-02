<?php

namespace app\htsystem\controller;

use app\common\validate\TroubleshootTemplateFieldVer;
use Carbon\Carbon;
use think\Request;
use app\common\model\TroubleshootingTemplateField as TroubleshootingTemplateFieldModel;
use app\common\model\TroubleshootingTemplate as TroubleshootingTemplateModel;

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
        $keywords = $request->param('keywords', '', 'trim');
        $query = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE);
        if (!empty($templateId)) {
            $is_so = true;
            $query->where('TEMPLATE_ID', $templateId);
        }
        if (!empty($keywords)) {
            $is_so = true;
            $fields = ['CODE', 'NAME', 'DESC'];
            $query->whereLike(implode('|', $fields), "%$keywords%");
        }
        $rows = $query->with([
            'template' => function($query) {
                return $query->field('ID, NAME');
            }
        ])->paginate(self::PAGE_SIZE, false, [
            'query' => request()->param()
        ])->each(function($item) {
            $item->NULLABLE = $item->NULLABLE == 'Y' ? '是' : '否';
            $item->WIDGET = in_array($item->WIDGET, array_keys(self::WIDGET_LIST)) ? self::WIDGET_LIST[$item->WIDGET] : '其它';
            return $item;
        });
        $templateList = $this->getTemplateList();
        $this->assign('templateList', $templateList);
        $this->assign('list', $rows);
        $this->assign('page', $rows->render());
        $this->assign('total', $rows->total());
        $this->assign('is_so', $is_so);
        $this->assign('keywords', $keywords);
        $this->assign('templateId', $templateId);
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
        $info = TroubleshootingTemplateFieldModel::find($id);
        if (empty($info)) {
            $this->error("字段已删除或不存在");
        }
        $info->EFFECTIVE = INEFFECTIVE;
        $info->save();
        $this->success('安保排查模板删除成功');
    }

}
