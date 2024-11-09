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
    	$bookedUserEmail = $event->data['user_email'];
    	$attendeesEmails = $event->data['attendees_emails'];

    	$commonData = [
    		'date' => $event->data['date'],
    		'startTime' => $event->data['start_time'],
    		'endTime' => $event->data['end_time'],
    		'username' => $event->data['username'],
    		'contact_name' => $event->data['contact_name'],
    	];

    	$userEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
	        'recipientType' => 'user',
    	]));

    	self::addEmailToQueue($email, $subject, $userEmailBody);

    	$vcEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
	        'recipientType' => 'vc',
    	]));

    	self::addEmailToQueue($bookedUserEmail, $subject, $vcEmailBody);

    	if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
        		$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
            	'recipientType' => 'attendee',
            	'attendeeName' => $attendeeName
        		]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
    		}
    	}
	}

	public static function onAppointmentRejected(Event $event)
	{
		$email = $event->data['email'];
    	$subject = $event->data['subject'];
    	$bookedUserEmail = $event->data['user_email'];
    	$attendeesEmails = $event->data['attendees_emails'];

    	$commonData = [
    		'date' => $event->data['date'],
    		'startTime' => $event->data['start_time'],
    		'endTime' => $event->data['end_time'],
    		'username' => $event->data['username'],
    		'contact_name' => $event->data['contact_name'],
    		'rejectionReason' => $event->data['rejection_reason']
    	];

    	$userEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
	        'recipientType' => 'user',
    	]));

    	self::addEmailToQueue($email, $subject, $userEmailBody);

    	$vcEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
	        'recipientType' => 'vc',
    	]));

    	self::addEmailToQueue($bookedUserEmail, $subject, $vcEmailBody);

    	if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
        		$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
            	'recipientType' => 'attendee',
            	'attendeeName' => $attendeeName
        		]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
    		}
    	}
	}

	public static function onAppointmentCancelled(Event $event)
    {
    	 
        $contactEmail = $event->data['contactEmail'];
        $bookedUserEmail = $event->data['bookedUserEmail'];
        $attendeesEmails = $event->data['attendees_emails'];

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

        if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
        		$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
            	'recipientType' => 'attendee',
            	'attendeeName' => $attendeeName
        		]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
    		}
    	}
    }

    public static function onAppointmentReschedule(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];
    	$attendeesEmails = $event->data['attendees_emails'];

    	$commonData = [
    		'bookedUserName' => $event->data['bookedUserName'],
    		'supportEmail' => 'example@gmail.com'
    	];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentAffected', 
    		array_merge($commonData, [
    			'name' => $event->data['name'],
    			'recipientType' => 'user',
    	]));

    	// self::send($email, $subject, $body);
    	self::addEmailToQueue($email, $subject, $body);

    	if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
        		$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentAffected', array_merge($commonData, [
            		'recipientType' => 'attendee',
            		'attendeeName' => $attendeeName
        		]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
    		}
    	}
    }

    public static function onAppointmentRescheduled(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];
    	$attendeesEmails = $event->data['attendees_emails'];

    	$commonData = [
    		'username' => $event->data['username'],
    		'date' => $event->data['date'],
        	'startTime' => $event->data['startTime'],
        	'endTime' => $event->data['endTime'],
    	];

        $body = Yii::$app->view->render('@ui/views/emails/appointmentRescheduled', 
        	array_merge($commonData, [
        	'recipientType' => 'user',
        	'name' => $event->data['name'],
        ]));

        self::addEmailToQueue($email, $subject, $body);

        if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
        		$attendeeBody = Yii::$app->view->render('@ui/views/emails/appointmentRescheduled', array_merge($commonData, [
            		'recipientType' => 'attendee',
            		'attendeeName' => $attendeeName
        		]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeBody);
    		}
    	}
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
    	$attendeesEmails = $event->data['attendees_emails'];
    	// getting the appoiment id to update the reminder_sent_at when the reminder is sent
    	$id = $event->data['appointment_id']; 

    	$commonData = [
    		'date' => $event->data['date'],
        	'startTime' => $event->data['startTime'],
        	'endTime' => $event->data['endTime'],
        	'username' => $event->data['username'],
    	];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentReminder', 
    		array_merge($commonData, [
        		'contact_name' => $event->data['contact_name'],
        		'recipientType' => 'user'
        ]));

    	// self::send($email, $subject, $body);
    	self::addEmailToQueue($email, $subject, $body, 'reminder', $id);

    	if(!empty($attendeesEmails)) {
    		foreach ($attendeesEmails as $attendeeEmail) {
    			$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));

        		$attendeeBody = Yii::$app->view->render('@ui/views/emails/appointmentReminder', 
		    		array_merge($commonData, [
		        		'attendeeName' => $attendeeName,
		        		'recipientType' => 'attendee'
		        ]));
        		self::addEmailToQueue($attendeeEmail, $subject, $attendeeBody, 'reminder', $id);
    		}
    	}
    }

    public static function addEmailToQueue($email, $subject, $body, $type = null, $id = null)
	{
		self::initQueue();

	    $emailData = [
	        'email' => $email,
	        'subject' => $subject,
	        'body' => $body,
	        'type' => $type,
	        'id' => $id
	    ];

	    if (self::$mailQueue->addToQueue($emailData)) {
	        Yii::info('Email queued successfully for: ' . $email, __METHOD__);
	    } else {
	        Yii::error('Failed to queue email for: ' . $email, __METHOD__);
	    }
	}
}