<?php

namespace cmd\workers;


use Yii;
use yii\base\BaseObject;
use PhpAmqpLib\Message\AMQPMessage;
use helpers\RabbitMqComponent;
use helpers\traits\Mail;

class EmailWorker extends BaseObject
{
	use Mail;

    public $queue = 'email_queue';
    public $logCategory = 'email_worker';
    public $logFile = '@app/providers/bin/logs/worker.log';

    private $rabbitMq;
    private $channel;
    private $shutdown = false;

    /**
     * Initializes the RabbitMQ connection via the RabbitMqComponent.
     */
    public function init()
    {
        parent::init();

        $this->logFile = Yii::getAlias($this->logFile);

        // Initialize RabbitMQ component
        if (!Yii::$app->has('rabbitmq')) {
            Yii::error('RabbitMQ component is not configured.', $this->logCategory);
            throw new \RuntimeException('RabbitMQ component is not configured.');
        }

        $this->rabbitMq = Yii::$app->rabbitmq;
        $this->channel = $this->rabbitMq->getChannel();

        // Declare the queue
        $this->rabbitMq->declareQueue($this->queue);

        if(function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }
    }

    /**
     * Handle shutdown signals
     * 
     * @param int $signal
     */
    
    public function handleSignal($signal)
    {
        switch ($signal) {
            case SIGTERM:
            case SIGINT:
                // $this->shutdown = true;
                echo "[x] Shutdown signal received.\n";
                break;
        }
    }

     /**
     * Starts consuming messages from the queue.
     */
    public function run()
    {
        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function (AMQPMessage $msg) {
            $this->processMessage($msg);
        };

        $this->channel->basic_qos(null, 1, false);
        $this->channel->basic_consume($this->queue, '', false, false, false, false, $callback);

        // Keep the script running
        // while (/*!$this->shutdown && */ $this->channel->is_consuming()) {
        //     $this->channel->wait();
        //     // $this->channel->consume();

        //     // Dispatch signals
        //     if(function_exists('pcntl_signal_dispatch')){
        //         pcntl_signal_dispatch();
        //     }
        // }
        try {
            $this->channel->consume();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }

        $this->close();
        $this->channel->getConnection()->close();
    }

     /**
     * Processes a single message.
     *
     * @param AMQPMessage $msg
     */
    private function processMessage(AMQPMessage $msg)
    {
        Yii::info("Received message: {$msg->body}", $this->logCategory);
        echo "Received message: {$msg->body}", $this->logCategory;

        $payload = json_decode($msg->body, true);

        if ($payload && isset($payload['email'], $payload['subject'], $payload['body'])) {
            try {

                $emailSent = $this->send($payload['email'], $payload['subject'], $payload['body']);

                if ($emailSent) {
                    Yii::info("Email sent to {$payload['email']}", $this->logCategory);
                    echo "Email sent to {$payload['email']}", $this->logCategory;

                    $msg->ack(); // Acknowledge the message upon successful send
                } else {
                    throw new \Exception("Failed to send email to {$payload['email']}");
                }
            } catch (\Exception $e) {
                Yii::error("Error sending email: {$e->getMessage()}", $this->logCategory);
                $msg->nack(false, true); // Requeue the message for retry
            }
        } else {
            Yii::error("Invalid payload: " . print_r($msg->body, true), $this->logCategory);
            $msg->reject(false); // Reject the message without requeueing
        }
    }

    /**
     * Closes the channel and connection.
     */
    public function close()
    {
        $this->rabbitMq->close();
        Yii::info("Worker shut down gracefully.", $this->logCategory);
    }
}