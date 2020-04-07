<?php

namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Request;
use app\common\model\DrugMessageReports;
//use app\common\model\Options;
//use app\common\model\Areas;
use app\common\model\Upareatable,
    app\common\model\Subareas;
//use app\common\model\AreasSubs;

class UserManageDrugMessages extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

//        $opts = new Options();
        //todo 加上权限范围条件

        $sop = $this->doSearch();

        $list = $sop['st']->where(function ($query){
                $ids = $this->getManageMuids();
                if($ids != 'all'){
                    $query->whereIn('UMID', $ids);
                }
            })->order('REPORT_TIME', 'desc')->paginate(self::PAGE_SIZE, false, [
                'query'=>request()->param(),
            ]);


        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'usermanagedrugmessages_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('total', $list->total());
        $this->assign('fselect',$this->filterSelect($sop['p']['sok'], [
            'title'=>'标题',
            'area'=>'地点',
//            'up_name'=>'上报人',
            'umid'=>'上报人ID',
            'dmr_code'=>'编号']));
        $this->assign('is_so', $sop['is_so']);
        $this->assign('param', $sop['p']);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    protected function doSearch(){
        $st = null;
        $p = [];
        $soks = ['umid', 'up_name','title','dmr_code', 'area'];
        $p['sok'] = input('get.sok','');
        $p['sov'] = input('get.sov','');
        $is_so = false;
        $st = DrugMessageReports::with([
            'muser'=>function($query)  use($p){
                $query->field('ID,NAME');
//                if($p['sok'] == 'up_name' && $p['sov']){
//                    $query->where('NAME', 'like', '%'.$p['sov'].'%');
//                }
            }
        ])->where('ISDEL',0);


        if($p['sov'] && $p['sok'] && in_array($p['sok'],$soks)){

            if(in_array($p['sok'] ,['dmr_code','umid'])){
                $st->where(strtoupper($p['sok']), '=', $p['sov']);
            }elseif($p['sok']=='area'){
                $st->where('ADDRESS', 'like', '%'.$p['sov'].'%');
            }elseif($p['sok']=='title'){
                $st->where(strtoupper($p['sok']), 'like', '%'.$p['sov'].'%');
            }
//            elseif($p['sok']=='up_name'){
//                $st->hasWhere('muser', ['NAME'=>['like', '%'.$p['sov'].'%']]);
//            }

            $is_so = true;
        }

        $p['a1'] = input('area1', '');
        $p['a2'] = input('area2', '');
        $p['a3'] = input('area3', '');

        if($p['a1'] > 0){
            $code12 = strlen($p['a1'])==6 ? $p['a1'].'000000' : $p['a1'];
            $code6 = substr($p['a1'], 0, 6);

            $st->whereIn('COUNTY_ID', [$code6, $code12]);
            $is_so = true;
        }
        if($p['a2'] > 0){
            $st->where('STREET_ID', $p['a2']);
            $is_so = true;
        }
        if($p['a3'] > 0){
            $st->where('COMMUNITY_ID', $p['a3']);
            $is_so = true;
        }


        return ['st'=>$st,'p'=>$p,'is_so'=>$is_so];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        //todo 加上权限范围条件
        $info = DrugMessageReports::with([
            'muser'=>function($query){
                $query->field('ID,NAME,UCODE');
            }
        ])->where('ISDEL','=',0)->find($id);

        if(!$info){
            $this->error('该报告不存在或已删除');
        }

        if(!$this->checkMUid($info->UMID)){
            $this->error('权限不足');
        }
//        $areas = Areas::where('ID','in',[$info->PROVINCE_ID,$info->CITY_ID,$info->COUNTY_ID])
//            ->order('ID','asc')->select()->column('NAME');
        $address1 = Upareatable::where('UPAREAID', '=', $info->COUNTY_ID)->find();
        $address2 = [];
        if($info->STREET_ID>0){
            $address2 = Subareas::where('CODE12', 'in', [$info->STREET_ID,$info->COMMUNITY_ID])
                ->order('ID','asc')->select()->column('NAME');
//            $areasubs = AreasSubs::where('ID','in',[$info->STREET_ID,$info->COMMUNITY_ID])
//                ->where('ACTIVE',1)
//                ->order('ID','asc')->select()->column('NAME');
        }
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
        //print_r($info);exit;
        //$info->GPS_LOCATION = '';
//        $info->AREAS = implode(' ',array_merge($areas,$areasubs));
        $info->AREAS = $address1->UPAREANAME . implode(' ',$address2);;

        $this->assign('info',$info);
        return $this->fetch();

    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id=0)
    {
        if(!$id){
            $this->error('访问错误');
        }
        //todo 加上权限范围条件
        $info = DrugMessageReports::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该报告不存在或已删除');
        }
        if(!$this->checkMUid($info->UMID)){
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        $this->success('删除成功');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }



    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


}
