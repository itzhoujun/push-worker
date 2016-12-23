<?php

return array(

    'JPUSH_RATE' => 600, //极光推送每分钟600次

    'ALIPUSH_COUNT' => 600, //每次推送数量

    "JPUSH" => array( //极光推送 红米安卓
        "co.sihe.hongmi" => array(
            "APPKEY" => "eaa0a5b094110aa09d01ca19",
            "SECRET" => "fd6756d6cb420dbd0a85595f",
            "TITLE"  => "赢球大师",
        ),
    ),
    'ALIPUSH' => array(
        'com.tigercai.TigerLottery' => array(
            'ACCESS_KEY_ID'=> 'n7fZXCA25Uyr5N25',
            'ACCESS_SECRET'=> 'QGxzgWi3vOd7R8vdTOnd9je2A9iesP',
            'APP_KEY' => '23383332',
            "TITLE"  => "老虎彩票",
        ),
        'co.sihe.tigerlottery' => array(
            'ACCESS_KEY_ID'=> 'n7fZXCA25Uyr5N25',
            'ACCESS_SECRET'=> 'QGxzgWi3vOd7R8vdTOnd9je2A9iesP',
            'APP_KEY' => '23383332',
            "TITLE"  => "老虎彩票",
        ),
        'com.hucai.tigerlottery' => array(
            'ACCESS_KEY_ID'=> 'n7fZXCA25Uyr5N25',
            'ACCESS_SECRET'=> 'QGxzgWi3vOd7R8vdTOnd9je2A9iesP',
            'APP_KEY' => '23444515',
            "TITLE"  => "老虎彩票",
        ),
    ),
    'XIAOMIPUSH' => array(),
    'HUAWEIPUSH' => array(),

    'IOS_PUSH_CONFIG' => array(
        'IOS_PRODUCTION_MODE' => false,
        'IOS_PASSPHRASE' => 'com.tiger@sh',
        'IOS_APNS_PORT' => 2195,
        //其他配置在IOS推送工具类中根据实际情况进行配置
    ),
);