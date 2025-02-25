<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
use scheduler\models\AppointmentAttendees;
use scheduler\models\AppointmentAttachments;
use scheduler\models\Events;
use scheduler\hooks\TimeHelper;
use scheduler\models\SpaceAvailability;
use scheduler\models\Space;
use scheduler\models\Availability;
// use borales\extensions\phoneInput\PhoneInputValidator;
use scheduler\hooks\NotificationTrait;


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
    use NotificationTrait;


    const STATUS_MISSED = 9;
    const STATUS_ATTENDED = 6;
    const STATUS_ACTIVE = 10;
    const STATUS_PENDING = 11;
    const STATUS_RESCHEDULE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_RESCHEDULED = 5;
    const STATUS_DELETED = 0;
    const STATUS_REJECTED = 2;
    const STATUS_CHECKED_IN = 1;

    const COMFIRMED_ATTENDANCE = 1;
    const DECLINED_ATTENDANCE = 0;

    // appointment priorities

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;


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
    const SCENARIO_DECLINE = 'reject';


    public $attendees = [];
    public $space_id;
    public $uploadedFile;
    public $cancellation_reason;
    public $rejection_reason;

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
                'appointment_type_id',
                'appointment_date',
                'start_time',
                'end_time',
                'contact_name',
                'email_address',
                'mobile_number',
                'subject',
                'status',
                'recordStatus' => function () {
                    return $this->recordStatus;
                },
                'created_by',
                'updated_by',
                'created_at' => function () {
                    return $this->created_at ? Yii::$app->formatter->asDateTime($this->created_at) : null;
                },
                'relative_time' => function () {
                    return $this->created_at ? Yii::$app->formatter->asRelativeTime($this->created_at) : null;
                },
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
            [['user_id', 'appointment_type_id', 'space_id', 'status'], 'integer'],
            [['appointment_date', 'email_address', 'start_time', 'end_time', 'user_id', 'appointment_type_id', 'subject', 'contact_name', 'mobile_number', 'description'], 'required'],
            [['appointment_date', 'start_time', 'end_time', 'attendees', 'space_id'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['subject'], 'string', 'max' => 100],

            // Custom inline validators as separate rules
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['start_time', 'end_time'], 'validateOverlappingEvents'],
            [['start_time', 'end_time'], 'validateAdvanceBooking'],
            [['start_time', 'end_time'], 'validateAvailability'],
            [['start_time', 'end_time'], 'validateOverlappingAppointment'],
            [['start_time'], 'validateMeetingTime'],

            [['appointment_date'], 'validateOverlappingEvents'],
            [['space_id'], 'validateSpace'],
            [['appointment_date'], 'checkWeekends'],
            [['appointment_date'], 'validateBookingWindow'],
            [['appointment_date'], 'validateOverlappingAppointment'],
            [['attendees'], 'validateAttendeesCount'],
            [['mobile_number'], 'validateMobileNumber'],
            [['contact_name'], 'validateName'],

            [['appointment_date'], 'date', 'format' => 'php:Y-m-d'],
            ['appointment_date', 'date', 'format' => 'php:Y-m-d', 'min' => date('Y-m-d'), 'message' => 'The appointment date must not be in the past'],

            [['subject', 'description'], 'string'],
            [['contact_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            ['email_address', 'email'],
            // [['mobile_number'], PhoneInputValidator::className(), 'region' => ['KE']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],

            // Rules for scenarios
            ['cancellation_reason', 'required', 'on' => self::SCENARIO_CANCEL, 'message' => 'Cancellation reason is required.'],
            ['cancellation_reason', 'string', 'max' => 100],
            ['rejection_reason', 'required', 'on' => self::SCENARIO_REJECT, 'message' => 'Rejection reason is required.'],
            ['rejection_reason', 'string', 'on' => self::SCENARIO_REJECT, 'max' => 100],


            // file upload
            [['uploadedFile'], 'file', 'extensions' => 'pdf, doc, docx', 'maxSize' => 2 * 1024 * 1024, 'skipOnEmpty' => false],
            [['uploadedFile'], 'validateFileAttachment'],
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CANCEL] = ['cancellation_reason'];
        $scenarios[self::SCENARIO_REJECT] = ['rejection_reason'];
        $scenarios[self::SCENARIO_DECLINE] = ['decline_reason'];
        return $scenarios;
    }

    public function validateName($attribute, $params)
    {
        $name = $this->$attribute;

        if (!preg_match("/^[a-zA-Z']+$/", $name)) {
            $this->addError($attribute, 'The name can only contain alphabetic characters');
        }

        if (preg_match('/(.)\1{2,}/', $name)) {
            $this->addError($attribute, 'The name cannot contain three or more consecutive identical characters.');
        }
    }


    public function validateTimeRange($attribute, $params)
    {
        $currentTime = date('Y-m-d h:i:sa');
        $dateTime = date($this->appointment_date . ' ' . $this->start_time);

        if (strtotime($this->end_time) <= strtotime($this->start_time) || strtotime($dateTime) < strtotime($currentTime)) {
            $this->addError($attribute, 'invalid time range');
        }
    }

    public function validateMobileNumber($attribute, $params)
    {
        $pattern = '/^(07|01|\+2547)[0-9]{8}$/';

        if (!preg_match($pattern, $this->$attribute)) {
            $this->addError($attribute, 'Invalid phone number');
        }
    }

    public function validateOverlappingEvents($attribute, $params)
    {
        if (!$this->isValidDate($this->appointment_date)) {
            $this->addError($attribute, 'The provided date is not valid.');
            return;
        }

        if (!$this->isValidTime($this->start_time) || !$this->isValidTime($this->end_time)) {
            $this->addError($attribute, 'Start time and End time must be in the format HH:MM.');
            return;
        }

        $hasOverlappingEvents = Events::getOverlappingEvents(
            $this->appointment_date,
            $this->start_time,
            $this->end_time
        );

        if ($hasOverlappingEvents) {
            $this->addError($attribute, 'The selected time or date overlaps with another event.');
        }
    }

    public function checkWeekends($attribute, $params)
    {
        $dayOfWeek = date('N', strtotime($this->$attribute));

        if ($dayOfWeek >= 6) { 
            $this->addError($attribute, 'Booking is not allowed on weekends.');
        }
    }

    private function isValidTime($time)
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);
    }

    protected function isValidDate($date)
    {
        $timestamp = strtotime($date);
        return $timestamp && date('Y-m-d', $timestamp) === $date;
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
        if ($this->isNewRecord) {
            $isAdvancedBookingValid = TimeHelper::validateAdvanceBooking(
                $this->user_id,
                $this->start_time,
                $this->appointment_date
            );

            if (!$isAdvancedBookingValid) {
                $this->addError($attribute, 'You cannot book an appointment this soon. Please choose a later time.');
            }
        }
    }

    public function validateBookingWindow($attribute, $params)
    {
        $validateBookingWindow = TimeHelper::isWithinBookingWindow(
            $this->user_id,
            $this->appointment_date
        );

        $bookingWindow = Settings::find()
            ->select('booking_window')
            ->where(['user_id' => $this->user_id])
            ->scalar();
        $month = $validateBookingWindow > 1 ? 'months' : 'month';

        if (!$validateBookingWindow) {
            $this->addError($attribute, "The appointment is outside the allowed booking window. Booking for this user is only allowed up to {$bookingWindow} {$month} in advance.");
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
            $this->id,
            // $this->priority
        );

        if ($appointmentExists) {
            $this->addError($attribute, 'An overlapping appointment already exists.');
        }
    }

    public function validateSpace($attribute, $params)
    {
        $space = Space::findOne($this->$attribute);
    
        if ($space) {
            if ($space->is_deleted == 1 && $space->space_type === Space::SPACE_TYPE_MANAGED) {
                $this->addError($attribute, 'The selected space is deleted and cannot be used for booking.');
            }
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

    public function validateFileAttachment($attribute, $params)
    {
        $existingAttachment = AppointmentAttachments::findOne(['appointment_id' => $this->id]);

        if ($this->uploadedFile && $existingAttachment) {
            $this->addError($attribute, 'A file is already attached to this appointment. File updates are not allowed.');
        }
    }

    public function validateAttendeesCount($attribute, $params)
    {
        $space = Space::findOne($this->space_id);

        if (is_string($this->attendees)) {
            $this->attendees = explode(',', $this->attendees);
        }

        if ($space) {
            $capacity = $space->capacity;
            $attendeesCount = count($this->attendees);

            if ($attendeesCount > $capacity) {
                $this->addError($attribute, "The number of attendees ({$attendeesCount}) exceeds the space capacity ({$capacity}).");
            }
        } else {
            $this->addError('space_id', 'The specified space does not exist.');
        }
    }

    public function validateMeetingTime($attribute, $params)
    {
        $space = Space::findOne($this->space_id);

        if (!$space) {
            $this->addError('space_id', 'The specified space does not exist.');
            return;
        }

        $spaceOpenTime = strtotime($space->opening_time);
        $spaceCloseTime = strtotime($space->closing_time);
        $meetingStartTime = strtotime($this->start_time);
        $meetingEndTime = strtotime($this->end_time);

        if ($meetingStartTime < $spaceOpenTime || $meetingEndTime > $spaceCloseTime) {
            $this->addError($attribute, "The meeting time ({$this->start_time} - {$this->end_time}) must be within the venue operating hours ({$space->opening_time} - {$space->closing_time}).");
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
            'appointment_type_id' => 'Appointment Type',
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

    public function getMeetingType()
    {
        return $this->hasOne(\scheduler\models\MeetingTypes::class, ['id' => 'appointment_type_id']);
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

        $appointment->checked_in = self::STATUS_CHECKED_IN;
        // $appointment->appointment_date = date('Y-m-d', strtotime($appointment->appointment_date));
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

    public static function getAppointmentsPastOneHour()
    {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        // $appointments = self::find()
        //     ->where(['<=', 'CONCAT(appointment_date, end_time)', $oneHourAgo])
        //     ->andWhere(['status' => self::STATUS_ACTIVE])
        //     ->all();

        $appointments = self::find()
            ->where(['<=', 'appointment_date', $currentDate])
            ->andWhere(['<', 'end_time', $currentTime])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->all();

        $filteredAppointments = array_filter($appointments, function ($appointment) use ($oneHourAgo) {
            return strtotime($appointment->end_time) <= strtotime($oneHourAgo);
        });

        // return $appointments;
        return $filteredAppointments;
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

    protected function getSpaceName($spaceId)
    {

        return Space::find()
            ->select(['name'])
            ->where(['id' => $spaceId])
            ->scalar() ?: '';
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
  

    public function getOverlappingAppointment($user_id, $start_date, $end_date, $start_time, $end_time, $ignoreUser = false)
    {
        $query = self::find()
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
            ->orderBy(['created_at' => SORT_ASC]);

        // Only apply user_id condition if $ignoreUser is false
        if (!$ignoreUser) {
            $query->andWhere(['user_id' => $user_id]);
        }

        return $query->all();
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
