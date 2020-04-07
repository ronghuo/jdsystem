<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-13
 * Time: 20:15
 */
namespace app\htsystem\model;

use think\Db;
class Systable extends  Base{
    protected $admin_table = 'ADMINS';
//    protected $depart_table = 'admin_department';
    protected $node_table = 'ADMIN_NODES';
    protected $node_group_table = 'ADMIN_NODE_GROUPS';
    protected $acc_table = 'ADMIN_ACCESS';
    protected $role_table = 'ADMIN_ROLES';
//    protected $role_user_table = 'admin_role_user';

    //=====================admin=======================

    public function get_admin_list($where,$field='*',$limit=15){
        return $this->_get_list($this->admin_table,$where,['ID'=>'desc'],$field,$limit);
    }

    public function get_admin_count($where){
        return $this->_get_count($this->admin_table,$where);
    }

    public function get_admin_info($where,$field='*'){
        return $this->_get_info($this->admin_table,$where,$field);
    }

    public function save_admin_data($data){
        if(isset($data['ID']) && $data['ID']>0){
            return Db::table($this->admin_table)->where(['ID'=>$data['ID']])->update($data);
        }else{
            $data['ADD_TIME'] = time();
            $data['PWD'] = create_pwd($data['PWD'],$data['STAT']);
            return Db::table($this->admin_table)->insert($data);
        }
    }

    public function update_admin($where,$update){
        return $this->_update_data($this->admin_table,$where,$update);
    }

    public function del_admin($id){
        return $this->update_admin(['ID'=>$id],['ISDEL'=>1]);
    }

    //============================admin_department

    public function get_depart_list($where,$field='*',$limit=15,$kv=false){
        $list = $this->_get_list($this->depart_table,$where,['ID'=>'asc'],$field,$limit);
        if(!$kv){
            return $list;
        }
        return create_kv($list,'ID','NAME');
    }

    public function get_depart_count($where){
        return $this->_get_count($this->depart_table,$where);
    }

    public function get_depart_info($where,$field='*'){
        return $this->_get_info($this->depart_table,$where,$field);
    }

    public function save_depart_data($data){
        if(isset($data['ID']) && $data['ID']>0){
            return Db::table($this->depart_table)->where(['ID'=>$data['ID']])->update($data);
        }else{
            $data['ADD_TIME'] = time();
            return Db::table($this->depart_table)->insert($data);
        }
    }

    public function del_depart($where){
        return $this->_del_row($this->depart_table,$where);
    }

    //=============================admin_node

    public function get_all_nodes(){
        return $this->_get_list($this->node_table,['STATUS'=>1],['LEVEL'=>'asc'],'ID,TITLE,LEVEL,PID',0);
    }
    /**
     * 获取菜单项
     * @return array
     */
    public function get_node_pids(){
        return $this->_get_list($this->node_table,['ISMENU'=>1,'STATUS'=>1],['LEVEL'=>'asc','SORT'=>'asc'],'ID,TITLE,LEVEL,PID',0);
    }
    public function get_node_list($where,$field='*',$limit=15){
        return $this->_get_list($this->node_table,$where,['LEVEL'=>'asc','SORT'=>'asc'],$field,$limit);
    }

    public function get_node_count($where){
        return $this->_get_count($this->node_table,$where);
    }

    public function get_node_info($where,$field='*'){
        return $this->_get_info($this->node_table,$where,$field);
    }

    public function del_node($nid){
        return $this->_del_row($this->node_table,['ID'=>$nid]);
    }

    public function update_node($where,$update){
        return $this->_update_data($this->node_table,$where,$update);
    }

    public function save_add_node($data){
        //如果类型被修改成’项目‘，则检查是不是已经有了项目数据，有则不能修改
        if ($data['LEVEL'] == 1) {
            $module = $this->get_node_count(['LEVEL' => '1']);
            if ($module) {
                return array('err'=>1,'mesg'=>'项目只能有一个，不能再添加项目类型');
            }
        }

        //判断插入的level值，与其父节点的level值之差，不能大于等于2级或0
        $pidn = $this->get_node_info(['ID'=>$data['PID']],'LEVEL,GID');
        $res      = $data['LEVEL'] - $pidn['LEVEL'];
        if ($res > 1) {
            return array('err'=>1,'mesg'=>'父节点与类型选择不匹配');
        }

        if($data['LEVEL']==3){
            $data['GID'] = $pidn['GID'];
        }
        $res = Db::table($this->node_table)->insert($data);
        if (!$res) {
            return array('err'=>1,'mesg'=>'添加权限节点失败');
        }
        return array('err'=>0,'mesg'=>'添加权限节点成功');
    }

    public function save_edit_node($data){

        if (!$data['ID'] || $data['ID']<=0) {
            return array('err'=>1,'mesg'=>'系统错误，请重试');
        }

        $pid = $data['PID'];
        $oldPid = $data['OLDPID'];
        $level = $data['LEVEL'];
        $oldLevel = $data['OLDLEVEL'];
        unset($data['OLDPID'],$data['OLDLEVEL']);
        if ($data['ID'] == $pid) {
            return array('err'=>1,'mesg'=>'父节点不能修改为自身');
        }

        //如果类型被修改成’项目‘，则检查是不是已经有了项目数据，有则不能修改
        if ($oldLevel != 1 && $level == 1) {
            $module = $this->get_node_count(['LEVEL' => '1']);
            if ($module) {
                return array('err'=>1,'mesg'=>'项目只能有一个，不能再修改为项目类型');
            }
        }

        //如果修改了父节点或类型信息
        $pidn = $this->get_node_info(['ID'=>$data['PID']],'LEVEL,GID');

        if ($pid != $oldPid || $level != $oldLevel) {
            //查找是否有子节点
            $childs = $this->get_node_count(['PID' => $data['ID']]);;
            if ($childs) {
                return array('err'=>1,'mesg'=>'该节点下面还有子节点，不能修改类型或父节点');
            }

            //判断修改后的level值，与其父节点的level值之差，不能大于等于2级或0

            $res = $level - $pidn['LEVEL'];
            if ($res > 1) {
                return array('err'=>1,'mesg'=>'父节点与类型选择不匹配');
            }
        }

        if($level==3){
            $data['GID'] = $pidn['GID'];
        }
        Db::table($this->node_table)->where(['id'=>$data['ID']])->update($data);
        return array('err'=>0,'mesg'=>'编辑成功');
    }

    //=============================admin_node_group
    /**
     * 获取分组
     * @param string $fields
     * @return mixed
     */
    public function get_node_groups($fields = 'ID,NAME'){
        return Db::table($this->node_group_table)->field($fields)->order('SORT','asc')->select();
    }

    public function get_node_group_list($where,$field='*',$limit=15){
        return $this->_get_list($this->node_group_table,$where,['SORT'=>'asc'],$field,$limit);
    }

    public function get_node_group_count($where){
        return $this->_get_count($this->node_group_table,$where);
    }

    public function get_node_group_info($where,$field='*'){
        return $this->_get_info($this->node_group_table,$where,$field);
    }

    public function del_node_group($gid){
        return $this->_del_row($this->node_group_table,['ID'=>$gid]);
    }

    public function update_node_group($where,$update){
        return $this->_update_data($this->node_group_table,$where,$update);
    }

    public function save_group_data($data){
        if(isset($data['ID']) && $data['ID']>0){
            return Db::table($this->node_group_table)->where(['ID'=>$data['ID']])->update($data);
        }else{
            return Db::table($this->node_group_table)->insert($data);
        }
    }

    //===================================admin_role

    public function get_role_list($where,$field='*',$limit=15){
        return $this->_get_list($this->role_table,$where,['ID'=>'asc'],$field,$limit);
    }

    public function get_role_count($where){
        return $this->_get_count($this->role_table,$where);
    }

    public function get_role_info($where,$field='*'){
        return $this->_get_info($this->role_table,$where,$field);
    }

    public function del_role($role_id){
        //Db::startTrans();

        //admin_access   role_id
        $this->_del_row($this->acc_table,['ROLE_ID'=>$role_id]);

        //admin_role_user  role_id
        $this->_del_row($this->role_user_table,['ROLE_ID'=>$role_id]);

        //admin  ,role_id,role
        $this->_update_data($this->admin_table,['ROLE_ID'=>$role_id],['ROLE_ID'=>'','ROLE'=>'']);

        //admin_role   id
        $this->_del_row($this->role_table,['ID'=>$role_id]);
    }

    public function del_role_group($gid){
        return $this->_del_row($this->role_table,['ID'=>$gid]);
    }

    public function save_role_data($data){
        if(isset($data['ID']) && $data['ID']>0){
            Db::table($this->role_table)->where(['ID'=>$data['ID']])->update($data);
            return $data['ID'];
        }else{
            return Db::table($this->role_table)->insertGetId($data);
        }
    }


    //===================================admin_access

    public function get_access_list($where,$field='*'){
        return $this->_get_list($this->acc_table,$where,[],$field,0);
    }

    public function save_access_date($role_id,$access){
        $where = ['ROLE_ID'=>$role_id];
        $this->_del_row($this->acc_table,$where);
        if(empty($access)){
            return false;
        }
        $data = [];
        foreach($access as $acc){
            $data[] = ['ROLE_ID'=>$role_id,'NODE_ID'=>$acc];
        }
        return Db::table($this->acc_table)->insertAll($data);
    }


    //===================================admin_role_user
}