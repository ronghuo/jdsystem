<?php

namespace app\htsystem\controller;


use think\Request;
use app\htsystem\model\Admins;
use app\htsystem\model\Syspower;
use think\captcha\Captcha;

class Login extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        if($request->isPost()){

            return $this->login($request);
        }

        //$js = $this->loadJsCss(array('login'), 'js', 'admin');
        //$this->assign('footjs', $js);
        return $this->fetch();
    }

    public function out(Request $request){
        cookie('mcuser',null);
        session(null);
        session_destroy();
        $this->redirect('Login/index');
    }

    public function verify()
    {
        $config =    [
            'fontSize'    =>    30,
            'length'      =>    4,
            'useNoise'    =>    false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    protected function login(Request $request){

        $username = $request->param('uname','','trim');
        $pwd = $request->param('pwsd','','trim');
        $verify = $request->param('verify','','trim');
        $jump = $request->param('ref') ? : url('Index/index');


        if(!$username || !$pwd || !$verify){
            return ['err'=>1,'mesg'=>'信息有误，请重试'];
        }
        $ref =get_ref();
        if(strpos($ref,'.local')===false && !captcha_check($verify)){
            return ['err'=>1,'mesg'=>'验证码不正确'];
        }
        $user = Admins::field('ID AS USER_ID,LOG,PWD,STAT,ROLE_ID,NAME,GENDER,STATUS,LOGINIP,LGTIME,POWER_COMMUNITY_ID,POWER_STREET_ID,POWER_COUNTY_ID_12,POWER_CITY_ID,DMMCIDS')
            ->where(['LOG'=>$username,'IS_WORK'=>1,'ISDEL'=>0])
            ->find();
        //$user = $this->admin_model->get_admin_info(['log'=>$username,'is_work'=>1,'isdel'=>0],'id as user_id,cpid,log,pwd,stat,role_id,name,sex,status,depart_id,loginip,lgtime');
        if(!$user){
            return ['err'=>1,'mesg'=>'账号不存在'];
        }
        if($user->STATUS!=1 || $user->ROLE_ID==0){
            return ['err'=>1,'mesg'=>'账号不可用，请与管理员联系'];
        }
        $in_pwd = create_pwd($pwd,$user->STAT);
        if($in_pwd !== $user->PWD){
            return ['err'=>1,'mesg'=>'密码不正确'];
        }

        //检查所属角色状态
//        if(!$this->admin_model->get_role_count(['id'=>$user['role_id'],'status'=>1])){
//            return ['err'=>1,'mesg'=>'账号不可用，请与管理员联系'];
//        }
        //检查账号角色权限
        $accNodeCount = (new Syspower())->get_my_node_count($user->ROLE_ID);
        if($accNodeCount ==0){
            return ['err'=>1,'mesg'=>'无权登录'];
        }


        $user->LOGINIP = $request->ip();
        $user->LGTIME = \Carbon\Carbon::now()->toDateTimeString();
        $user->save();
        unset($user->STAT,$user->PWD,$pwd);

        //记录到session
        $this->login_session($user);
        //\app\gerent\model\Adminoperlog::instance()->save_data('登录后台系统');

        return ['err'=>0,'mesg'=>'登录成功！','url'=>$jump];

    }


    protected function login_session(Admins $user){

        $powerLevel = 0;
        if($user->POWER_COMMUNITY_ID > 0){
            $powerLevel = 4;
        }elseif($user->POWER_STREET_ID > 0){
            $powerLevel = 3;
        }elseif($user->POWER_COUNTY_ID_12 > 0) {
            $powerLevel = 2;
        }elseif($user->POWER_CITY_ID > 0){
            $powerLevel = 1;
        }
        session('power_level', $powerLevel);
        cookie('mcuser',$user->USER_ID,config('app.admin_lgn_status_expire'));

        session('username', $user->LOG);
        session('name', $user->NAME);
        session('user_id', $user->USER_ID);
        session('superadmin', false);
        //是否为超级管理员
        if ($user->ROLE_ID == config('rbac.rbac_superman_id')) {
            session('superadmin', true);
        }

        session('info',$user->toArray());
        //引入RBAC,保存登录者的权限列表
        $acc_info = (new Syspower())->get_my_node_info($user->ROLE_ID);
        session('_ACCESS_LIST', $acc_info['acc_list']);
        session('acc_ids', $acc_info['acc_ids']);
        session('gids', $acc_info['gids']);
        session('gid_node_ids', $acc_info['gid_node_ids']);
        //return true;
    }
}
