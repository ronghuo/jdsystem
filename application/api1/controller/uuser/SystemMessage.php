<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/4/12
 */
namespace app\api1\controller\uuser;

use app\api1\controller\Common;
use think\Collection;
use app\common\model\SystemMessages as SystemMessageModel,
    app\common\model\SystemMessageRead;
use think\Request;
use Carbon\Carbon;

class SystemMessage extends Common{

    public function index(Request $request){
        $page = $request->param('page',1,'int');
        $list = Collection::make(
            SystemMessageModel::getClientMessage($request->UUID,self::UUSER_TAG,[self::UUSER_TAG,self::ALL_TAG])
                ->field('sm.ID,sm.TITLE,sm.CONTENT,sm.ADD_TIME,smr.ISREAD,smr.READ_TIME')
                ->order('sm.ADD_TIME','asc')
                ->page($page,self::PAGE_SIZE)->select()
        )->map(function($t) use($request){
            $t['ISREAD'] = (int) $t['ISREAD'];
            $t['H5_URL'] = get_host().url('h5/AppPages/info',['uid'=>$request->UUID,'tag'=>self::UUSER_TAG,'type'=>2,'id'=>$t['ID']]);
            return $t;
        });


        return $this->ok('ok',[
            'list'=>!empty($list) ? $list->toArray() : []
        ]);

    }


    public function setRead(Request $request){

        $msgid = $request->param('MSGID',0,'int');

        if(!$msgid){
            return $this->fail('参数有误');
        }

        $msginfo = SystemMessageModel::find($msgid);

        if(!$msginfo){
            return $this->fail('该消息不存在');
        }
        SystemMessageRead::saveRead([
            'MSGID'=>$msginfo->ID,
            'CLIENT_TAG'=>self::UUSER_TAG,
            'CLIENT_UID'=>$request->UUID,
        ]);
        /*$smr = SystemMessageRead::where('MSGID',$msginfo->ID)
            ->where('CLIENT_TAG',self::UUSER_TAG)
            ->where('CLIENT_UID',$request->UUID)
            ->find();
        if($smr){
            $smr->ISREAD = 1;
            $smr->READ_TIME = Carbon::now()->toDateTimeString();
            $smr->save();
        }else{
            (new SystemMessageRead())->insert([
                'MSGID'=>$msginfo->ID,
                'CLIENT_TAG'=>self::UUSER_TAG,
                'CLIENT_UID'=>$request->UUID,
                'ISREAD'=>1,
                'READ_TIME'=>Carbon::now()->toDateTimeString()
            ]);
        }*/


        return $this->ok('设置成功');

    }

}
