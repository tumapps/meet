<?php

namespace scheduler\hooks;


use auth\models\User;
use scheduler\models\AppointmentAttendees;
use scheduler\models\AppointmentAttachments;
use scheduler\models\OperationReasons;
use yii\base\Event;
use helpers\EventHandler;

trait NotificationTrait
{
    const EVENT_APPOINTMENT_CANCELLED = 'appointmentCancelled';
    const EVENT_APPOINTMENT_RESCHEDULE = 'appointmentReschedule';
    const EVENT_APPOINTMENT_RESCHEDULED = 'appointmentRescheduled';
    const EVENT_AFFECTED_APPOINTMENTS = 'affectedAppointments';
    const EVENT_APPOINTMENT_REMINDER = 'appointmentReminder';
    const EVENT_APPOINTMENT_CREATED = 'appointmentCreated';
    const EVENT_APPOINTMENT_REJECTED = 'appointmentRejected';
    const EVENT_APPOINTMENT_DATE_UPDATED = 'appointmentUpdated';
    const EVENT_APPOINTMENT_VENUE_UPDATE = 'appointmentVenueUpdated';
    const EVENT_APPOINTMENT_COMPLETED_REMINDER = 'appointmentCompletedReminder';

    public function sendAppointmentsReminderEvent()
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Reminder';

        $user = User::findOne($this->user_id);
        $chairPersonEmail = $user->profile->email_address;

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id, false, true);

        $eventData = [
            'contact_email' => $this->email_address,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'description' => $this->description,
            'date' => $this->appointment_date,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'contact_name' => $this->contact_name,
            'username' => $this->getUserName($this->user_id),
            'chairPersonEmail' => $chairPersonEmail,
            'appointment_id' => $this->id,
            'attendees_emails' => $attendeesEmails,
        ];

        $this->on(self::EVENT_APPOINTMENT_REMINDER, [EventHandler::class, 'onAppointmentReminder'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_REMINDER, $event);
    }


    public function sendAppointmentCancelledEvent()
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Cancelled';

        $user = User::findOne($this->user_id);
        $chairPersonEmail = $user->profile->email_address;

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);
        $cancellation_reason = OperationReasons::getActionReason($this->id, $this->user_id);

        $eventData = [
            'contactPersonEmail' => $this->email_address,
            'contact_name' => $this->contact_name,
            'date' => $this->appointment_date,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'chairPersonEmail' => $chairPersonEmail,
            'cancellation_reason' => $cancellation_reason,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'attendees_emails' => $attendeesEmails,

        ];
        $this->on(self::EVENT_APPOINTMENT_CANCELLED, [EventHandler::class, 'onAppointmentCancelled'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_CANCELLED, $event);
    }

    public function sendAppointmentCreatedEvent()
    {

        $event = new Event();
        $event->sender = $this;

        $user = User::findOne($this->user_id);
        $chairPersonEmail = $user->profile->email_address;

        $attendeesDetails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id, true, false);
        $attachementFile = AppointmentAttachments::getAppointmentAttachment($this->id);

        $fileName = null;
        $downloadLink = null;

        if ($attachementFile !== null) {
            $fileName = $attachementFile['fileName'];
            $downloadLink = $attachementFile['downloadLink'];
        }

        $eventData = [
            'appointment_id' => $this->id,
            'contact_person_email' => $this->email_address,
            'subject' => $this->subject,
            'appointment_subject' => $this->subject,
            'description' => $this->description,
            'contact_person' => $this->contact_name,
            'date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'chair_person' => $this->getUserName($this->user_id),
            'chair_person_email' => $chairPersonEmail,
            'attendees_details' => $attendeesDetails,
            'attachment_file_name' => $fileName,
            'attachment_download_link' => $downloadLink,
        ];

        $this->on(self::EVENT_APPOINTMENT_CREATED, [EventHandler::class, 'onCreatedAppointment'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_CREATED, $event);
    }

    public function sendAppointmentVenueUpdateEvent($new_venue_id, $previous_venue_id)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Meeting Venue Updated';

        $user = User::findOne($this->user_id);
        $chairPersonEmail = $user->profile->email_address;

        $attendeesDetails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id, true, false);
        $attachementFile = AppointmentAttachments::getAppointmentAttachment($this->id);

        $fileName = null;
        $downloadLink = null;

        // geting venue detials
        $previousVenue = $this->getSpaceName($previous_venue_id);
        $newVenue = $this->getSpaceName($new_venue_id);

        if ($attachementFile !== null) {
            $fileName = $attachementFile['fileName'];
            $downloadLink = $attachementFile['downloadLink'];
        }

        $eventData = [
            'appointment_id' => $this->id,
            'contact_person_email' => $this->email_address,
            'subject' => $subject,
            'contact_person_name' => $this->contact_name,
            'date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'contact_person_username' => $this->getUserName($this->user_id),
            'chair_person_email' => $chairPersonEmail,
            'attendees_details' => $attendeesDetails,
            'attachment_file_name' => $fileName,
            'attachment_download_link' => $downloadLink,
            'new_venue' => $newVenue,
            'previous_venue' => $previousVenue
        ];

        $this->on(self::EVENT_APPOINTMENT_VENUE_UPDATE, [EventHandler::class, 'onAppointmentVenueUpdate'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_VENUE_UPDATE, $event);
    }

    public function sendAppointmentRescheduleEvent($isEvent = false)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Reschedule';

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $this->email_address,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'contact_name' => $this->contact_name,
            'chair_person' => $this->getUserName($this->user_id),
            'attendees_emails' => $attendeesEmails,
            'isEvent' => $isEvent,
        ];

        $this->on(self::EVENT_APPOINTMENT_RESCHEDULE, [EventHandler::class, 'onAppointmentReschedule'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_RESCHEDULE, $event);
    }

    public function sendAppointmentRescheduledEvent()
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Rescheduled';
        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $this->email_address,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'date' => $this->appointment_date,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'name' => $this->contact_name,
            'username' => $this->getUserName($this->user_id),
            'attendees_emails' => $attendeesEmails,
        ];

        $this->on(self::EVENT_APPOINTMENT_RESCHEDULED, [EventHandler::class, 'onAppointmentRescheduled'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_RESCHEDULED, $event);
    }

    public function sendAppointmentDateUpdatedEvent($initial_date)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Meeting Date Updates';
        $attendees_emails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $this->email_address,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'current_date' => $this->appointment_date,
            'initial_date' => $initial_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'contact_person_name' => $this->contact_name,
            'chair_person' => $this->getUserName($this->user_id), // chair of the appointment
            'attendees_emails' => $attendees_emails,
        ];

        $this->on(self::EVENT_APPOINTMENT_DATE_UPDATED, [EventHandler::class, 'onAppointmentDateUpdated'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_DATE_UPDATED, $event);
    }

    public function sendAppointmentCompletionReminder()
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Meeting Followup';

        $user = User::findOne($this->user_id);
        $chairPersonEmail = $user->profile->email_address;

        $eventData = [
            'contact_email' => $this->email_address,
            'subject' => $subject,
            'appointment_subject' => $this->subject,
            'date' => $this->appointment_date,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'contact_name' => $this->contact_name,
            'username' => $this->getUserName($this->user_id),
            'chairPersonEmail' => $chairPersonEmail,
            'appointment_id' => $this->id,
        ];

        $this->on(self::EVENT_APPOINTMENT_COMPLETED_REMINDER, [EventHandler::class, 'onAppointmentCompletedReminder'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_COMPLETED_REMINDER, $event);
    }

    public function sendAppointmentRejectedEvent($isSpaceRequestUpdate = false)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Rejected';

        $user = User::findOne($this->user_id);
        $bookedUserEmail = $user->profile->email_address;

        $rejection_reason = OperationReasons::getActionReason($this->id, $this->user_id);
        $attendeesEmails = [];

        $eventData = [
            'email' => $this->email_address,
            'subject' => $subject,
            'contact_name' => $this->contact_name,
            'date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'username' => $this->getUserName($this->user_id),
            'user_email' => $bookedUserEmail,
            'attendees_emails' => $attendeesEmails,
            'rejection_reason' => $rejection_reason,
            'spaceRequestUpdate' => $isSpaceRequestUpdate
        ];

        $this->on(self::EVENT_APPOINTMENT_REJECTED, [EventHandler::class, 'onAppointmentRejected'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_REJECTED, $event);
    }

    public function sendAffectedAppointmentsEvent($appointments, $isEvent = false)
    {
        $user_id = $appointments[0]->user_id;
        $user = $user = User::findOne($user_id);
        $userEmail = $user->profile->email_address;

        $event = new Event();
        $event->sender = $this;
        $subject = 'Affected Appointments';

        $eventData = [
            'email' => $userEmail,
            'subject' => $subject,
            'appointments' => $appointments,
            'isEvent' => $isEvent
        ];

        $this->on(self::EVENT_AFFECTED_APPOINTMENTS, [EventHandler::class, 'onAffectedAppointments'], $eventData);
        $this->trigger(self::EVENT_AFFECTED_APPOINTMENTS, $event);
    }
}
