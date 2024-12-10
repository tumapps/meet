<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
use scheduler\models\AppointmentAttendees;
use scheduler\models\AppointmentAttachments;
use scheduler\models\Events;
use scheduler\hooks\TimeHelper;
use scheduler\models\SpaceAvailability;
use yii\base\Event;
use scheduler\models\Availability;
use helpers\EventHandler;

/**
 *@OA\Schema(
 *  schema="Appointments",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="user_id", type="int",title="User id", example="int"),
 *  @OA\Property(property="appointment_date", type="string",title="Date", example="string"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="contact_name", type="string",title="Contact name", example="string"),
 *  @OA\Property(property="email_address", type="string",title="Email address", example="string"),
 *  @OA\Property(property="mobile_number", type="string",title="Mobile number", example="string"),
 *  @OA\Property(property="subject", type="string",title="Subject", example="string"),
 *  @OA\Property(property="appointment_type", type="string",title="Appointment type", example="string"),
 *  @OA\Property(property="status", type="string",title="Status", example="string"),
 *  @OA\Property(property="created_at", type="string",title="Created at", example="string"),
 *  @OA\Property(property="updated_at", type="string",title="Updated at", example="string"),
 * )
 */

class Appointments extends BaseModel
{
    const STATUS_MISSED = 9;
    const STATUS_ATTENDED = 6;
    const STATUS_ACTIVE = 10;
    const STATUS_PENDING = 11;
    const STATUS_RESCHEDULE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_RESCHEDULED = 5;
    const STATUS_DELETED = 0;
    const STATUS_REJECTED = 2;

    // appointment priorities

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    const EVENT_APPOINTMENT_CANCELLED = 'appointmentCancelled';
    const EVENT_APPOINTMENT_RESCHEDULE = 'appointmentReschedule';
    const EVENT_APPOINTMENT_RESCHEDULED = 'appointmentRescheduled';
    const EVENT_AFFECTED_APPOINTMENTS = 'affectedAppointments';
    const EVENT_APPOINTMENT_REMINDER = 'appointmentReminder';
    const EVENT_APPOINTMENT_CREATED = 'appointmentCreated';
    const EVENT_APPOINTMENT_REJECTED = 'appointmentRejected';


    protected static $statusLabels = [
        self::STATUS_MISSED => 'Missed',
        self::STATUS_ATTENDED => 'Attended',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_RESCHEDULE => 'Reschedule',
        self::STATUS_RESCHEDULED => 'Rescheduled',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_DELETED => 'Deleted',
    ];

    const SCENARIO_CANCEL = 'cancel';
    const SCENARIO_REJECT = 'reject';


    public $attendees = [];
    public $space_id;

    

    public function init()
    {
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%appointments}}';
    }
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(),
            [
                'id',
                'user_id',
                'appointment_date',
                'start_time',
                'end_time',
                'contact_name',
                'email_address',
                'mobile_number',
                'cancellation_reason',
                'subject',
                'appointment_type',
                'status',
                'recordStatus' => function () {
                    return $this->recordStatus;
                },
                'created_at',
                'updated_at',
            ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id', 'status'], 'integer'],
            [['appointment_date', 'email_address', 'start_time', 'end_time', 'user_id', 'subject', 'contact_name', 'mobile_number', 'appointment_type', 'description'], 'required'],
            [['appointment_date', 'start_time', 'end_time', 'created_at', 'updated_at', 'attendees', 'space_id'], 'safe'],

            // Custom inline validators as separate rules
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['start_time', 'end_time'], 'validateOverlappingEvents'],
            [['start_time', 'end_time'], 'validateAdvanceBooking'],
            [['start_time', 'end_time'], 'validateAvailability'],
            [['start_time', 'end_time'], 'validateOverlappingAppointment'],

            [['appointment_date'], 'validateOverlappingEvents'],
            [['appointment_date'], 'validateBookingWindow'],
            [['appointment_date'], 'validateOverlappingAppointment'],

            [['appointment_date'], 'date', 'format' => 'php:Y-m-d'],
            ['appointment_date', 'date', 'format' => 'php:Y-m-d', 'min' => date('Y-m-d'), 'message' => 'The appointment date must not be in the past'],

            [['subject', 'description'], 'string'],
            [['contact_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            ['email_address', 'email'],
            ['mobile_number', 'string', 'max' => 13, 'tooLong' => 'Phone number must not exceed 13 digits.'],
            ['mobile_number', 'match', 'pattern' => '/^\+?[0-9]{7,15}$/', 'message' => 'Phone number must be a valid integer with a maximum of 13 digits.'],
            [['appointment_type'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],

            // Rules for cancellation and rejection scenarios
            ['cancellation_reason', 'required', 'on' => self::SCENARIO_CANCEL, 'message' => 'Cancellation reason is required.'],
            ['cancellation_reason', 'string', 'max' => 255],
            ['rejection_reason', 'required', 'on' => self::SCENARIO_REJECT, 'message' => 'Rejection reason is required.'],
            ['rejection_reason', 'string', 'max' => 255],
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CANCEL] = ['cancellation_reason'];
        $scenarios[self::SCENARIO_REJECT] = ['rejection_reason'];
        return $scenarios;
    }


    public function validateTimeRange($attribute, $params)
    {
        $currentTime = date('Y-m-d h:i:sa');
        $dateTime = date($this->appointment_date . ' ' . $this->start_time);

        if (strtotime($this->end_time) <= strtotime($this->start_time) || strtotime($dateTime) < strtotime($currentTime)) {
            $this->addError($attribute, 'invalid time range');
        }
    }

    public function validateOverlappingEvents($attribute, $params)
    {
        $hasOverlappingEvents = Events::getOverlappingEvents(
            $this->appointment_date,
            $this->start_time,
            $this->end_time
        );

        if ($hasOverlappingEvents) {
            $this->addError($attribute, 'The selected time overlaps with another event.');
        }
    }

    public function validateOverlappingSpace($attribute, $params)
    {
        $hasOverlappingSpace = SpaceAvailability::getOverlappingSpace(
            $this->space_id,
            $this->appointment_date,
            $this->start_time,
            $this->end_time
        );

        if ($hasOverlappingSpace) {
            $this->addError($attribute, 'The selected space is not available at the chosen time.');
        }
    }

    public function validateAdvanceBooking($attribute, $params)
    {
        $isAdvancedBookingValid = TimeHelper::validateAdvanceBooking(
            $this->user_id,
            $this->start_time,
            $this->appointment_date
        );

        if (!$isAdvancedBookingValid) {
            $this->addError($attribute, 'You cannot book an appointment this soon. Please choose a later time.');
        }
    }

    public function validateBookingWindow($attribute, $params)
    {
        $validateBookingWindow = TimeHelper::isWithinBookingWindow(
            $this->user_id,
            $this->appointment_date
        );

        if (!$validateBookingWindow) {
            $this->addError($attribute, 'The appointment is outside the allowed booking window.');
        }
    }

    public function validateAvailability($attribute, $params)
    {
        $isAvailable = $this->getAvailability(
            $this->user_id,
            $this->appointment_date,
            $this->start_time,
            $this->end_time
        );

        if (!$isAvailable) {
            $this->addError($attribute, 'The selected time is unavailable for booking.');
        }
    }

    public function validateOverlappingAppointment($attribute, $params)
    {
        $appointmentId = $this->id;

        $appointmentExists = self::hasOverlappingAppointment(
            $this->user_id,
            $this->appointment_date,
            $this->start_time,
            $this->end_time,
            $appointmentId,
            // $this->priority
        );

        if ($appointmentExists) {
            $this->addError($attribute, 'An overlapping appointment already exists.');
        }
    }

    public function validateAttendeesAvailability($attribute, $params)
    {
        foreach ($this->attendees as $attendeeId) {
            $appointmentConflict = AppointmentAttendees::isAttendeeUnavailable(
                $attendeeId,
                $this->appointment_date,
                $this->start_time,
                $this->end_time
            );

            if ($appointmentConflict) {
                $this->addError($attribute, 'One or more attendees are already booked for this time slot.');
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'appointment_date' => 'Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'contact_name' => 'Contact Name',
            'email_address' => 'Email Address',
            'mobile_number' => 'Mobile Number',
            'subject' => 'Subject',
            'description' => 'Notes',
            'cancellation_reason' => 'Reason',
            'rejection_reason'  => 'Rejection Reason',
            'appointment_type' => 'Appointment Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    public static function getPriorityLabel()
    {
        return [
            ['code' => self::PRIORITY_LOW, 'label' => 'Low'],
            ['code' => self::PRIORITY_MEDIUM, 'label' => 'Medium'],
            ['code' => self::PRIORITY_HIGH, 'label' => 'High'],
        ];
    }

    public static function getStatusLabel($status)
    {
        return self::$statusLabels[$status] ?? 'Unknown';
    }

    public static function getUserName($user_id)
    {
        return User::find()->select('username')->where(['user_id' => $user_id])->scalar();
    }

    public static function checkedInAppointemnt($id)
    {
        $appointment = self::findOne(['id' => $id]);

        if (!$appointment) {
            // return false;
            return [
                'success' => false,
                'message' => 'Appointment not found.'
            ];
        }

        $currentTime = date('Y-m-d H:i:s');
        $appointmentTime = date('Y-m-d H:i:s', strtotime($appointment->appointment_date . ' ' . $appointment->start_time));

        if ($currentTime < $appointmentTime) {
            return [
                'success' => false,
                'message' => 'You cannot check in before the appointment start time.'
            ];
        }

        // $appointment->checked_in = !$appointment->checked_in;

        $appointment->checked_in = true;
        $appointment->status = self::STATUS_ATTENDED;

        // $appointment->save(false);

        if ($appointment->save(false)) {
            // $message = $appointment->checked_in ? 'Appointment successfully checked in' : '';
            if ($appointment->checked_in) {
                $message = 'Appointment successfully checked in';
            }

            return [
                'success' => true,
                'message' => $message
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update appointment status.'
        ];
    }

    public static function toggleCheckedInAppointment($id)
    {
        $appointment = self::findOne(['id' => $id]);

        if (!$appointment) {
            return false;
        }

        $appointment->checked_in = !$appointment->checked_in;

        return $appointment->save(false);
    }


    public function sendAppointmentCancelledEvent($email, $name, $date, $startTime, $endTime, $bookedUserEmail)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Cancelled';

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'contactEmail' => $email,
            'contact_name' => $name,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'bookedUserEmail' => $bookedUserEmail,
            'cancellation_reason' => $this->cancellation_reason,
            'subject' => $subject,
            'attendees_emails' => $attendeesEmails,

        ];
        $this->on(self::EVENT_APPOINTMENT_CANCELLED, [EventHandler::class, 'onAppointmentCancelled'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_CANCELLED, $event);
    }

    public function sendAppointmentCreatedEvent($id, $email, $name, $user_id, $date, $startTime, $endTime)
    {

        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Created';

        $user = User::findOne($user_id);
        $bookedUserEmail = $user->profile->email_address;

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($id);
        $attachementFile = AppointmentAttachments::getAppointmentAttachment($this->id);

        $fileName = null;
        $downloadLink = null;

        if ($attachementFile !== null) {
            $fileName = $attachementFile['fileName'];
            $downloadLink = $attachementFile['downloadLink'];
        }

        $eventData = [
            'appointment_id' => $this->id,
            'email' => $email,
            'subject' => $subject,
            'contact_name' => $name,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'username' => $this->getUserName($user_id),
            'user_email' => $bookedUserEmail,
            'attendees_emails' => $attendeesEmails,
            'attachment_file_name' => $fileName,
            'attachment_download_link' => $downloadLink,
        ];

        $this->on(self::EVENT_APPOINTMENT_CREATED, [EventHandler::class, 'onCreatedAppointment'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_CREATED, $event);
    }

    public function sendAppointmentRescheduleEvent($email, $name, $bookedUserId)
    {
        $event = new Event();
        $event->sender = $this;
        $userName = User::find()->select('username')->where(['user_id' => $bookedUserId]);
        $subject = 'Appointment Reschedule';

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'name' => $name,
            'bookedUserName' => $userName,
            'attendees_emails' => $attendeesEmails,
        ];

        $this->on(self::EVENT_APPOINTMENT_RESCHEDULE, [EventHandler::class, 'onAppointmentReschedule'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_RESCHEDULE, $event);
    }

    public function sendAppointmentRescheduledEvent($user_id, $email, $date, $startTime, $endTime, $name)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Rescheduled';
        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'date' => $date,
            'sartTime' => $startTime,
            'endTime' => $endTime,
            'name' => $name,
            'username' => $this->getUserName($user_id),
            'attendees_emails' => $attendeesEmails,
        ];

        $this->on(self::EVENT_APPOINTMENT_RESCHEDULED, [EventHandler::class, 'onAppointmentRescheduled'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_RESCHEDULED, $event);
    }

    public static function updatePassedAppointments()
    {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $appointments = self::find()
            ->where(['<=', 'appointment_date', $currentDate])
            ->andWhere(['<', 'end_time', $currentTime])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->all();

        if (empty($appointments)) {
            return 'No appointments to process.';
        }

        foreach ($appointments as $appointment) {
            if (!$appointment->checked_in) {
                // If not checked in, mark the appointment as 'Missed'
                $appointment->status = self::STATUS_MISSED;
            } else {
                // If checked in, mark the appointment as 'Attended'
                $appointment->status = self::STATUS_ATTENDED;
            }

            // Save the appointment and log the result
            if ($appointment->save(false)) {
                Yii::info("Appointment ID {$appointment->id} marked as {$appointment->status}.");
            } else {
                Yii::error("Failed to update status for Appointment ID {$appointment->id}.");
            }
        }

        // return 'Appointments updated successfully.';
        return $appointments;
    }

    public static function getUpcomingAppointmentsForReminder()
    {
        // Get the current time
        $currentTime = new \DateTime();
        $reminderTime = (clone $currentTime)->modify('+30 minutes');

        $appointments = self::find()
            ->where(['=', 'appointment_date', date('Y-m-d')])
            // ->andWhere(['>=', 'start_time', $currentTime])
            // ->andWhere(['<=', 'start_time', $reminderTime])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->andWhere(['reminder_sent_at' => null])
            ->all();

        $upcomingAppointments = [];

        foreach ($appointments as $appointment) {
            $appointmentStartTime = new \DateTime($appointment->start_time);

            // Check if the start time is between the current time and 30 minutes from now
            if ($appointmentStartTime > $currentTime && $appointmentStartTime <= $reminderTime) {
                $upcomingAppointments[] = $appointment;
            }
        }

        return $upcomingAppointments;
    }

    public function sendAppointmentsReminderEvent($id, $email, $contact_name, $date, $start_time, $end_time, $user_id)
    {

        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Reminder';
        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'date' => $date,
            'startTime' => $start_time,
            'endTime' => $end_time,
            'contact_name' => $contact_name,
            'username' => $this->getUserName($user_id),
            'appointment_id' => $id,
            'attendees_emails' => $attendeesEmails,
        ];

        $this->on(self::EVENT_APPOINTMENT_REMINDER, [EventHandler::class, 'onAppointmentReminder'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_REMINDER, $event);
    }

    public function resetReminder()
    {
        $this->updateAttributes(['reminder_sent_at' => null]);
    }

    public static function updateReminder($id)
    {
        $appointment = self::findOne($id);
        if ($appointment) {
            $appointment->updateAttributes(['reminder_sent_at' => date('Y-m-d H:i:s')]);
            Yii::info("Updated reminder_sent_at for appointment ID: {$id}", __METHOD__);
        } else {
            Yii::warning("Appointment not found for ID: {$id}", __METHOD__);
        }
    }

    public function sendAffectedAppointmentsEvent($appointments)
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
        ];

        $this->on(self::EVENT_AFFECTED_APPOINTMENTS, [EventHandler::class, 'onAffectedAppointments'], $eventData);
        $this->trigger(self::EVENT_AFFECTED_APPOINTMENTS, $event);
    }

    public function sendAppointmentRejectedEvent($email, $name, $user_id, $date, $startTime, $endTime)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Rejected';

        $user = User::findOne($user_id);
        $bookedUserEmail = $user->profile->email_address;

        $attendeesEmails = AppointmentAttendees::getAttendeesEmailsByAppointmentId($this->id);

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'contact_name' => $name,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'username' => $this->getUserName($user_id),
            'user_email' => $bookedUserEmail,
            'attendees_emails' => $attendeesEmails,
            'rejection_reason' => $this->rejection_reason,
        ];

        $this->on(self::EVENT_APPOINTMENT_REJECTED, [EventHandler::class, 'onAppointmentRejected'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_REJECTED, $event);
    }

    public function getRescheduledAppointment($id)
    {
        return self::find()
            ->where(['id' => $id, 'status' => self::STATUS_RESCHEDULE])
            ->one();
    }

    public function getActiveAppointments()
    {
        return self::find()->where(['status' => self::STATUS_ACTIVE])->count();
    }

    public function getCancelledAppointments()
    {
        return self::find()->where(['status' => self::STATUS_CANCELLED])->count();
    }

    public function getRescheduleAppointments()
    {
        return self::find()->where(['status' => self::STATUS_RESCHEDULE])->count();
    }

    public function getRescheduledAppointments()
    {
        return self::find()->where(['status' => self::STATUS_RESCHEDULED])->count();
    }

    public function getAllAppointments()
    {
        return self::find()->count();
    }

    public function upComingAppointments($appointment_date = null, $limit = 10)
    {
        if ($appointment_date === null) {
            $appointment_date = date('Y-m-d');
        }

        return self::find()
            ->where(['appointment_date' => $appointment_date])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->andWhere(['>', 'start_time', date('H:i:s')])
            ->orderBy(['start_time' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    private static function getUnavailableSlotsQuery($user_id, $appointment_date, $start_time, $end_time)
    {
        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere([
                'AND',
                ['<=', 'start_date', $appointment_date],
                ['>=', 'end_date', $appointment_date],
            ])
            ->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ]);
    }

    public static function getBookedSlotsForRange($user_id, $start_date, $end_date)
    {
        // $currentDateTime = new \DateTime();

        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['between', 'appointment_date', $start_date, $end_date])
            ->andWhere(['!=', 'status', self::STATUS_RESCHEDULED])
            // ->andWhere(['>=', 'end_time', $currentDateTime->format('H:i:s')])
            ->orderBy(['start_time' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Checks if the requested appointment time overlaps with any existing appointments.
     *
     * @param int $user The VC's ID
     * @param string $appointment_date The date of the appointment
     * @param string $start_time The start time of the appointment
     * @param string $end_time The end time of the appointment
     * @return bool True if an overlapping appointment exists, false otherwise
     */
    public static function hasOverlappingAppointment($user_id, $date, $start_time, $end_time, $appointment_id = null, $priority = null)
    {
        $query = self::find()
            ->where(['user_id' => $user_id, 'appointment_date' => $date])
            ->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['!=', 'status', self::STATUS_CANCELLED]);

        if ($appointment_id !== null) {
            $query->andWhere(['!=', 'id', $appointment_id]);
        }

        $overlappingAppointment = $query->one();

        if (!$overlappingAppointment) {
            return false;
        }

        if ($priority !== null) {
            return self::checkPriority($overlappingAppointment, $priority);
        }
        // return true;
        return $query->exists();
    }

    private static function checkPriority($overlappingAppointment, $newPriority)
    {
        if ($overlappingAppointment->priority >= $newPriority) {
            return true;
        } else {
            $overlappingAppointment->status = Appointments::STATUS_RESCHEDULED;
            $overlappingAppointment->save(false);
            return false;
        }
    }

    public static function canOverride($existingAppointment, $newPriority)
    {
        // Compare the priority of the existing appointment with the new appointment
        if ($existingAppointment->priority >= $newPriority) {
            return false;
        } else {
            return true;
        }
    }


    public function getOverlappingAppointment($user_id, $start_date, $end_date, $start_time, $end_time)
    {
        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere([
                'AND',
                ['>=', 'appointment_date', $start_date],
                ['<=', 'appointment_date', $end_date],
            ])
            ->andWhere([
                'OR',
                ['AND', ['=', 'appointment_date', $start_date], ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<', 'start_time', $end_time]],
                ['AND', ['>', 'end_time', $start_time], ['<=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<', 'start_time', $end_time], ['>', 'end_time', $end_time]],
            ])
            // ->andWhere(['!=', 'status', 'self'])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    private function getAvailability($user_id, $appointment_date, $start_time, $end_time)
    {
        $bookedSlots = Availability::getUnavailableSlots(
            $user_id,
            $appointment_date,
            $start_time,
            $end_time
        );

        if ($bookedSlots) {
            return false;
        }
        return true;
    }
}
