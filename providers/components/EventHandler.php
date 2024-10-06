<?php

namespace helpers;

use Yii;
use yii\base\Event;
use helpers\traits\Mail;


class EventHandler
{
	use Mail;


	public static function handlePasswordResetRequest(Event $event)
	{
		$data = $event->data;
		 
		if(self::send($data['email'], $data['subject'], $data['body'])){
			$event->handled = true;
		} else {
			$event->handled = false;
		}
	}

	public static function onAppointmentCancelled(Event $event)
    {
    	 
        $contactEmail = $event->data['contactEmail'];
        $bookedUserEmail = $event->data['bookedUserEmail'];

         $commonData = [
	        'date' => $event->data['date'],
	        'startTime' => $event->data['startTime'],
	        'endTime' => $event->data['endTime'],
	        'contactLink' => 'https://localhost',
    	];

    	$userBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
	        'name' => $event->data['contact_name'],
	        'recipientType' => 'user',
    	]));

        // notify contact user
        self::send($contactEmail, $event->data['subject'], $userBody);

        $vcBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
	        'recipientType' => 'vc', // Specify the recipient type
    	]));

        self::send($bookedUserEmail, $event->data['subject'], $vcBody);
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

    	self::send($email, $subject, $body);
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

        self::send($email, $subject, $body);
    }

    public static function onAffectedAppointments(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = Yii::$app->view->render('@ui/views/emails/appointmentsNeedReschedule',[
    		'affectedAppointments' => $event->data['appointments'],
    	]);

    	self::send($email, $subject, $body);
    }

    public static function onAppointmentReminder(Event $event)
    {
    	$email = $event->data['email'];
    	$subject = $event->data['subject'];

    	$body = '';

    	self::send($email, $subject, $body);
    }
}