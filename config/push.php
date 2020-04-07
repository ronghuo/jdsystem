<?php

return [
    'user'=>[
        'AppKey'=>'abfcd886162b23d3b5256525',
        'Secret'=>'503199c2d1ac0928918d2bc5',
        'apns_production'=>true,
        'log_file'=>Env::get('runtime_path').'log/push_user/'.date('Y-m-d').'.log'
    ],
    'manage'=>[
        'AppKey'=>'6e2e8d5e494dbb5c0658bcc1',
        'Secret'=>'b4acaef4bcb8f3c3cc1e5251',
        'apns_production'=>true,
        'log_file'=>Env::get('runtime_path').'log/push_manage/'.date('Y-m-d').'.log'
    ]
];