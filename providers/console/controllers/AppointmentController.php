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

        $appointments = Appointments::updatePassedAppointments();
        
        console::output($appointments);

    }

     public function actionSendAppointmentReminders()
     {
        // $date = date('Y-m-d H:1:s');
     	$reminders = Appointments::getUpcomingAppointmentsForReminder();
        $appointment = new Appointments();

     	if(!empty($reminders)) {
     		foreach($reminders as $reminder){
     			Console::output($reminder->start_time);
                $appointment->sendAppointmentsReminderEvent(
                    $reminder->email_address,
                    $reminder->contact_name, 
                    $reminder->appointment_date, 
                    $reminder->start_time,
                    $reminder->end_time, 
                    $reminder->user_id);
                echo "[*] :: " . "Reminder sent for :" . $reminder->email_address . "\n";
     		}
     	} else {
     		// $this->stdout('No reminders ');
            echo "No reminders \n";
     	}
     }
}
