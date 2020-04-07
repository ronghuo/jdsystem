<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use Carbon\Carbon;
use app\api1\controller\Common;
use think\Request;
use app\common\model\MessageBoardAsks,
    app\common\model\MessageBoardAnswers;

class Qa extends Common{

    public function index(Request $request){

        $list = [];
        MessageBoardAsks::field('ID, ASKER_NAME as UNAME,QUESTION as CONTENT,ISNEW,ADD_TIME')
            ->where('ASKER_UID','=',$request->MUID)
            ->where('ISDEL','=',0)
            ->order('ADD_TIME','DESC')
            ->paginate(self::PAGE_SIZE)->map(function($t)use(&$list){
//                $t->ADD_TIME = Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i');
                $list[] = [
                    'UNAME'=>$t->UNAME,
                    'CONTENT'=>$t->CONTENT,
                    'ISNEW'=>$t->ISNEW,
                    'ADD_TIME'=>Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i')
                ];
                $t->ANSWERS = $t->ANSWERS
//                    ->order('ADD_TIME', 'desc')
                    ->map(function($st)use(&$list){
//                    $st->ADD_TIME = Carbon::parse($st->ADD_TIME)->format('Y-m-d H:i');
                    $list[] = [
                        'UNAME'=>$st->ANSWERER_NAME,
                        'CONTENT'=>$st->CONTENT,
                        'ISNEW'=>$st->ISNEW,
                        'ADD_TIME'=>Carbon::parse($st->ADD_TIME)->format('Y-m-d H:i')
                    ];
//                    return $st;
                });
//                return $t;
            });

        return $this->ok('',[
            'list'=>$list
        ]);
    }

    public function index_bak2(Request $request){

        $page = $request->param('page',1,'int');
        //$offset = ($page-1) * self::PAGE_SIZE;

        $ids = [];

        $list = MessageBoardAsks::field('0,ASKER_NAME as UNAME,QUESTION as CONTENT,ISNEW,ADD_TIME')
            ->union(function($query){
                $query->field('ID,ANSWERER_NAME as UNAME,CONTENT,ISNEW,ADD_TIME')->table('MESSAGE_BOARD_ANSWERS');
            })
            ->where('ASKER_UID','=',$request->MUID)
            ->where('ISDEL','=',0)
            ->order('ADD_TIME','DESC')
            //->paginate(self::PAGE_SIZE)
            ->page($page,self::PAGE_SIZE)
            ->select()
            ->map(function($t) use (&$ids){
                $t->ADD_TIME = Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i');
                if($t["0"]>0){
                    $ids[] = $t["0"];
                }
                unset($t["0"]);
                return $t;
            });

        //set read
        if(!empty($ids)){
            MessageBoardAnswers::where('ID','in',$ids)->update(['ISNEW'=>0]);
        }

        return $this->ok('',[
            //'ids'=>$ids,
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    public function index_bak(Request $request){
        $list = MessageBoardAsks::where('ASKER_UID','=',$request->MUID)
            ->where('ISDEL','=',0)
            ->order('ID','DESC')
            ->paginate(self::PAGE_SIZE)->map(function($t){
                $t->ADD_TIME = Carbon::parse($t->ADD_TIME)->format('Y-m-d H:i');

                $t->ANSWERS = $t->ANSWERS->map(function($st){
                    $st->ADD_TIME = Carbon::parse($st->ADD_TIME)->format('Y-m-d H:i');
                    return $st;
                });
                return $t;
            });

        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    public function save(Request $request){

        $question = $request->param('QUESTION','','trim');
        if(!$question){

            $this->fail('缺少留言内容');
        }
        $data = [
            'ASKER_UID'=>$request->MUID,
            'ASKER_NAME'=>$request->User->NAME,
            'QUESTION'=>$request->param('QUESTION')
        ];

        $res = (new MessageBoardAsks())->insert($data);
        if($res){
            $this->ok('提交留言成功');
        }

        $this->fail('提交留言失败');
    }
}