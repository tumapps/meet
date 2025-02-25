<?php

namespace cmd\controllers;

// use Yii;
use yii\console\Controller;
use scheduler\models\Appointments;
use yii\helpers\Console;


class AppointmentController extends Controller
{
    private $appointmentModel;

    public function init()
    {
        parent::init();
        $this->appointmentModel = new Appointments();
    }

    public function actionRun()
    {
        Console::output("Starting appointment cron jobs...\n");

        $this->actionSendAppointmentReminders();
        $this->actionAppointmentsPastOneHour();

        Console::output("Appointment cron jobs completed.\n");
    }


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
        $reminders = Appointments::getUpcomingAppointmentsForReminder();

        if (!empty($reminders)) {
            foreach ($reminders as $reminder) {
                Console::output("Sending reminder for appointment");
                $reminder->sendAppointmentsReminderEvent();

                echo "[*] :: " . "Reminder sent to :" . $reminder->email_address . "\n";
            }
        } else {
            // $this->stdout('No reminders ');
            echo "No reminders to send\n";
        }
    }

    public function actionAppointmentsPastOneHour()
    {
        $appointments = Appointments::getAppointmentsPastOneHour();

        if (empty($appointments)) {
            Console::output('No meeting update reminders found.');
        } else {
            foreach ($appointments as $appointment) {
                // Console::output("Sending ");
                Console::output("Meeting ID: {$appointment->id}, Ended at: {$appointment->appointment_date} {$appointment->end_time}");
                $appointment->sendAppointmentCompletionReminder();
            }
        }
    }
}
