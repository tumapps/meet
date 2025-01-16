<?php

namespace app\providers\components;

use Yii;
use yii\base\Component;
use helpers\traits\Mail;
use scheduler\models\Appointments;

class MailQueueManager extends Component
{
	use Mail;
	private $redis;

	public function init()
	{
		parent::init();
		$this->redis = Yii::$app->redis;
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
		// $redis = Yii::$app->redis;
		while ($serializedEmail = $this->redis->lpop('email_queue')) {
			$emailData = unserialize($serializedEmail);

			$email = $emailData['email'];
			$subject = $emailData['subject'];
			$body = $emailData['body'];


			$type = $emailData['type']; // Retrieve the type of the email event 

			$date = date('Y-m-d H:i:s');

			if (self::send($email, $subject, $body)) {
				if ($type !== null && $type === 'reminder') {
					Appointments::updateReminder($emailData['id']);
				}
				echo "Email sent to {$email} at: {$date}\n";
				$this->logEmailStatus($email, true);
			} else {
				echo "Failed to send email to {$email}\n";
				$this->logEmailStatus($email, false);
				$this->queueForRetry($emailData, 30);
			}
		}
	}

	protected function queueForRetry($emailData, $delayInSeconds)
	{
		$retryTime = time() + $delayInSeconds;
		$serializedEmail = serialize($emailData);
		$this->redis->zadd('retry_email_queue', $retryTime, $serializedEmail);
		echo "Email added to retry queue: {$emailData['email']}\n";
	}


	protected function retryFailedEmails()
	{
		$currentTime = time();

		$emailsToRetry = $this->redis->zrangebyscore('retry_email_queue', '-inf', $currentTime);

		foreach ($emailsToRetry as $serializedEmail) {
			$this->redis->zrem('retry_email_queue', $serializedEmail);

			$emailData = unserialize($serializedEmail);

			// $this->redis->rpush('email_queue', $serializedEmail);
			$this->addToQueue($emailData);
		}
	}


	public function viewQueue()
	{
		return Yii::$app->redis->lrange('email_queue', 0, -1);
	}

	public function removeFromQueue($emailData)
	{
		$serializedEmailData = serialize($emailData);
		$this->redis->lrem('email_queue', 0, $serializedEmailData);
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
