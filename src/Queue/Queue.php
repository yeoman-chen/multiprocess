<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\MultiProcess\Queue;

use Kcloze\MultiProcess\Logs;

class Queue
{
    public static function getQueue(array $config, Logs $logger)
    {
        if (isset($config['type']) && $config['type'] == 'redis') {
            try {
                $redis = new \Redis();
                $redis->connect($config['host'], $config['port']);
                if (isset($config['password']) && !empty($config['password'])) {
                    $redis->auth($config['password']);
                }
            } catch (\Exception $e) {
                die($e->getMessage() . PHP_EOL);
            }
            $connection = new RedisTopicQueue($redis);
        } elseif (isset($config['type']) && $config['type'] == 'rabbitmq') {
            try {
                $connection       =  RabbitmqTopicQueue::getConnection($config, $logger);
            } catch (\Exception $e) {
                die($e->getMessage() . PHP_EOL);
            }
        } else {
            echo 'you must add queue config' . PHP_EOL;
            exit;
        }

        return $connection;
    }
}
