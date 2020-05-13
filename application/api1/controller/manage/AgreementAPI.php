<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\model\Agreement;
use think\Request;

class AgreementAPI extends Common {


    public function index(Request $request) {

        $list = Agreement::where(function($query) use($request) {
                if (!$request->User->isTopPower) {
                    $query->whereIn('UUID', $this->getManageUserIds($request->MUID));
                }
                $uuid = $request->param('UUID', 0, 'int');
                if ($uuid > 0) {
                    $query->where('UUID', $uuid);
                }
            })
            ->order('ADD_TIME','DESC')
            ->select()
            ->map(function ($item){
                $item->images->map(function($image) {
                    return $image;
                });
                return $item;
            });

        return $this->ok('',[
            'list' => !empty($list) ? $list->toArray() : []
        ]);
    }

}