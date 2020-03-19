<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return $config = [
    //log目录
    'logPath'          => __DIR__ . '/log',
    'logSaveFileApp'   => 'application.log', //默认log存储名字
    'logSaveFileWorker'=> 'workers.log', // 进程启动相关log存储名字
    'pidPath'          => __DIR__ . '/log',
    'processName'      => ':swooleMultiProcess', // 设置进程名, 方便管理, 默认值 swooleTopicQueue
    'sleepTime'        => 3000, // 子进程退出之后，自动拉起暂停毫秒数
    'redis'            => [
        'host'  => '127.0.0.1',
        'port'  => '6379',
        'preKey'=> 'SwooleMultiProcess-',
        //'password'=>'',
        'select'    => 0, // 操作库(可选参数，默认0)
        'serialize' => true, // 是否序列化(可选参数，默认true)
    ],

    //exec任务相关,name的名字不能相同
    'exec'      => [
        [
            'name'            => 'kcloze-test-1',
            'bin'             => '/usr/local/php7/bin/php',
            'binArgs'         => ['/mnt/hgfs/www/saletool/think', 'testAmqp', '0'],
            'workerMinNum'    => 1, // 外部程序最小进程数(固定)
            'workerMaxNum'    => 2, // 外部程序最大进程数,最大进程数=最小进程数时，动态进程功能失效
            'exchange'        => 'router',// 如需根据队列长度动态控制进程数量，需要设置为非空
            'queue'           => 'msgs',// 如需根据队列长度动态控制进程数量，需要设置为非空
            'routingKey'      => 'goods_syncdb_addvv',// 如需根据队列长度动态控制进程数量，需要设置为非空
        ],
        /* [
            'name'      => 'kcloze-test-1',
            'bin'       => '/usr/local/bin/php',
            'binArgs'   => [__DIR__ . '/test/cli/test.php', 'oop', '123'],
            'workNum'   => 3,
        ], */
    ],
    // redis
    /* 'queue'   => [
        'type'    => 'redis',
        'host'    => '127.0.0.1',
        'port'    => 6379,
        'password'=> 'pwd',
    ], */
    // rabbitmq
    'queue'   => [
        'type'      => 'rabbitmq',
        'host'      => '127.0.0.1',
        'user'      => 'admin',
        'pass'      => '123456',
        'port'      => '5672',
        'vhost'     => '/',
    ],
];
