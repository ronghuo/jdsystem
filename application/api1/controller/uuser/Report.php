<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use think\Request;
use app\common\validate\UserReportsVer;
use app\common\model\UserReports,
    app\common\model\UserReportImgs;

class Report extends Common{


//    protected $middleware = ['Api1UUAuth'];


    public function test(Request $request){

    }

    public function index(Request $request){
        $page = $request->param('page',1,'int');
        //print_r($request);exit;

        $list = UserReports::where(['UUID'=>$request->UUID])->where('ISDEL','=',0)
            //->field(['ID',''])
            ->order('ADD_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select();

        if($list){
//            foreach($list as $k=>$v){
//                $list[$k]['NAME'] = $request->User->NAME;
//            }
        }

        return $this->ok('ok',[
            'list'=>!$list ? [] : $list->toArray()
        ]);

    }

    public function info(Request $request){

        $info = UserReports::where(['ID'=>$request->param('id',0)])->where('ISDEL','=',0)
            ->where(['UUID'=>$request->UUID])
            ->find();

        $info->IMGS->map(function ($t){
            $t->IMG_URL = build_http_img_url($t->SRC_PATH);
            return $t;
        });

        return $this->ok('',[
            'info'=>!$info ? new \stdClass() : $info->toArray()
        ]);

    }

    public function save(Request $request){

        $data = [
            'UUID'=>$request->UUID,
            'UNAME'=>$request->User->NAME,
            'TITLE'=>$request->param('TITLE','','trim'),
            'CONTENT'=>$request->param('CONTENT','','trim'),
        ];

        $v = new UserReportsVer();
        if(!$v->check($data)){
            return $this->fail($v->getError());
        }

        $ur_id = (new UserReports())->insertGetId($data);

        if($ur_id>0){
            $this->saveImages($request,$ur_id);
        }

        return $this->ok('报告提交成功',[
            'result'=>$ur_id
        ]);

    }

    protected function saveImages(Request $request,$ur_id){

        $res = $this->uploadImages($request,['report/']);

        if(!$res){
            return false;
        }
        return (new UserReportImgs())->saveData($ur_id,$res['images']);
//        return $this->saveHolidayImages2DB($uhal_id,$res['images']);
    }

}