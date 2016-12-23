<?php

return array(

    'JPUSH_RATE' => 600, //极光推送每分钟600次

    'ALIPUSH_COUNT' => 600, //每次推送数量

    "JPUSH" => array( //极光推送 红米安卓
        "package_name" => array(
            "APPKEY" => "",
            "SECRET" => "",
            "TITLE"  => "",
        ),
    ),
    'ALIPUSH' => array(
        'package_name' => array(
            'ACCESS_KEY_ID'=> '',
            'ACCESS_SECRET'=> '',
            'APP_KEY' => '',
            "TITLE"  => "",
        ),
        'package_name' => array(
            'ACCESS_KEY_ID'=> '',
            'ACCESS_SECRET'=> '',
            'APP_KEY' => '',
            "TITLE"  => "",
        ),
        'package_name' => array(
            'ACCESS_KEY_ID'=> '',
            'ACCESS_SECRET'=> '',
            'APP_KEY' => '',
            "TITLE"  => "",
        ),
    ),
    'XIAOMIPUSH' => array(),
    'HUAWEIPUSH' => array(),

    'IOS_PUSH_CONFIG' => array(
        'IOS_PRODUCTION_MODE' => false,
        'IOS_PASSPHRASE' => '',
        'IOS_APNS_PORT' => 2195,
        //其他配置在IOS推送工具类中根据实际情况进行配置
    ),
);
