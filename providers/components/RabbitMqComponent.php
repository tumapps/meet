<?php

namespace helpers;

use Yii;
use yii\base\Component;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqComponent extends Component
{
    public $host;
    public $port;
    public $user;
    public $password;
    public $vhost;
    public $logFile;

    private $_connection;
    private $_channel;

    public function init()
    {
        parent::init();
        try {
            Yii::info('Attempt to connect to RabbitMq');
            $this->_connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost,
            );
            $this->_channel = $this->_connection->channel();
        } catch (\Exception $e){
            Yii::error('RabbitMq Connection failed: ' . $e->getMessage());
            throw new \RuntimeException("Cannot connect to RabbitMq", 1);
        }
    }

    public function enqueueEmail(array $data)
    {
        try{

            $queueName = 'email_queue';
            $this->declareQueue($queueName);

            $msg = new AMQPMessage(json_encode($data), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $this->_channel->basic_publish($msg, '', $queueName);
            Yii::info("Enqueued email to {$data['email']}", 'rabbitmq');

            return true;

        } catch(\Exception $e){
            Yii::error('Failed to enqueue email'. $e->getMessage(), 'rabbitmq');
            return false;
        }
    }

    public function getChannel()//: AMQPChannel
    {
        return $this->_channel;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function declareQueue(string $queueName, bool $durable = true, string $deadLetterQueue = 'email_dead_letter_queue')
    {
        $this->_channel->queue_declare(
            $queueName,
            false,    // passive
            $durable, // durable
            false,    // exclusive
            false,     // auto-delete
            new \PhpAmqpLib\Wire\AMQPTable([
                'x-dead-letter-exchange' => '',
                'x-dead-letter-routing-key' => $deadLetterQueue,
            ])
        );

        // Declare the dead-letter queue
        $this->_channel->queue_declare(
            $deadLetterQueue,
            false,
            true,
            false,
            false
        );
    }
    
    public function close()
    {
        if($this->_channel){
            $this->_channel->close();
        }
        if($this->_connection){
            $this->_connection->close();
        };
    }
}