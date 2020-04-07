<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2017-06-15
 * Time: 20:36
 */

namespace app\htsystem\model;

use think\Db;
class Syspower extends  Base{

    protected $node_table = 'ADMIN_NODES';
    protected $node_group_table = 'ADMIN_NODE_GROUPS';
    protected $acc_table = 'ADMIN_ACCESS';

    public function get_my_node_count($role_id){
        return $this->_get_count($this->acc_table,['ROLE_ID'=>$role_id]);
    }

    public function get_my_node_info($role_id){

        $node_ids = $this->get_my_node_ids($role_id);
        /*$nodes = $this->_get_list(
            $this->node_table,
            ['ID'=>['in',$node_ids],'STATUS'=>1,'LEVEL'=>['gt',1]],
            ['LEVEL'=>'asc'],
            'ID,NAME,TITLE,LEVEL,PID,GID',
            0);*/
        $nodes = AdminNodes::field('ID,NAME,TITLE,LEVEL,PID,GID')
            ->where('STATUS','=',1)
            ->whereIn('ID',$node_ids)
            ->where('LEVEL','>',1)
            ->order('LEVEL','asc')
            ->select();

        $gids = $nodes->column('GID');
        //$groups = $this->find_groups(['ID'=>['in',$gids]],'ID');
        $gids = AdminNodeGroups::field('ID')->whereIn('ID',$gids)->select()->column('ID');
        //$gids = array2to1($groups,'ID');


        $nodes = create_level_tree($nodes->toArray(),1);
        $gid_node_ids = [];
        $acc_list = [];
        foreach($nodes as $nd){
            $gid_node_ids[$nd['GID']][] = $nd['ID'];
            $acc_list[strtolower($nd['NAME'])] = [$nd['ID'],$nd['GID']];
            if(!empty($nd['SUB'])){
                foreach($nd['SUB'] as $sub1){
                    $gid_node_ids[$sub1['GID']][] = $sub1['ID'];
                    $acc_list[strtolower($nd['NAME'].'_'.$sub1['NAME'])] = [$sub1['ID']];
                    if(!empty($sub1['SUB'])){
                        foreach($sub1['SUB'] as $sub2){
                            $gid_node_ids[$sub2['GID']][] = $sub2['ID'];
                            $acc_list[strtolower($nd['NAME'].'_'.$sub2['NAME'])] = [$sub2['ID']];
                        }
                    }
                }
            }
        }
        return ['gids'=>$gids,'acc_list'=>$acc_list,'acc_ids'=>$node_ids,'gid_node_ids'=>$gid_node_ids];
    }

    public function find_groups($where,$field='*',$limit=0){
        return $this->_get_list($this->node_group_table,$where,['SORT'=>'asc'],$field,$limit);
    }

    //找出当前登录者的权限节点
    public function get_my_node_ids($role_id){
        $acc = $this->_get_list($this->acc_table,['ROLE_ID'=>$role_id],[],'NODE_ID',0);
        return array2to1($acc,'NODE_ID');
    }

    public function get_menu_by_ids($ids){
        if(empty($ids)){
            return [];
        }
        //$nodes = $this->_get_list($this->node_table,['ID'=>['in',$ids],'STATUS'=>1,'ISMENU'=>1],['LEVEL'=>'asc','SORT'=>'asc'],'ID,TITLE,NAME,ICON,LEVEL,PID',0);

        $nodes = AdminNodes::field('ID,TITLE,NAME,ICON,LEVEL,PID')
            ->where('STATUS','=',1)
            ->where('ISMENU','=',1)
            ->whereIn('ID',$ids)
            ->order('LEVEL','asc')
            ->order('SORT','asc')
            ->select();
        return create_level_tree($nodes->toArray(),1);
    }
}