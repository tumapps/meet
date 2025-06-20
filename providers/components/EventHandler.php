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
			'chairPerson' => $event->data['chair_person'],
			'contactPerson' => $event->data['contact_person'],
			'attachment_file_name' => $event->data['attachment_file_name'],
			'attachment_download_link' => $event->data['attachment_download_link'],
			'subject' => $event->data['subject'],
			'description' => $event->data['description'],
		];

		$contactPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
			'recipientType' => 'contact_person',
		]));

		// self::addEmailToQueue($contactPersonEmail, $subject, $contactPersonEmailBody);
		self::queueEmail($contactPersonEmail, 'Meeting Confirmed', $contactPersonEmailBody);

		$chairPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentCreated', array_merge($commonData, [
			'recipientType' => 'chair_person',
		]));

		// self::addEmailToQueue($chairPersonEmail, $subject, $chairPersonEmailBody);
		self::queueEmail($chairPersonEmail, 'Meeting Confirmed', $chairPersonEmailBody);


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
				self::queueEmail($attendeeEmail, 'Meeting Invitation', $attendeeEmailBody);
			}
		}
	}

	//onAppointmentVenueUpdate
	public static function onAppointmentVenueUpdate(Event $event)
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
			'previous_venue' => $event->data['previous_venue'],
			'new_venue' => $event->data['new_venue'],
		];

		$contactPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentVenueUpdate', array_merge($commonData, [
			'recipientType' => 'contact_person',
		]));

		// self::addEmailToQueue($contactPersonEmail, $subject, $contactPersonEmailBody);
		self::queueEmail($contactPersonEmail, $subject, $contactPersonEmailBody);

		$chairPersonEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentVenueUpdate', array_merge($commonData, [
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

				$attendeeEmailBody = Yii::$app->view->render('@ui/views/emails/appointmentVenueUpdate', array_merge($commonData, [
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
			'rejectionReason' => $event->data['rejection_reason'],
			'spaceRequestUpdate' => $event->data['spaceRequestUpdate']
		];

		$contactPerson = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
			'recipientType' => 'contactPerson',
		]));

		// self::addEmailToQueue($email, $subject, $contactPerson);
		self::queueEmail($email, $subject, $contactPerson);


		$chairPerson = Yii::$app->view->render('@ui/views/emails/appointmentRejected', array_merge($commonData, [
			'recipientType' => 'chairPerson',
		]));

		// self::addEmailToQueue($bookedUserEmail, $subject, $chairPerson);
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
		$contactPersonEmail = $event->data['contactPersonEmail'];
		$chairPerson = $event->data['chairPersonEmail'];
		$attendeesEmails = $event->data['attendees_emails'];

		$commonData = [
			'date' => $event->data['date'],
			'startTime' => $event->data['startTime'],
			'endTime' => $event->data['endTime'],
			'reason' => $event->data['cancellation_reason'],
			'appointment_subject' => $event->data['appointment_subject'],
		];

		$userBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
			'name' => $event->data['contact_name'],
			'recipientType' => 'user',
		]));

		// self::addEmailToQueue($contactPersonEmail, $event->data['subject'], $userBody);
		self::queueEmail($contactPersonEmail, $event->data['subject'], $userBody);


		$vcBody = Yii::$app->view->render('@ui/views/emails/appointmentCancelled', array_merge($commonData, [
			'name' => $event->data['contact_name'],
			'recipientType' => 'vc', // Specify the recipient type
		]));

		// self::addEmailToQueue($chairPerson, $event->data['subject'], $vcBody);
		self::queueEmail($chairPerson, $event->data['subject'], $vcBody);


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
			'meeting_subject' => $event->data['meeting_subject'],
			'date' => $event->data['appointment_date'],
			'start_time' => $event->data['start_time'],
			'end_time' => $event->data['end_time'],
			'subject' => $event->data['subject'],
		];

		$attendeeName = substr($email, 0, strpos($email, '@'));

		$confirmationBaseUrl = Yii::$app->params['confirmationLink'];


		$appointmentEnc = self::encryptData($event->data['meeting_id']);
		$attendeeIdEnc = self::encryptData($event->data['attendee_id']);

		$confirmationLink = $confirmationBaseUrl . '/' . $appointmentEnc . '/' . $attendeeIdEnc;

		$body = Yii::$app->view->render('@ui/views/emails/attendeeUpdate', array_merge($commonData, [
			'contact_name' => $attendeeName,
			'reason' => $event->data['reason'] ?? '',
			'is_removed' => $event->data['is_removed'],
			'confirmationLink' => $confirmationLink,
		]));

		// self::addEmailToQueue($email, $event->data['subject'], $body);
		self::queueEmail($email, $event->data['subject'], $body);
	}

	public static function onAppointmentReschedule(Event $event)
	{
		$email = $event->data['email'];
		$subject = $event->data['subject'];
		$appointment_subject = $event->data['appointment_subject'];
		$attendeesEmails = $event->data['attendees_emails'];

		$commonData = [
			'chairPerson' => $event->data['chair_person'],
			'appointmentSubject' => $appointment_subject,
			'date' => $event->data['date'],
			'start_time' => $event->data['start_time'],
			'end_time' => $event->data['end_time'],
			'isEvent' => $event->data['isEvent']
		];

		$body = Yii::$app->view->render(
			'@ui/views/emails/appointmentAffected',
			array_merge($commonData, [
				'contact_name' => $event->data['contact_name'],
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
			'appointment_subject' => $event->data['appointment_subject'],
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
			'chairPerson' => $event->data['chair_person'],  // chair of the meeting
			'appointment_subject' => $event->data['appointment_subject'],
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
		$isEvent = $event->data['isEvent'];

		$body = Yii::$app->view->render('@ui/views/emails/appointmentsNeedReschedule', [
			'affectedAppointments' => $event->data['appointments'],
			'isEvent' => $isEvent,
		]);

		// self::send($email, $subject, $body);
		self::queueEmail($email, $subject, $body);
	}

	public static function onAppointmentReminder(Event $event)
	{
		$contact_person_email = $event->data['contact_email'];
		$chair_person_email = $event->data['chairPersonEmail'];
		$subject = $event->data['subject'];
		$attendeesEmails = $event->data['attendees_emails'];
		// getting the appoiment id to update the reminder_sent_at when the reminder is sent
		$id = $event->data['appointment_id'];

		$commonData = [
			'appointment_subject' => $event->data['appointment_subject'],
			'date' => $event->data['date'],
			'startTime' => $event->data['startTime'],
			'endTime' => $event->data['endTime'],
			'username' => $event->data['username'],
			'contact_person_name' => $event->data['contact_name'],
		];

		$contactPersonBody = Yii::$app->view->render(
			'@ui/views/emails/appointmentReminder',
			array_merge($commonData, [
				'recipientType' => 'contact_person'
			])
		);

		self::queueEmail($contact_person_email, $subject, $contactPersonBody, 'reminder', $id);

		$chairPersonBody = Yii::$app->view->render(
			'@ui/views/emails/appointmentReminder',
			array_merge($commonData, [
				'recipientType' => 'chair_person',
			])
		);

		self::queueEmail($chair_person_email, $subject, $chairPersonBody, 'reminder', $id);

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

	public static function onAppointmentCompletedReminder(Event $event)
	{
		// $contact_person_email = $event->data['contact_email'];
		$chair_person_email = $event->data['chairPersonEmail'];
		$subject = $event->data['subject'];
		$id = $event->data['appointment_id'];
		$data = [
			'meeting_subject' => $event->data['appointment_subject'],
			'date' => $event->data['date'],
			'startTime' => $event->data['startTime'],
			'endTime' => $event->data['endTime'],
			'username' => $event->data['username'],
			'contact_person_name' => $event->data['contact_name'],
		];

		$mailBody = Yii::$app->view->render('@ui/views/emails/appointmentCompletedReminder',$data);

		self::queueEmail($chair_person_email, $subject, $mailBody, 'reminder', $id, true);
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

	public static function queueEmail($email, $subject, $body, $type = '', $id = '', $appointmentcompleted = false)
	{
		Yii::$app->queue->push(new \scheduler\jobs\MailJob([
			'email' => $email,
			'subject' => $subject,
			'body' => $body,
			'type' => $type,
			'id' => $id,
			'appointmentcompleted' => $appointmentcompleted
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
