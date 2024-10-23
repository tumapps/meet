<?php

namespace app\providers\components;

use Yii;
use yii\base\Component;
use helpers\traits\Mail;

class MailQueueManager extends Component
{
	use Mail;

	public function __construct()
	{
		//
	}

	public function addToQueue($emailData)
	{
	    try {
	        $serializedEmail = serialize($emailData);
	        $result = Yii::$app->redis->rpush('email_queue', $serializedEmail);

	        if ($result > 0) {
	            return true;
	        }

	        return false;
	    } catch (\Exception $e) {
	        Yii::error('Error adding email to queue: ' . $e->getMessage(), __METHOD__);
	        return false;
	    }
	}

	public function processQueue()
	{
		$redis = Yii::$app->redis;
        while ($serializedEmail = $redis->lpop('email_queue')) {
	        $emailData = unserialize($serializedEmail);
	        
	        $email = $emailData['email'];
	        $subject = $emailData['subject'];
	        $body = $emailData['body'];
	        
	        if (self::send($email, $subject, $body)) {
	            echo "Email sent to {$email}\n";
	            $this->logEmailStatus($email, true);
	        } else {
	            echo "Failed to send email to {$email}\n";
	            $this->logEmailStatus($email, false);
	        }
    	}
	}

	public function viewQueue()
    {
        return Yii::$app->redis->lrange('email_queue', 0, -1);
    }

    public function removeFromQueue($emailData)
	{
	    $serializedEmailData = serialize($emailData);
	    Yii::$app->redis->lrem('email_queue', 0, $serializedEmailData);
	}

	protected function logEmailStatus($email, $success)
	{
	    $statusMessage = $success 
	        ? "Email sent to {$email}." 
	        : "Failed to send email to {$email}.";

	    // Using Yii's logger to log to a file
	    Yii::info($statusMessage, 'mailQueue');
	}
}