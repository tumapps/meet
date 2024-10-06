<?php

namespace cmd\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use scheduler\models\Appointments;
use yii\helpers\Console;


class AppointmentController extends Controller
{
	public function actionHello()
	{
		$this->stdout('Hello');
	}

	public function actionCheckPassedAppointments()
    {

        $appointments = Appointments::markPassedAppointmentsInactive();
        
        foreach($appointments as $appointment){
        	console::output($appointment->appointment_date);
        }

    }

     public function actionSendAppointmentReminders()
     {

     	$reminders = Appointments::getUpcomingAppointmentsForReminder();

     	if(!empty($reminders)) {
     		foreach($reminders as $reminder){
     			Console::output($reminder->start_time);
                // new Appointments()->sendAppointmentsReminderEvent($reminder->email_address, $reminder->start_time, $reminder->user_id);
     		}
     	} else {
     		$this->stdout('No reminders ');
     	}
     }
}
