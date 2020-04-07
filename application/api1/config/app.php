<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */
return [
    'default_return_type'    => 'json',

    //
    'api_keys'=>[
        'ulogin_sms'=>'uloginsms:',
        'ureg_sms'=>'uloginsms:',
        'mlogin_sms'=>'mloginsms:',
        'mreg_sms'=>'mloginsms:',
        'areas'=>'areas:',
        'subareas'=>'subareas:',
        'dmmcs'=>'dmmcs',
        'levelareas'=>'levelareas',
    ],

    //JWT
    'jwt_api_uuser_key'=>'jfOImklsd-23490dfl*2-fsldgpp12',
    'jwt_api_muser_key'=>'FToIeKiwjSFTW90oSF23j4;LS24YHFB',
    'jwt_api_algorithm' => ['HS256'],
    'jwt_token_expiry' => 17200,
];