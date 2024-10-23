<?php

namespace helpers;

use Yii;
use yii\base\Event;
// use helpers\traits\Mail;
use PhpAmqpLib\Message\AMQPMessage;
use app\providers\components\MailQueueManager;

class EventHandler
{
	// use Mail;
	private static $mailQueue;

    public function __construct()
    {
        self::initQueue();
    }

    private static function initQueue()
    {
        if (self::$mailQueue === null) {
            self::$mailQueue = new MailQueueManager();
        }
    }


	public static function handlePasswordResetRequest(Event $event)
	{
		$data = $event->data;
		$queueMail = new MailQueueManager();
		 
		$emailData = [
			'email' => $data['email'],
			'subject' => $data['subject'], 
			'body' => $data['body']
		];

		if($queueMail->addToQueue($emailData)){
			$event->handled = true;
		} else {
			$event->handled = false;
		}
	}

	public static function onCreatedAppointment(Event $event)
	{
		$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentCreated', [
    		'contact_name' => $event->data['contact_name'],
    		'date' => $event->data['date'],
    		'startTime' => $event->data['start_time'],
    		'endTime' => $event->data['end_time'],
    		'username' => $event->data['username'],
    	]);

    	self::addEmailToQueue($email, $subject, $body);
	}

	public static function onAppointmentCancelled(Event $event)
    {
    	 
        $contactEmail = $event->data['contactEmail'];
        $bookedUserEmail = $event->data['bookedUserEmail'];

         $commonData = [
	        'date' => $event->data['date'],
	        'startTime' => $event->data['startTime'],
	        'endTime' => $event->data['endTime'],
	        'reason' => $event->data['cancellation_reason'],
	        'contactLink' => 'https://localhost',
    	];

    	$userBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
	        'name' => $event->data['contact_name'],
	        'recipientType' => 'user',
    	]));

        // notify contact user
        // self::send($contactEmail, $event->data['subject'], $userBody);
        self::addEmailToQueue($contactEmail, $event->data['subject'], $userBody);

        $vcBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
	        'name' => $event->data['contact_name'],
	        'recipientType' => 'vc', // Specify the recipient type
    	]));

        // self::send($bookedUserEmail, $event->data['subject'], $vcBody);
        self::addEmailToQueue($bookedUserEmail, $event->data['subject'], $vcBody);
    }

    public static function onAppointmentReschedule(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentAffected', [
    		'name' => $event->data['name'],
    		'bookedUserName' => $event->data['bookedUserName'],
    		'supportEmail' => 'example@gmail.com'
    	]);

    	// self::send($email, $subject, $body);
    	self::addEmailToQueue($email, $subject, $body);
    }

    public static function onAppointmentRescheduled(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

        $body = Yii::$app->view->render('@ui/views/emails/appointmentRescheduled',[
        	'name' => $event->data['name'],
        	'date' => $event->data['date'],
        	'startTime' => $event->data['startTime'],
        	'endTime' => $event->data['endTime'],
        ]);

        // self::send($email, $subject, $body);
        self::addEmailToQueue($email, $subject, $body);
    }

    public static function onAffectedAppointments(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentsNeedReschedule',[
    		'affectedAppointments' => $event->data['appointments'],
    	]);

    	// self::send($email, $subject, $body);
    	self::addEmailToQueue($email, $subject, $body);
    }

    public static function onAppointmentReminder(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentReminder',[
        	'date' => $event->data['date'],
        	'startTime' => $event->data['startTime'],
        	'endTime' => $event->data['endTime'],
        	'contact_name' => $event->data['contact_name'],
        	'username' => $event->data['username'],
        ]);

    	// self::send($email, $subject, $body);
    	self::addEmailToQueue($email, $subject, $body);
    }

    public static function addEmailToQueue($email, $subject, $body)
	{
		self::initQueue();

	    $emailData = [
	        'email' => $email,
	        'subject' => $subject,
	        'body' => $body,
	    ];

	    if (self::$mailQueue->addToQueue($emailData)) {
	        Yii::info('Email queued successfully for: ' . $email, __METHOD__);
	    } else {
	        Yii::error('Failed to queue email for: ' . $email, __METHOD__);
	    }
	}
}