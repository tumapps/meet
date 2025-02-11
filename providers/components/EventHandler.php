<?php

namespace helpers;

use Yii;
use yii\base\Event;
use helpers\traits\Keygen;
use app\providers\components\MailQueueManager;

class EventHandler
{
	use Keygen;

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
		$email = $event->data['email'];
		$subject = $event->data['subject'];
		$body = $event->data['body'];

		// self::addEmailToQueue($email, $subject, $body);
		self::queueEmail($email, $subject, $body);

		$event->handled = true;
	}

	public static function onCreatedAppointment(Event $event)
	{
		$contactPersonEmail = $event->data['contact_person_email'];
		$subject = $event->data['subject'];
		$chairPersonEmail = $event->data['chair_person_email'];
		$attendeesDetails = $event->data['attendees_details'];
		$appointmentId = $event->data['appointment_id'];

		$commonData = [
			'date' => $event->data['date'],
			'startTime' => $event->data['start_time'],
			'endTime' => $event->data['end_time'],
			'username' => $event->data['contact_person_username'],
			'contact_name' => $event->data['contact_person_name'],
			'attachment_file_name' => $event->data['attachment_file_name'],
			'attachment_download_link' => $event->data['attachment_download_link'],
		];

		$contactPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
			'recipientType' => 'contact_person',
		]));

		// self::addEmailToQueue($contactPersonEmail, $subject, $contactPersonEmailBody);
		self::queueEmail($contactPersonEmail, $subject, $contactPersonEmailBody);

		$chairPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
			'recipientType' => 'chair_person',
		]));

		// self::addEmailToQueue($chairPersonEmail, $subject, $chairPersonEmailBody);
		self::queueEmail($chairPersonEmail, $subject, $chairPersonEmailBody);


		if (!empty($attendeesDetails)) {
			foreach ($attendeesDetails as $attendeeDetail) {
				$attendeeEmail = $attendeeDetail['email'];
				$attendee_id = $attendeeDetail['staff_id'];
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$confirmationBaseUrl = Yii::$app->params['confirmationLink'];
				// $appointmentHash = hash_hmac('sha256', $appointmentId, Yii::$app->params['secret_key']);
				// $attendeeHash = hash_hmac('sha256', $attendee_id, Yii::$app->params['secret_key']);

				$appointmentEnc = self::encryptData($appointmentId);
				$attendeeIdEnc = self::encryptData($attendee_id);

				$confirmationLink = $confirmationBaseUrl . '/' . $appointmentEnc . '/' . $attendeeIdEnc;

				$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName,
					'confirmationLink' => $confirmationLink,
				]));
				// self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
				self::queueEmail($attendeeEmail, $subject, $attendeeEmailBody);
			}
		}
	}

	public static function onAccountCreation(Event $event)
	{
		$email = $event->data['email'];
		$subject = $event->data['subject'];
		$username = $event->data['username'];
		$loginLink = $event->data['loginLink'];
		$name = $event->data['name'];

		$body = Yii::$app->view->render('@ui/views/emails/accountCreated', [
			'username' => $username,
			'loginLink' => $loginLink,
			'contact_name' => $name,
		]);

		// self::addEmailToQueue($email, $subject, $body);
		self::queueEmail($email, $subject, $body);
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

		$contactPerson = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
			'recipientType' => 'contactPerson',
		]));

		// self::addEmailToQueue($email, $subject, $contactPerson);
		self::queueEmail($email, $subject, $contactPerson);


		$chairPerson = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
			'recipientType' => 'chairPerson',
		]));

		self::addEmailToQueue($bookedUserEmail, $subject, $chairPerson);
		self::queueEmail($bookedUserEmail, $subject, $chairPerson);


		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName
				]));
				// self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
				self::queueEmail($attendeeEmail, $subject, $attendeeEmailBody);
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

		// self::addEmailToQueue($contactEmail, $event->data['subject'], $userBody);
		self::queueEmail($contactEmail, $event->data['subject'], $userBody);


		$vcBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
			'name' => $event->data['contact_name'],
			'recipientType' => 'vc', // Specify the recipient type
		]));

		// self::addEmailToQueue($bookedUserEmail, $event->data['subject'], $vcBody);
		self::queueEmail($bookedUserEmail, $event->data['subject'], $vcBody);


		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName
				]));
				// self::addEmailToQueue($attendeeEmail, $event->data['subject'], $attendeeEmailBody);
				self::queueEmail($attendeeEmail, $event->data['subject'], $attendeeEmailBody);
			}
		}
	}

	public static function onAttendeeUpdate(Event $event)
	{

		$email = $event->data['email'];

		$commonData = [
			'appointment_name' => $event->data['appointment_subject'],
			'date' => $event->data['appointment_date'],
			'start_time' => $event->data['start_time'],
			'end_time' => $event->data['end_time'],
			'subject' => $event->data['subject'],
		];

		$attendeeName = substr($email, 0, strpos($email, '@'));

		$body = Yii::$app->view->render('@ui/views/emails/attendeeUpdate', array_merge($commonData, [
			'contact_name' => $attendeeName,
			'reason' => $event->data['reason'] ?? '',
			'is_removed' => $event->data['is_removed']
		]));

		// self::addEmailToQueue($email, $event->data['subject'], $body);
		self::queueEmail($$email, $event->data['subject'], $body);
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

		$body = Yii::$app->view->render(
			'@ui/views/emails/appointmentAffected',
			array_merge($commonData, [
				'name' => $event->data['name'],
				'recipientType' => 'user',
			])
		);

		// self::addEmailToQueue($email, $subject, $body);
		self::queueEmail($email, $subject, $body);


		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentAffected', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName
				]));
				// self::addEmailToQueue($attendeeEmail, $subject, $attendeeEmailBody);
				self::queueEmail($attendeeEmail, $subject, $attendeeEmailBody);
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

		$body = Yii::$app->view->render(
			'@ui/views/emails/appointmentRescheduled',
			array_merge($commonData, [
				'recipientType' => 'user',
				'name' => $event->data['name'],
			])
		);

		// self::addEmailToQueue($email, $subject, $body);
		self::queueEmail($email, $subject, $body);


		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$attendeeBody = Yii::$app->view->render('@ui/views/emails/appointmentRescheduled', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName
				]));
				self::queueEmail($attendeeEmail, $subject, $attendeeBody);
			}
		}
	}

	public static function onAppointmentDateUpdated(Event $event)
	{
		$email = $event->data['email'];
		$subject = $event->data['subject'];
		$attendeesEmails = $event->data['attendees_emails'];

		$commonData = [
			'username' => $event->data['username'],  // chair of the meeting
			'current_date' => $event->data['current_date'],
			'initial_date' => $event->data['initial_date'],
			'start_time' => $event->data['start_time'],
			'end_time' => $event->data['end_time'],
		];

		$body = Yii::$app->view->render(
			'@ui/views/emails/appointmentDateUpdated',
			array_merge($commonData, [
				'recipientType' => 'contact_person', // contact person
				'contact_person_name' => $event->data['contact_person_name'],
			])
		);

		self::queueEmail($email, $subject, $body);

		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));
				$attendeeBody = Yii::$app->view->render('@ui/views/emails/appointmentDateUpdated', array_merge($commonData, [
					'recipientType' => 'attendee',
					'attendeeName' => $attendeeName
				]));
				self::queueEmail($attendeeEmail, $subject, $attendeeBody);
			}
		}
	}

	public static function onAffectedAppointments(Event $event)
	{
		$email = $event->data['email'];
		$subject = $event->data['subject'];

		$body = Yii::$app->view->render('@ui/views/emails/appointmentsNeedReschedule', [
			'affectedAppointments' => $event->data['appointments'],
		]);

		// self::send($email, $subject, $body);
		self::queueEmail($email, $subject, $body);
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

		$body = Yii::$app->view->render(
			'@ui/views/emails/appointmentReminder',
			array_merge($commonData, [
				'contact_name' => $event->data['contact_name'],
				'recipientType' => 'user'
			])
		);

		// self::send($email, $subject, $body);
		self::queueEmail($email, $subject, $body, 'reminder', $id);

		if (!empty($attendeesEmails)) {
			foreach ($attendeesEmails as $attendeeEmail) {
				$attendeeName = substr($attendeeEmail, 0, strpos($attendeeEmail, '@'));

				$attendeeBody = Yii::$app->view->render(
					'@ui/views/emails/appointmentReminder',
					array_merge($commonData, [
						'attendeeName' => $attendeeName,
						'recipientType' => 'attendee'
					])
				);
				self::queueEmail($attendeeEmail, $subject, $attendeeBody, 'reminder', $id);
			}
		}
	}

	public static function onBackUpFileUploaded(Event $event)
	{
		$email = $event->data['email'];
		$subject = $event->data['subject'];
		$file_url = $event->data['file_url'];
		$self_link = $event->data['file_url'];

		$body = "Dear Admin,\n\n";
		$body .= "The database backup has been successfully uploaded.\n\n";
		$body .= "You can view or download the backup file using the following links:\n";
		$body .= "Download Link: {$file_url}\n";
		$body .= "Self Link: {$self_link}\n\n";
		$body .= "Thank you for managing the system backup.";

		self::queueEmail($email, $subject, $body);
	}

	public static function queueEmail($email, $subject, $body, $type = null, $id = null)
	{
		Yii::$app->queue->push(new \cmd\controllers\MailJob([
			'email' => $email,
			'subject' => $subject,
			'body' => $body,
			'type' => $type,
			'id' => $id,
		]));

		Yii::info("Email queued for: {$email}", 'mailQueue');
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
