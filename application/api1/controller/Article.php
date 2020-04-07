<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller;

use think\Request;
use app\common\model\Articles;

class Article extends Common{


    public function index(Request $request){

        $ru = $request->routeInfo();
        $tags = [$ru['option']['client_tag'],3];

//        print_r([
//            $ru,
//            $tags
//        ]);

        $list = Articles::field(['ID','CATE_ID','TITLE','COVER_IMG','ADD_TIME'])
//            ->where(['CLIENT_TAG'=>['in',$tags]])
            ->where('ISDEL','=',0)
            ->whereIn('CLIENT_TAG',$tags)
            ->order('ADD_TIME','DESC')
            ->paginate(self::PAGE_SIZE);

        if(!$list){
            return $this->ok('',[
                'list'=>[]
            ]);

        }
        $uid = 0;
//        print_r($list);exit;
        $type_lists = [];
        foreach($list as $k=>$v){
//            $cate = $v->cate;
            //print_r($cate);
            if(!isset($type_lists[$v->cate->ID])){
                $type_lists[$v->cate->ID] = [
                    'tab_title'=>$v->cate->NAME,
                    'list'=>[]
                ];
            }
            $v['ADD_TIME'] = \Carbon\Carbon::parse($v['ADD_TIME'])->format('Y-m-d');
            $v['H5_URL'] = get_host().url('h5/AppPages/info',['uid'=>0,'tag'=>$ru['option']['client_tag'],'type'=>1,'id'=>$v['ID']]);
            $type_lists[$v->cate->ID]['list'][] = $v;
        }

        return $this->ok('',[
            'list'=>array_values($type_lists)
        ]);
    }

    public function info(Request $request){
        $id = $request->param('id',0);
        $info = Articles::field(['ID','TITLE','CONTENT','ADD_TIME'])
            ->where(['ID'=>$id])
            ->where('ISDEL','=',0)
            ->find();

        if($info){
            $info['ADD_TIME'] = \Carbon\Carbon::parse($info['ADD_TIME'])->format('Y-m-d');
            //阅读量 +1
            Articles::where('ID','=',$id)->setInc('VIEW_NUM');
        }



        return $this->ok('ok',[
            'info'=>!$info ? new \stdClass() : $info->toArray()
        ]);

    }

}