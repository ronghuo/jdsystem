<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/26
 */
namespace app\htsystem\widget;
use app\htsystem\controller\Common;
use app\htsystem\model\Syspower;
use app\htsystem\model\AdminNodeGroups;
use app\htsystem\model\AdminNodes;
use think\Request;


class Menu extends Common{


    public function top(Request $request){

        $gids = session('gids');

        //$groups = $power_model->find_groups($where);
        $groups = AdminNodeGroups::where('ID','in',$gids)->order('SORT','asc')->select();
        //echo $groups;exit;
        //找出当前的控制器属于哪个组
        $contr = strtolower($request->controller());
        //print_r([session('gid_node_ids'),session('_ACCESS_LIST')]);exit;
        $node = session('_ACCESS_LIST.'.$contr);
        if(!$node){
            $n = AdminNodes::where('NAME', toCamelCase($contr))->where('LEVEL', 2)->find();
            if(!$n){
                header("Location:".Url('Index/index',['t'=>1]));
                exit;
            }
            $cgp = $n->GID;
            $gidNodeIds = session('gid_node_ids');
            $subs = $gidNodeIds[$cgp] ?? false;

            if(!$subs){
                header("Location:".Url('Index/index',['t'=>2]));
                exit;
            }

            $fnodes = AdminNodes::whereRaw("ID in (".implode(',', $subs).")")
                ->order(['LEVEL'=>'asc', 'ISMENU'=>'desc'])
                ->select();

            if(!$fnodes){
                header("Location:".Url('Index/index',['t'=>3]));
                exit;
            }

            $fcontr = '';
            $fact = '';
            foreach($fnodes as $fd){
                if($fd->LEVEL == 2){
                    $fcontr = $fcontr ? : $fd->NAME;
                }elseif($fd->LEVEL == 3){
                    $fact = $fact ? : $fd->NAME;
                }
            }

            $url = Url("{$fcontr}/$fact");
//            print_r([
//                $url,
//                $fnodes->toArray()]);

            header("Location:{$url}");

        }else{
            $cgp = $node[1];
        }

        //print_r($node);
        //exit;
        $this->assign('groups',$groups->toArray());
        $this->assign('cgp', $cgp);


        return $this->fetch('widget/top');
    }

    public function left(Request $request){

        //找出当前的控制器属于哪个组
        $contr = strtolower($request->controller());
        $act = strtolower($request->action());
        $node = session('_ACCESS_LIST.'.$contr);
        $nodes = [];
        if($node[1]>0){
            $power_model = new Syspower();
            $node_ids = session('gid_node_ids.'.$node[1]);
            $nodes = $power_model->get_menu_by_ids($node_ids);
        }
        $this->assign('nodes',$nodes);
        $this->assign('contr',$contr);
        $this->assign('act',$act);

        return $this->fetch('widget/left');
    }
}