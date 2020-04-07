<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-14
 * Time: 15:45
 */
namespace app\htsystem\controller;
use app\htsystem\model\Systable as mSystable;
use think\Request;

class Syspower extends Common{

    /**
     * @var mSystable
     */
    protected $admin_model;


    /**
     * 菜单排序
     */
    public function menuSort(Request $request){

        if($request->isPost()){
            return $this->do_menu_post($request);
        }
//        $this->admin_model = new mSystable();
        $groups = (new mSystable())->get_node_group_list([],'*',0);

        $js = $this->loadJsCss(array( 'p:dragSort/jquery.dragsort-052','syspower_menusort'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('groups', $groups);
        return $this->fetch('menusort');
    }

    protected function do_menu_post(Request $request){
        $acts = ['getsub','savesort'];
        $act = input('post.act','');
        if(!$act || !in_array($act,$acts)){
            return ['err'=>1,'mesg'=>'访问错误'];
        }
        $admin_model = new mSystable();
        switch($act){
            case 'getsub':
                $isg = input('post.isg',0);
                $pid = input('post.pid',0);
                if(!$pid){
                    return ['err'=>1,'mesg'=>'访问错误'];
                }

                $w = ['ISMENU'=>'1','STATUS'=>'1'];
                if($isg){
                    $w['GID'] = $pid;
                    $w['LEVEL'] = '2';
                }else{
                    $w['PID'] = $pid;
                }
                $list = $admin_model->get_node_list($w,'ID,TITLE AS NAME,ICON',0);
                return ['err'=>0,'data'=>$list];

                break;
            case 'savesort':
                $post = input('post.');
                $ids = $post['ids'];
                $isg = $post['isg'] + 0;
                //print_r($post);exit;
                if(empty($ids)){
                    return ['err'=>1,'mesg'=>'访问错误'];
                }

                foreach($ids as $k=>$id){
                    if($isg==1){
                        $admin_model->update_node_group(['ID'=>$id],['SORT'=>$k]);
                    }else{
                        $admin_model->update_node(['ID'=>$id],['SORT'=>$k]);
                    }
                }
                return ['err'=>0];
                break;
        }
    }

    //*********************************************权限分组********************************************************
    /**
     * 权限分组
     */
    public function group(Request $request) {
        $where = [];
        $admin_model = new mSystable();
        $count = $admin_model->get_node_group_count($where);
        $data = $page = [];
        if($count>0){
            $page = $this->_pagenav($count);
            $field = 'ID,NAME,ICON,LINK';
            $data = $admin_model->get_node_group_list($where,$field,$page['offset'].','.$page['limit']);
        }
        $js = $this->loadJsCss(array( 'syspower_group'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('pagenav',$page);
        $this->assign('data',$data);
        return $this->fetch('group');
    }

    /**
     * 添加/编辑分组
     */
    public function groupInfo(Request $request,$gid = 0) {

        $admin_model = new mSystable();

        if($request->isPost()){
            $res = $admin_model->save_group_data($request->post());
            if($res){
                $this->jsalert('保存成功',3);
            }
            $this->error('保存失败');
        }
        $info = [];
        if($gid>0){
            $info = $admin_model->get_node_group_info(['ID'=>$gid]);
            if(!$info){
                if(!$info){
                    $this->error('该分组信息不存在');
                }
            }
        }

        $js = $this->loadJsCss(array( 'syspower_group'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        return $this->fetch('groupinfo');
    }

    /**
     * 删除分组
     */
    public function groupDel($gid = 0) {
        if(!$gid || $gid<=0){
            $this->error('访问错误');
        }
        $admin_model = new mSystable();
        $has = $admin_model->get_node_group_count(['ID'=>$gid]);
        if($has<=0){
            $this->error('不存在在该分组或已删除');
        }
        $res = $admin_model->del_node_group($gid);
        if($res){
            $this->success('删除成功','syspower/group');
        }
        $this->error('删除失败');
    }

    /**
     * 分组的其他操作
     */
    public function groupOpers(){

    }

    //*********************************************权限节点********************************************************
    /**
     * 权限列表
     */
    public function nodeList() {

        $where = [];
        $admin_model = new mSystable();
        $count = $admin_model->get_node_count($where);
        $data = $page = [];
        if($count>0){
            //$page = $this->_pagenav($count);
            $all_nodes = $admin_model->get_node_list($where,'ID,TITLE,NAME,PID,LEVEL,STATUS',0);
            //用数组切割方式进行分页
            //$data = array_slice(create_tree($all_nodes), $page['offset'], $page['limit']);
            $data = create_tree($all_nodes);
            unset($nodelist);
        }

        $js = $this->loadJsCss(array('p:common/common'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('pagenav',$page);
        $this->assign('data',$data);
        $this->assign('ntype', $this->nodeType());
        return $this->fetch('nodelist');
    }

    /**
     * 添加权限节点
     */
    public function nodeAdd(Request $request,$nid = 0) {
        if($request->isPost()){
            return $this->save_node_data($request);
        }
        $admin_model = new mSystable();
        $pids = $admin_model->get_node_pids();
        $pids = create_tree($pids);
        $groups = $admin_model->get_node_groups();
        $js   = $this->loadJsCss(array( 'syspower_node'), 'js', 'admin');

        $this->assign('footjs', $js);
        $this->assign('groups', $groups);
        $this->assign('pids', $pids);
        $this->assign('ntype', $this->nodeType());
        return $this->fetch('nodeadd');
    }

    /**
     * 编辑权限节点
     */
    public function nodeEdit(Request $request,$nid = 0) {
        if($request->isPost()){
            return $this->save_node_data($request,1);
        }
        if(!$nid || $nid<=0){
            $this->error('访问错误');
        }
        $admin_model = new mSystable();
        $info = $admin_model->get_node_info(['ID'=>$nid]);
        if(!$info){
            $this->error('该节点信息不存在');
        }
        $pids = $admin_model->get_node_pids();
        $pids = create_tree($pids);
        $groups = $admin_model->get_node_groups();
        $js   = $this->loadJsCss(array( 'syspower_node'), 'js', 'admin');

        $this->assign('footjs', $js);
        $this->assign('groups', $groups);
        $this->assign('pids', $pids);
        $this->assign('info', $info);
        $this->assign('ntype', $this->nodeType());
        $this->assign('ref', get_ref(1));
        return $this->fetch('nodeedit');
    }

    /**
     * 删除权限节点
     */
    public function nodeDel($nid = 0) {
        if(!$nid || $nid<=0){
            $this->error('访问错误');
        }
        $admin_model = new mSystable();
        $childrens = $admin_model->get_node_count(['PID'=>$nid]);
        if($childrens>0){
            $this->error('删除的节点中还含有子节点，不能删除！');
        }
        $res = $admin_model->del_node($nid);
        if($res){
            $this->success('删除节点数据成功');
        }
        $this->error('删除节点数据失败');
    }

    /**
     * 节点其他操作
     */
    public function nodeopers(){

    }

//*********************************************角色********************************************************

    /**
     * 角色列表
     */
    public function roleList(){
        $where = [];
        $admin_model = new mSystable();
        $count = $admin_model->get_role_count($where);
        $data = $page = [];
        if($count>0){
            $page = $this->_pagenav($count);
            $field = 'ID,NAME,STATUS,REMARK';
            $data = $admin_model->get_role_list($where,$field,$page['offset'].','.$page['limit']);
        }
        $js = $this->loadJsCss(array('p:common/common'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('pagenav',$page);
        $this->assign('data',$data);
        return $this->fetch('rolelist');
    }


    /**
     * 添加角色
     */
    public function roleAdd(Request $request,$rid=0){
        if($request->isPost()){
            return $this->save_role_data($request);
        }
        $admin_model = new mSystable();
        $info = [];
        if($rid>0){
            $info = $admin_model->get_role_info(['ID'=>$rid]);
            if(!$info){
                $this->error('该角色信息不存在');
            }
            $acc = $admin_model->get_access_list(['ROLE_ID'=>$rid],'NODE_ID');
            $info['acc'] = array2to1($acc,'NODE_ID');
        }
        $allnodes = create_level_tree($admin_model->get_all_nodes());

        $js = $this->loadJsCss(array('syspower_role'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        $this->assign('nodes', $allnodes[0]);
        return $this->fetch('roleadd');
    }

    /**
     * 编辑角色
     */
    public function roleEdit(Request $request,$rid=0){
        if($rid<=0){
            $this->error('访问错误');
        }
        return $this->roleadd($request,$rid);
    }
    /**
     * 删除角色
     */
    public function roleDel($rid=0){
        if($rid<=0){
            $this->error('访问错误');
        }

        (new mSystable())->del_role($rid);
        $this->success('删除成功');
    }


    /**========================内部方法=================================**/
    /**
     * 节点类型
     * @return array
     */
    protected function nodeType() {
        return array(1 => '项目', 2 => '模块', 3 => '操作');
    }

    /**
     * 保存添加节点数据
     */
    protected function save_node_data(Request $request,$isedit = false) {
        $post = $request->post();
        $ref = isset($post['ref'])?base64_decode($post['ref']):url('Syspower/nodeList');

        $admin_model = new mSystable();
        if ($isedit) {
            unset($post['ref']);
            $res = $admin_model->save_edit_node($post);
        } else {
            $res = $admin_model->save_add_node($post);
        }

        if ($res['err'] != 0) {
            $this->error($res['mesg']);
        }

        $this->success($res['mesg'], $ref);
    }

    protected function save_role_data(Request $request){
        $post = $request->post();
        $acc = $post['acc'];
        unset($post['acc']);
        $admin_model = new mSystable();
        $role_id = $admin_model->save_role_data($post);
        if(!$role_id){
            $this->error('保存失败');
        }
        $admin_model->save_access_date($role_id,$acc);
        $this->success('保存成功',url('Syspower/roleList'));
    }

}