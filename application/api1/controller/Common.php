<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
namespace app\api1\controller;

use think\Controller;
use think\db\Query;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use app\common\library\Mylog;
use app\common\model\UserManagerPower,
    app\common\model\UserUsers;
use app\common\library\Uploads;

class Common extends Controller
{

    const PAGE_SIZE = 30;
    const UUSER_TAG = 1;
    const MANAGE_TAG = 2;
    const ALL_TAG = 3;


    public function userCheck(UserUsers $user){
        if($user->JD_ZHI_PAI_ID == 2){
            return $this->fail([
               'success'=>false,
                'msg'=>'当前已是解除社戒社康状态'
            ]);
        }
    }

    public function getManageUserIds($umid){
        $cache_key = 'power:'.$umid;
        $ids = cache($cache_key);
        if ($ids) {
            return $ids;
        }

        $areas = (new UserManagerPower())->getPowerSettings($umid);
        if (!$areas) {
            return [];
        }
        $ids = (new UserUsers())->getUserIdsByAreas($areas);
        cache($cache_key,$ids,30);

        return $ids;
    }

    protected function contactWithArea($managerId, Query &$query, $countyField, $streetField, $communityField) {
        $cacheKey = 'power_areas:' . $managerId;
        $areas = cache($cacheKey);
        if (!$areas) {
            $areas = (new UserManagerPower())->getPowerSettings($managerId);
            cache($cacheKey, $areas,30);
        }
        if (!empty($areas['COMMUNITY_IDS'])) {
            $query->whereOr($communityField, 'in', $areas['COMMUNITY_IDS']);
        }
        if(!empty($areas['STREET_IDS'])){
            $query->whereOr($streetField,'in', $areas['STREET_IDS']);
        }
        if(!empty($areas['COUNTY_IDS'])){
            $query->whereOr($countyField,'in', $areas['COUNTY_IDS']);
        }
    }

    protected function log($mesg,$file='test'){
        Mylog::write($mesg,$file);
    }

    public function _empty()
    {

        return [
            'code'=>404,
            'msg'=>'not found'
        ];
    }


    public function ok($msg = '', $data = [],$code=200)
    {

        return $this->returnResult($code,$msg,$data);
    }

    public function fail($msg = '', $data = [],$code=400){
        return $this->returnResult($code,$msg,$data);
    }

    protected function returnResult($code,$msg = '', $data = []){
        $result = [
            'code' => (string) $code,
            'msg'  => $msg,
            'data' => !empty($data) ? tostring($data) : new \stdClass(),
        ];
//        print_r(tostring($data));

//        $type                                   = self::getResponseType();
        $type                                   = 'json';
        $header['Access-Control-Allow-Origin']  = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,Authorization,Gps-Lat,Gps-Long,Device-System,App-Version';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PUT,DELETE,OPTIONS';
        $response                               = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }


    protected function uploadImages(Request $request, $dir=[], $fieldName = 'images'){

        $save_path = './uploads/'.implode('/',$dir);

        $res = (new Uploads())->images($request, $save_path, $fieldName);

        if (empty($res['save_files'])) {
            return false;
        }

        return ['images' => $res['save_files'], 'fileNames' => $res['save_file_names'], 'errors'=>$res['errors']];
    }

    protected function uploadAudios(Request $request,$dir=[], $fieldName = 'audios'){

        //$save_path = './uploads/'.implode('/',$dir);

        $res = (new Uploads())->audios($request, '', $fieldName);
        //print_r($res);
        if(empty($res['save_files'])){
            return false;
        }

        return ['audios'=>$res['save_files'], 'fileNames' => $res['save_file_names'],'errors'=>$res['errors']];
    }

    protected function uploadVideos(Request $request,$dir=[], $fieldName = 'videos'){

        //$save_path = './uploads/'.implode('/',$dir);

        $res = (new Uploads())->videos($request, '', $fieldName);

        if(empty($res['save_files'])){
            return false;
        }

        return ['videos'=>$res['save_files'], 'fileNames' => $res['save_file_names'],'errors'=>$res['errors']];
    }
}