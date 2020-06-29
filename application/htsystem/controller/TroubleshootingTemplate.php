<?php

namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Request;
use app\common\model\TroubleshootingTemplate as TroubleshootingTemplateModel;

class TroubleshootingTemplate extends Common
{

    public function index(Request $request) {
        $is_so = false;
        $keywords = $request->param('keywords', '', 'trim');
        $query = TroubleshootingTemplateModel::where('EFFECTIVE', EFFECTIVE);
        if (!empty($keywords)) {
            $is_so = true;
            $fields = ['NAME', 'REMARK'];
            $query->whereLike(implode('|', $fields), "%$keywords%");
        }
        $rows = $query->paginate(self::PAGE_SIZE, false, [
            'query' => request()->param()
        ])->each(function($item) {
            $item->EFFECTIVE = $item->EFFECTIVE == EFFECTIVE ? '有效' : '无效';
            return $item;
        });
        $this->assign('list', $rows);
        $this->assign('page', $rows->render());
        $this->assign('total', $rows->total());
        $this->assign('is_so', $is_so);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    public function create(Request $request) {
        if ($request->isPost()) {
            $data = [
                'NAME' => $request->param('NAME'),
                'REMARK' => $request->param('REMARK'),
                'CREATE_USER_ID' => session('user_id'),
                'CREATE_USER_NAME' => session('name'),
                'CREATE_TIME' => Carbon::now(),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now(),
                'EFFECTIVE' => EFFECTIVE
            ];
            TroubleshootingTemplateModel::create($data);
            return $this->success('安保排查模板新增成功', url('TroubleshootingTemplate/index'));
        }
        $js = $this->loadJsCss(array('troubleshooting_template_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        return $this->fetch();
    }

    public function delete(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error("非法操作");
        }
        $info = TroubleshootingTemplateModel::find($id);
        if (empty($info)) {
            $this->error("模板已删除或不存在");
        }
        $info->EFFECTIVE = INEFFECTIVE;
        $info->save();
        $this->success('安保排查模板删除成功');
    }

}
