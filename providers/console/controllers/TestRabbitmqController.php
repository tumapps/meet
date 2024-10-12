<?php

namespace cmd\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use PhpAmqpLib\Message\AMQPMessage;
use yii\helpers\Console;

class TestRabbitmqController extends Controller
{
	public function actionEnqueue()
	{
		$rabbitmq = Yii::$app->rabbitmq;

		$data = [
			'email' => 'test@gmail.com',
			'subject' => 'Tes Mail',
			'body' => 'This is Test email sent via yii2 and rabbitmq'
		];

		if($rabbitmq->enqueueEmail($data)){
			echo "successfully enqueued email\n";
		} else {
			echo "Failed to enqueue email\n";
		}
	}
}