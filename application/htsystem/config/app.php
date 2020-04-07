<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/26
 */
return [
//    'layout_on'     =>  true,
//    'layout_name'   =>  'layout',
    'rbac'=>[
        'rbac_superman_id'=>2,
        'no_auth_node'=>['index_index']
    ],
    'ad_cache_key'=>[
        'admin_in_depart'=>'admin_in_depart'
    ],
    'admin_lgn_status_expire'=>7200,

    'cache_version_file'=>Env('root_path') . 'cache.json'
//    'system_version'=>'1.2.4',
];