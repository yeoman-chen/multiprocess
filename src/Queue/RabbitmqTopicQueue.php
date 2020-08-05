<?php 

namespace Kcloze\MultiProcess\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Kcloze\MultiProcess\Logs;
use Kcloze\MultiProcess\Utils;

class RabbitmqTopicQueue extends BaseTopicQueue {

    private $logger     = null;
    private $config     = [];

    public $connection = '';
    public $channel    = '';
    public $exchange   = '';
    public $queue      = '';
    public $routingKey = '';

    /**
     * RabbitmqTopicQueue constructor.
     * 使用依赖注入的方式.
     *
     * @param array $queue
     * @param mixed $exchange
     */
    public function __construct(array $config, Logs $logger)
    {
        $this->config  = $config;
        $this->logger  = $logger;

        $this->connection = new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['pass']);
        $this->channel    = $this->connection->channel();

    }
    /**
     * 创建连接
     */
    public static function getConnection(array $config, Logs $logger)
    {
        if(!$config['host'] || !$config['port'] || !$config['user'] || !$config['pass']) {
            $error = "参数不完整：".var_export($config,true);
            $logger->log($error, 'error');
            return false;
        }
        try {
            $connection       = new self($config, $logger);
        } catch (\AMQPConnectionException $e) {
            Utils::catchError($logger, $e);

            return false;
        } catch (\Throwable $e) {
            Utils::catchError($logger, $e);

            return false;
        } catch (\Exception $e) {
            Utils::catchError($logger, $e);

            return false;
        }

        return $connection;
    }

    /**
     * 发送消息
     */
    public function push($topic, $value)
    {
        $this->createQueue($topic);
        $message = new AMQPMessage(json_encode($value), array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $result = $this->channel->basic_publish($msg, $this->exchange, $this->routingKey);

        return $result;
    }
    /**
     * 消费消息
     */
    public function pop($topic)
    {
        $this->createQueue($topic);
        $this->channel->exchange_declare($this->exchange, 'direct', false, false, false);
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);

        $callback = $topic["callback"];

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queue, '', false, false, false, false, $callback);

        try {
            while (\count($this->channel->callbacks)) {
                $this->channel->wait(null, false, 70);
            }
        } catch (\Exception $e) {
            $this->logger->log('【rabbitmq】normal quit', 'info');
        }

        $this->close();
    }

    //这里的topic跟rabbitmq不一样，其实就是队列名字
    public function len($topic)
    {
        $hostConf["host"] = $this->config["host"];
        $hostConf["port"] = $this->config["port"];
        $hostConf["login"] = $this->config["user"];
        $hostConf["password"] = $this->config["pass"];
        $hostConf["vhost"] = $this->config["vhost"];
        try {
            $conn = new \AMQPConnection($hostConf);
            $conn->connect();
        } catch (\AMQPConnectionException $e) {
            Utils::catchError($logger, $e);
            throw $e;
        }

        if (!$conn->isConnected()) {
            throw new \Exception('Connection Break');
        }

        //在连接内创建一个通道
        $ch = new \AMQPChannel($conn);
        $q  = new \AMQPQueue($ch);
        $q->setName($topic);
        $q->setFlags(\AMQP_PASSIVE);
        $len = $q->declareQueue();
        $conn->disconnect();
        return $len;
    }

    /**
     * 创建队列
     */
    public function createQueue($topic)
    {
        $this->queue = $topic["queue"] ?? "";
        $this->exchange = $topic["exchange"] ?? "";
        $this->routingKey = $topic["routingKey"] ?? "";
        try{
            $this->channel->queue_declare($this->queue, false, true, false, false);
            $this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
            //$this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);
        }catch(\Exception $e) {
            Utils::catchError($this->logger, $e);
            return false;
        }
        
        return true;
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}