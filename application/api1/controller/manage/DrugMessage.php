<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use think\Request;
use Carbon\Carbon;
use app\common\model\DrugMessageReports,
    app\common\model\DrugMessageReportImgs,
    app\common\model\Options,
//    app\common\model\Areas,
    app\common\model\Upareatable,
    app\common\model\Subareas;
//    app\common\model\AreasSubs;
use app\common\validate\DrugMessageReportsVer;
use \app\common\library\Credit;
use \app\common\library\Mylog;

class DrugMessage extends Common{

    public function index(Request $request){
        $page = $request->param('page',1,'int');
        $list = DrugMessageReports::field(['ID','TITLE','CONTENT','WRITE_TIME'])
            ->where('STATUS',1)
            ->where('UMID','=',$request->MUID)->where('ISDEL','=',0)
            ->order('WRITE_TIME','DESC')
            ->page($page,self::PAGE_SIZE)
            ->select()->map(function($t) use($request){
                $t->UNAME = $request->User->NAME;
                $t->WRITE_TIME = Carbon::parse($t->WRITE_TIME)->format('Y/m/d H:i');
                $t->CONTENT = strip_tags($t->CONTENT);
                return $t;
            });


        return $this->ok('',[
            'list'=>!$list ? [] : $list->toArray()
        ]);
    }

    // 撤销删除记录
    public function cancel(Request $request){
        $id = $request->param('ID',0,'int');
        $info = DrugMessageReports::find($id);
        if(!$info || $info->STATUS == 0){
            return $this->fail('该报告不存在');
        }

        $info->STATUS = 0;
        $info->save();
        //减去相应积分
        Credit::updateManagerCredit($request->MUID,Credit::CANCEL_DRUG_MESSAGE);
        return $this->ok('撤销成功');
    }

    public function info(Request $request,$id){

        $info = DrugMessageReports::where('ID','=',$id)
            ->where('ISDEL','=',0)
            ->where('UMID','=',$request->MUID)
            ->find();

        if(!$info){
            return $this->ok('',[
                'info'=>new \stdClass()
            ]);
        }

        $info->GPS_LOCATION = '';
        $info->CLUE_STATUS = Options::getNameById($info->CLUE_STATUS_ID)['NAME'];
        //$info->CLUE_STATUS = Options::getClueStatus($info->CLUE_STATUS_ID);
        //$info->CLUE_TYPE = Options::getClueType($info->CLUE_TYPE_ID);
        $info->CLUE_TYPE = Options::getNameById($info->CLUE_TYPE_ID)['NAME'];
        //$info->EMEY_LEVEL = Options::getEmeyLevel($info->EMEY_LEVEL_ID);
        $info->EMEY_LEVEL = Options::getNameById($info->EMEY_LEVEL_ID)['NAME'];
//        $info->REPORT_TYPE = Options::getReportType($info->REPORT_TYPE_ID);
        $info->REPORT_TYPE = Options::getNameById($info->REPORT_TYPE_ID)['NAME'];
//        $info->GATHER_TYPE = Options::getGatherType($info->GATHER_TYPE_ID);
        $info->GATHER_TYPE = Options::getNameById($info->GATHER_TYPE_ID)['NAME'];

        $info->HAPPENED = $info->happened;


//        $areas = Areas::where('ID','in',[$info->PROVINCE_ID,$info->CITY_ID,$info->COUNTY_ID])
//            ->order('ID','asc')->select()->column('NAME');
        $address1 = Upareatable::where('UPAREAID', '=', $info->COUNTY_ID)->find();//->UPAREANAME;

        $address2 = [];
        if($info->STREET_ID>0){
            //CODE12
            $address2 = Subareas::where('CODE12', 'in', [$info->STREET_ID,$info->COMMUNITY_ID])
                ->order('ID','asc')->select()->column('NAME');
//            $areasubs = AreasSubs::where('ID','in',[$info->STREET_ID,$info->COMMUNITY_ID])
//                ->where('ACTIVE',1)
//                ->order('ID','asc')->select()->column('NAME');
        }
        $info->AREA = $address1->UPAREANAME . implode(' ',$address2);
        // 将图片，音频，视频分开来
        // 将图片，音频，视频分开来
        $medias = [
            'audios'=>[],
            'videos'=>[],
            'images'=>[],
        ];
        $info->IMGS->map(function ($t) use (&$medias){
            $t->IMG_URL = build_http_img_url($t->SRC_PATH);
            if($t->MEDIA_TYPE==1){
                $medias['audios'][] = $t->toArray();
            }elseif($t->MEDIA_TYPE==2){
                $medias['videos'][] = $t->toArray();
            }else{
                $medias['images'][] = $t->toArray();
            }
            //return $t;
        });
        unset($info->IMGS);
        $info->IMGS = $medias['images'];
        $info->AUDIOS = isset($medias['audios']) ? $medias['audios'] : [];
        $info->VIDEOS = isset($medias['videos']) ? $medias['videos'] : [];

        return $this->ok('',[
            'info'=>$info->toArray(),
            'muser'=>[
                'ID'=>$request->MUID,
                'NAME'=>$request->User->NAME,
                'UCODE'=>$request->User->UCODE
            ]
        ]);
    }

    public function save(Request $request){

        $data = [
            'UMID'=>$request->MUID,
            'STATUS'=>1,
            'TITLE'=>$request->param('TITLE','','trim'),
            'CLUE_STATUS_ID'=>$request->param('CLUE_STATUS_ID','','int'),
            'CLUE_TYPE_ID'=>$request->param('CLUE_TYPE_ID','','int'),
            'EMEY_LEVEL_ID'=>$request->param('EMEY_LEVEL_ID','','int'),
            'REPORT_TYPE_ID'=>$request->param('REPORT_TYPE_ID','','int'),
            'GATHER_TYPE_ID'=>$request->param('GATHER_TYPE_ID','','int'),
            'PROVINCE_ID'=>$request->param('PROVINCE_ID',43,'int'),
            'CITY_ID'=>$request->param('CITY_ID','','int'),
            'COUNTY_ID'=>$request->param('COUNTY_ID','','int'),
            'STREET_ID'=>$request->param('STREET_ID',0,'int'),
            'COMMUNITY_ID'=>$request->param('COMMUNITY_ID',0,'int'),
            'ADDRESS'=>$request->param('ADDRESS','','trim'),
            'REPORT_TIME'=>$request->param('REPORT_TIME','','trim'),
            'WRITE_TIME'=>$request->param('WRITE_TIME','','trim'),
            'GPS_LAT'=>$request->param('GPS_LAT','','trim'),
            'GPS_LONG'=>$request->param('GPS_LONG','','trim'),
            'CONTENT'=>$request->param('CONTENT','','trim'),
        ];

        $v = new DrugMessageReportsVer();
        if(!$v->check($data)){
            return $this->fail($v->getError());
        }
        $dmr = new DrugMessageReports();

        $data['DMR_CODE'] = $dmr->createNewDMRCode($data['COUNTY_ID']);

        $dmr_id = $dmr->insertGetId($data);
        if(!$dmr_id){
            return $this->fail('上报失败');
        }

        // 每成功上报涉毒信息一次，增加1分到个人账号（前端暂不现实 ，仅记录导后台）
        Credit::updateManagerCredit($request->MUID,Credit::POST_DRUG_MESSAGE);
        // 支持图文毒情上报，音频、视频上传


        $this->saveImages($request,$dmr_id);

        $audios = $this->uploadAudios($request);
        Mylog::write([
            'audios'=>$audios
        ], 'drug_messages_save');
        if(isset($audios['audios']) && !empty($audios['audios'])){
            (new DrugMessageReportImgs())->saveData($dmr_id,$audios['audios'],1);
        }
        $videos = $this->uploadVideos($request);
        Mylog::write([
            'audios'=>$videos
        ], 'drug_messages_save');

        if(isset($videos['videos']) && !empty($videos['videos'])){
            (new DrugMessageReportImgs())->saveData($dmr_id,$videos['videos'],2);
        }

        $this->ok('上报成功');

    }

    protected function saveImages(Request $request,$dmr_id){

//        \app\common\library\Mylog::write([
//            $_POST,
//            $_FILES,
//            $_SERVER
//        ],'drug_message');

        $res = $this->uploadImages($request,['drug_message/']);

        if(!$res){
            return false;
        }
        return (new DrugMessageReportImgs())->saveData($dmr_id,$res['images'],0);
//        return $this->saveHolidayImages2DB($uhal_id,$res['images']);
    }
}