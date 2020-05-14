<?php

namespace app\htsystem\controller;

use app\common\model\NbAuthDept;
use app\htsystem\model\Admins;
use app\htsystem\model\Systable as mSystable;
use Carbon\Carbon;
use think\helper\Str;
use think\Request;

class SystemUsers extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {

        $sop = $this->doSearch();

        $list = $sop['query']->order('ID','desc')
            ->where(function ($query) {
               $where = $this->getAdminWhere();
               foreach ($where as $field => $value) {
                   $query->where($field, $value);
               }
            })
            ->paginate(self::PAGE_SIZE, false, [
                'query' => request()->param()
            ])->each(function($item, $key) {
                $item->HEAD_IMG_URL = build_http_img_url($item->HEAD_IMG);
        });


        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'systemusers_index'), 'js', 'admin');
        $css = $this->loadJsCss(array('systemusers_index'), 'css', 'admin');
        $this->assign('footjs', $js);
        $this->assign('headercss', $css);
        $this->assign('list',$list);
        $this->assign('total', $list->total());
        $this->assign('is_so', $sop['is_so']);
        $this->assign('param', $sop['p']);
        $this->assign('powerLevel', $this->getPowerLevel());
        return $this->fetch();
    }

    protected function doSearch(){

        $p = [];
        $fields = ['ID', 'MOBILE', 'NAME'];
        $p['keywords'] = input('get.keywords','');
        $is_so = false;
        $query = Admins::where('isdel',0);

        if (!empty($p['keywords'])) {
            $query->where(implode('|', $fields), 'like', '%' . $p['keywords'] . '%');
            $is_so = true;
        }

        $powerLevel = $this->getPowerLevel();
        $admin = session('info');
        $dmmcs = explode(',', $admin['DMMCIDS']);
        if (self::POWER_LEVEL_COUNTY == $powerLevel) {
            $p['a1'] = $dmmcs[0];
            $p['a2'] = $dmmcs[1];
            $p['a3'] = input('area3', '');
            $p['a4'] = input('area4', '');
        }
        elseif (self::POWER_LEVEL_STREET == $powerLevel) {
            $p['a1'] = $dmmcs[0];
            $p['a2'] = $dmmcs[1];
            $p['a3'] = $dmmcs[2];
            $p['a4'] = input('area4', '');
        }
        elseif (self::POWER_LEVEL_COMMUNITY == $powerLevel) {
            $p['a1'] = $dmmcs[0];
            $p['a2'] = $dmmcs[1];
            $p['a3'] = $dmmcs[2];
            $p['a4'] = isset($dmmcs[3]) ? $dmmcs[3] : '';
        } else {
            $p['a1'] = input('area1', $dmmcs[0]);
            $p['a2'] = input('area2', '');
            $p['a3'] = input('area3', '');
            $p['a4'] = input('area4', '');
        }

        if ($p['a4'] > 0) {
            $query->where('DMMCIDS', 'like', implode(',', [$p['a1'], $p['a2'], $p['a3'], $p['a4']]).'%');
            $is_so = true;
        }
        elseif ($p['a3'] > 0) {
            $query->where('DMMCIDS', 'like', implode(',', [$p['a1'], $p['a2'], $p['a3']]).'%');
            $is_so = true;
        }
        elseif ($p['a2'] > 0) {
            $query->where('DMMCIDS', 'like', implode(',', [$p['a1'], $p['a2']]).'%');
            $is_so = true;
        }

        return ['query' => $query, 'p' => $p, 'is_so' => $is_so];
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request, $id = 0)
    {

        if ($request->isPost()) {
            return $this->save($request);
        }
        $info = [];
        if ($id > 0) {
            $info = Admins::find($id);

            if(!$info){
                $this->error('该用户不存在');
            }
            $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
            $dmmcids = explode(',',$info->DMMCIDS);
            $info->DMMCIDS = fillArrayToLen($dmmcids,4);

            $powerids = explode(',',$info->POWER_IDS);
            $info->POWER_IDS = fillArrayToLen($powerids,3);
        }
        $admin = session('info');
        $lv1Value = explode(',', $admin['DMMCIDS'])[0];

        $admin_model = new mSystable();

        $roles = $admin_model->get_role_list(['STATUS'=>1],'ID,NAME,REMARK');


        $js = $this->loadJsCss(array('p:cate/jquery.cate','systemusers_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('roles',$roles);
        $this->assign('info',$info);
        $this->assign('lv1Value', $lv1Value);
        return $this->fetch('create');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request,$id=0)
    {
        //
        if(!$id){
            $this->error('访问错误');
        }
        $info = Admins::find($id);
        //$info->gender_text;
        $info->HEAD_IMG_URL = build_http_img_url($info->HEAD_IMG);
        $this->assign('info',$info);
        return $this->fetch('read');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request,$id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        return $this->create($request,$id);
    }



    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }

        $admin = Admins::find($id);
        if(!$admin || $admin->ISDEL==1){
            $this->error('该用户不存在或已删除');
        }
        $admin->ISDEL= 1;
        $admin->DEL_TIME = Carbon::now()->toDateTimeString();
        $admin->save();

        $this->success('删除成功');
    }




    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    protected function save(Request $request)
    {
        $ref = $request->post('ref') ? : url('SystemUsers/index');

        $id = $request->post('ID',0,'int');
        $isNew = $id == 0;

        $role = $request->post('role','','trim');
        if (empty($role)) {
            $this->error('请选择管理权限角色');
        }
        $role = explode('-', $request->post('role','','trim'));

        $dmmcids = $request->param('dmmc', [], 'trim');
        $dmmcids = array_filter($dmmcids);
        if (empty($dmmcids) || empty($dmmcids[1])) {
            $this->error('请选择单位');
        }
        $dmmc = NbAuthDept::find(end($dmmcids));
        if (!$dmmc) {
            $this->error('缺少所属禁毒办信息');
        }

        $log = $request->param('LOG','','trim');
        if ($isNew) {
            $admin = Admins::where(['LOG' => $log, 'ISDEL' => 0])->find();
            if (!empty($admin)) {
                $this->error('账号已经被使用');
            }
        } else {
            $admin = Admins::where(['LOG' => $log, 'ISDEL' => 0])->find();
            if (!empty($admin) && $admin->ID != $id) {
                $this->error('账号已经被使用');
            }
        }

        $mobile = $request->param('MOBILE','','trim');
        if ($isNew) {
            $admin = Admins::where(['MOBILE' => $mobile, 'ISDEL' => 0])->find();
            if (!empty($admin)) {
                $this->error('手机号码已经被使用');
            }
        } else {
            $admin = Admins::where(['MOBILE' => $mobile, 'ISDEL' => 0])->find();
            if (!empty($admin) && $admin->ID != $id) {
                $this->error('手机号码已经被使用');
            }
        }

        $pwd = $request->post('PWD','','trim');
        $stat = Str::random(5);

        $power = $request->param('power','','trim');
        $power = array_filter($power);

        $data = [
            'LOG' => $log,
            'MOBILE' => $mobile,
            'ROLE_ID' => $role[0],
            'ROLE' => $role[1],
            'NAME' => $request->param('NAME','','trim'),
            'GENDER' => $request->param('GENDER','','trim'),
            'DMMC_ID' => $dmmc->ID,
            'DMMC_NAME' => $dmmc->DEPTDESC,
            'POST' => $request->param('POST','','trim'),
            'CONTACT' => $request->param('CONTACT','','trim'),
            'REMARK' => $request->param('REMARK','','trim'),
            'POWER_CITY_ID' => 431200,
            'POWER_COUNTY_ID' => isset($power[0]) ? substr($power[0], 0, 6) : 0,
            'POWER_COUNTY_ID_12'=> $power[0] ??   0,
            'POWER_STREET_ID' => $power[1] ??   0,
            'POWER_COMMUNITY_ID' => $power[2] ??   0,
            'POWER_IDS' => implode(',',$power),
            'DMMCIDS' => implode(',', $dmmcids)
        ];

        if ($request->has('PWD')) {
            $data['PWD'] = create_pwd($pwd,$stat);
            $data['STAT'] = $stat;
        }

        if ($isNew) {
            $id = (new Admins())->insertGetId($data);
        } else {
            Admins::where('ID', $id)->update($data);
        }

        $img = $this->uploadImage($request, ['sysusers/']);
        if (isset($img['images'])) {
            Admins::where('ID','=', $id)->update(['HEAD_IMG' => $img['images'][0]]);
        }

        $this->success('保存用户信息成功',$ref);

    }

    public function changePwd(Request $request, $id = 0) {
        if ($request->isPost()) {

            $id = $request->post('ID',0);
            $pwd = $request->post('PWD','');

            if (!$id || !$pwd) {
                $this->error('提交数据不能为空');
            }

            // 校验密码长度
            $length = strlen($pwd);
            if ($length < 6) {
                $this->error('密码长度不得少于6位');
            }
            $admin = Admins::where('ISDEL',0)->find($id);
            if (!$admin) {
                $this->error('用户信息不存在或已删除');
            }

            if (!$this->checkAdminid($admin->ID)) {
                $this->error('权限不足');
            }

            $stat = Str::random(5);
            $admin->PWD = create_pwd($pwd,$stat);
            $admin->STAT = $stat;
            $admin->save();

            $this->jsalert('修改密码成功',7);
            return ;
        }


        if (!$id) {
            $this->error('访问错误');
        }
        $admin = Admins::where('ISDEL', 0)->find($id);
        if (!$admin) {
            $this->error('用户信息不存在或已删除');
        }

        if (!$this->checkAdminid($admin->ID)) {
            $this->error('权限不足');
        }

        $js = $this->loadJsCss(array( 'systemusers_change_pwd'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('user', $admin);
        return $this->fetch('change_pwd');
    }

}
