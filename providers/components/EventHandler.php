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

	public static function onAppointmentCancelled($event)
    {
        $contactEmail = $event->data['contactEmail'];
        $bookedUserEmail = $event->data['bookedUserEmail'];
    }
}