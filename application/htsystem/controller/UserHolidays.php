<?php

namespace app\htsystem\controller;

use Carbon\Carbon;
use think\Request;
use app\common\model\UserUsers;
use app\common\model\UserHolidayApplies,
    app\common\model\UserHolidayApplyLists;

class UserHolidays extends Common
{

    public $status_list = [
        99=>'审核中',
        1=>'同意',
        2=>'拒绝',
        3=>'续假',
        4=>'销假审核中',
//        5=>'同意',
        6=>'同意销假报道',
        7=>'拒绝销假报道'
    ];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $sop = $this->doSearch();
        // 加上权限范围条件
        //\think\Collection::make()
        $list = $sop['st']->paginate(self::PAGE_SIZE, false, [
                'query'=>request()->param(),
            ])->each(function($t,$k){
            $t->CTEXT = '';
            if($t->STATUS==3){
                $t->CTEXT = '续假';
            }
            $t->lists = $t->lists ? $t->lists->reverse()->shift() : false;
            //todo [msg] => Trying to get property 'BACK_TIME' of non-object
            if($t->lists && Carbon::parse($t->lists->BACK_TIME)->isPast()){
                $t->lists->BACK_TIME = '<span class="text-error">'.$t->lists->BACK_TIME.'</span>';
            }

            return $t;
        });

        //print_r($list);
        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'userholidays_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('list',$list);
        $this->assign('page', $list->render());
        $this->assign('total', $list->total());
        $this->assign('fselect',$this->filterSelect($sop['p']['sok'], [
            'uname'=>'请假人',
            'mobile'=>'联系手机',
            'checker_name'=>'审批人',
            'reason'=>'理由',
            'uuid'=>'请假人ID'
        ]));
        $this->assign('is_so', $sop['is_so']);
        $this->assign('param', $sop['p']);
        $this->assign('status_list', $this->status_list);
        return $this->fetch();
    }

    protected function doSearch(){

        $st = null;
        $p = [];
        $soks = ['uuid', 'uname', 'mobile', 'reason', 'checker_name'];
        $p['sok'] = input('get.sok','');
        $p['sov'] = input('get.sov','');
        $is_so = false;

        $st = UserHolidayApplies::where('ISDEL','=',0);


        $where = [];
        if($p['sov'] && $p['sok'] && in_array($p['sok'],$soks)){

            if(in_array($p['sok'] ,['uuid', 'mobile'])){
                $where[$p['sok']] = ['=', $p['sov']];
            }else{
                $where[$p['sok']] = ['like', '%'.$p['sov'].'%'];
            }
            $is_so = true;
        }
        $p['status'] = input('get.status', '');
//        echo $p['status'] ;
        if(in_array($p['status'], [99,1,2,3,4,6,7,])){

            if($p['status'] == 1){
                $st->whereIn('STATUS', [1,5]);
            }elseif($p['status'] == 99){
                $st->where('STATUS', 0);
            }else{
                $st->where('STATUS', $p['status']);
            }

            $is_so = true;
        }

        if($is_so && !empty($where)){
            $so = UserHolidayApplyLists::where('ISDEL','=',0);
            foreach($where as $fd=>$wh){
                $so->where(strtoupper($fd), $wh[0], $wh[1]);
            }
            $uha_ids = $so->select()->column('UHA_ID');
            if($uha_ids){
                $st->whereIn('ID', $uha_ids);
            }else{
                $st->where('ID', 0);
            }
        }



        $p['a1'] = input('area1', '');
        $p['a2'] = input('area2', '');
        $p['a3'] = input('area3', '');

        if($p['a1'] > 0 || $p['a2'] > 0 || $p['a3'] > 0){
            $is_so = true;

            $soids = UserUsers::field('ID')->where('ISDEL', 0)
                ->where(function ($q) use($p){
                    //县
                    if($p['a1'] >0){
                        $q->where('COUNTY_ID_12', $p['a1']);
                    }
                    //乡
                    if($p['a2']>0){
                        $q->where('STREET_ID', $p['a2']);
                    }
                    //村级
                    if($p['a3']>0){
                        $q->where('COMMUNITY_ID', $p['a3']);
                    }
                })->select()->column('ID');

//        var_dump($soids);
            $st->where(function ($query)use($soids, $is_so){
                if($is_so && $soids){
                    $query->whereIn('UUID', $soids);
                }elseif($is_so && !$soids){
                    $query->where('UUID', 0);
                }
            else{
                $muids = $this->getManageUUids();
                if($muids != 'all'){
                    $query->whereIn('UUID', $muids);
                }
            }

            });

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
        $info = UserHolidayApplies::where('ISDEL','=',0)
            ->find($id);

        if(!$info){
            $this->error('该请假不存在或已删除');
        }
        if(!$this->checkUUid($info->UUID)){
            $this->error('权限不足');
        }

        $info->CTEXT = '';
        if($info->STATUS==3){
            $info->CTEXT = '续假';
        }

        $list = UserHolidayApplyLists::where('UHA_ID','=',$id)->select()->map(function($t){
            $t->IMGS->map(function($tt){
                $tt->SRC_PATH_URL = build_http_img_url($tt->SRC_PATH);
                return $tt;
            });

            return $t;
        });


        $this->assign('info',$info);
        $this->assign('list',$list);
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

        $info = UserHolidayApplies::find($id);
        if(!$info || $info->ISDEL==1){
            $this->error('该请假不存在或已删除');
        }

        if(!$this->checkUUid($info->UUID)){
            $this->error('权限不足');
        }

        $info->ISDEL= 1;
        $info->DEL_TIME = Carbon::now()->toDateTimeString();
        $info->save();

        UserHolidayApplyLists::where('UHA_ID',$id)->update([
            'ISDEL'=>1,
            'DEL_TIME'=>Carbon::now()->toDateTimeString()
        ]);

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
