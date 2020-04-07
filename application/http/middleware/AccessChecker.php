<?php

namespace app\http\middleware;

use app\htsystem\model\AdminLogs;
use think\Request;

class AccessChecker
{
    protected $concerned_accesses = [
        'UserUsers' => [
            'create' => ['method' => 'POST', 'oper_type' => 1, 'oper_name' => '新增康复人员', 'target_type' => 'UserUser'],
            'zhipai' => ['method' => 'POST', 'oper_type' => 3, 'oper_name' => '指派康复人员', 'target_type' => 'UserUser', 'target_id' => 'uid'],
            'changepwsd' => ['method' => 'POST', 'oper_type' => 3, 'oper_name' => '修改康复人员密码', 'target_type' => 'UserUser', 'target_id' => 'ID'],
            'edit' => ['method' => 'POST', 'oper_type' => 3, 'oper_name' => '修改康复人员信息', 'target_type' => 'UserUser', 'target_id' => 'ID'],
            'delete' => ['oper_type' => 2, 'oper_name' => '删除康复人员', 'target_type' => 'UserUser', 'target_id' => 'id'],
            'agreement' => ['method' => 'POST', 'oper_type' => 3, 'oper_name' => '修改康复人员康复协议', 'target_type' => 'UserUser', 'target_id' => 'id']
        ],
        'UserEstimates' => [
            'create' => ['method' => 'POST', 'oper_type' => 1, 'oper_name' => '新增风险评估', 'target_type' => 'UserUser', 'target_id' => 'id'],
            'delete' => ['oper_type' => 2, 'oper_name' => '删除风险评估', 'target_type' => 'UserUser', 'target_id' => 'uuid']
        ]
    ];

    public function handle(Request $request, \Closure $next)
    {
        $controller = $request->controller();
        $concerned_controllers = array_keys($this->concerned_accesses);
        if (!in_array($controller, $concerned_controllers)) {
            return $next($request);
        }
        $action = $request->action();
        $concerned_actions = array_keys($this->concerned_accesses[$controller]);
        if (!in_array($action, $concerned_actions)) {
            return $next($request);
        }
        $method = $request->method();
        $concerned_action = $this->concerned_accesses[$controller][$action];
        if (isset($concerned_action['method']) && $concerned_action['method'] !== $method) {
            return $next($request);
        }

        $log = new AdminLogs();
        $log->USER_ID = session('user_id');
        $log->USER_NAME = session('username');
        $log->OPER_TYPE = $concerned_action['oper_type'];
        $log->OPER_CODE = $controller . '_' . $action;
        $log->OPER_NAME = $concerned_action['oper_name'];
        $log->CONTENT = json_encode($request->param());
        $log->IP = get_client_ip();
        $log->URL = $controller . '/' . $action;
        $log->ADD_TIME = date("Y-m-d H:i:s");
        if (isset($concerned_action['target_type'])) {
            $log->TARGET_TYPE = $concerned_action['target_type'];
        }
        if (isset($concerned_action['target_id'])) {
            $log->TARGET_ID = $request->param($concerned_action['target_id']);
        }
        $log->save();

        return $next($request);
    }
}
