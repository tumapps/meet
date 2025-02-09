<?php

namespace cmd\controllers;

use yii\console\Controller;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\providers\components\MailQueueManager;

class MailQueueController extends Controller
{

	private $queue;


	public function init()
	{
		parent::init();
		$this->queue = new MailQueueManager();
	}

	public function actionTest()
	{
		$this->stdout("Mail queue is up");
	}

	// public function actionRun()
	// {
	// 	while (true) {
	// 		$this->queue->processQueue();
	// 		$this->queue->retryFailedEmails();
	// 		sleep(10);
	// 	}
	// }


	public function actionRun()
	{
		while (true) {
			try {
				echo "[" . date('Y-m-d H:i:s') . "] Processing email queue...\n";
				\Yii::info('Processing email queue...', 'mail-queue');

				$this->queue->processQueue();
				$this->queue->retryFailedEmails();

				// Handle termination signals from Supervisor
				if (function_exists('pcntl_signal_dispatch')) {
					pcntl_signal_dispatch();
				}

				sleep(1);
			} catch (\Throwable $e) {
				\Yii::error("MailQueue Error: " . $e->getMessage(), 'mail-queue');
				echo "Error: " . $e->getMessage() . "\n";
				sleep(5); // Prevent rapid crash loops
			}
		}
	}


	public function actionViewQueue()
	{
		$queueList = $this->queue->viewQueue();
		if (empty($queueList)) {
			$this->stdout("The email queue is empty.\n", Console::FG_GREEN);
			return ExitCode::OK;
		}
		foreach ($queueList as $item) {
			$emailData = unserialize($item);
			$this->stdout("Email: {$emailData['email']}, Subject: {$emailData['subject']}\n");
		}
		return ExitCode::OK;
	}

	public function actionRemoveFromQueue($email)
	{
		if ($this->queue->removeFromQueue($email)) {
			$this->stdout("Email '{$email}' removed from the queue.\n", Console::FG_GREEN);
		} else {
			$this->stdout("Failed to remove email '{$email}' from the queue.\n", Console::FG_RED);
		}
		return ExitCode::OK;
	}
}
