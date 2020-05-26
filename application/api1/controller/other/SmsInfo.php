<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/18
 */
namespace app\api1\controller\other;

use app\api1\controller\Common;
use app\common\model\other\SmsInfo as SmsInfoModel;
use app\common\validate\other\SmsInfoVer;
use think\Request;

class SmsInfo extends Common {

    public function index() {
        $list = SmsInfoModel::order('addtime asc')->all();
        return $this->ok('ok', ['list' => $list]);
    }

    public function save(Request $request) {

        $data = [
            'smsAddress' => $request->param('smsAddress','', 'trim'),
            'smsbody' => $request->param('smsbody','', 'trim'),
            'smstime' => $request->param('smstime','', 'trim'),
            'userphone' => $request->param('userphone','', 'trim'),
            'addtime' => date('Y-m-d H:i:s'),
            'username' => $request->param('username','', 'trim'),
            'type' => $request->param('type','', 'trim')
        ];

        $smsInfoVer = new SmsInfoVer();
        if (!$smsInfoVer->check($data)) {
            return $this->fail($smsInfoVer->getError());
        }

        $smsInfo = new SmsInfoModel();
        $smsInfo->save($data);

        return $this->ok('ok',[
            'result' => $smsInfo->id
        ]);

    }


}