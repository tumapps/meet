<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
use yii\base\Event;
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
            'recordStatus' => function(){
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
            [['appointment_date', 'email_address','start_time','end_time','user_id','subject', 'contact_name','mobile_number', 'appointment_type','description', 'priority'], 'required'],
            [['appointment_date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['appointment_date'], 'date', 'format' => 'php:Y-m-d'],
            ['appointment_date', 'date', 'format' => 'php:Y-m-d', 'min' => date('Y-m-d'), 'message' => 'The appointment date must not be in the past'],
            [['subject','description'], 'string'],
            [['contact_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            ['email_address', 'email'],
            ['mobile_number', 'string', 'max' => 13, 'tooLong' => 'Phone number must not exceed 13 digits.'],
            ['mobile_number', 'match', 'pattern' => '/^\+?[0-9]{7,15}$/', 'message' => 'Phone number must be a valid integer with a maximum of 13 digits.'],
            [['appointment_type'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
            
            // applied only when cancelling appointments
            ['cancellation_reason', 'required', 'on' => self::SCENARIO_CANCEL, 'message' => 'Cancellation reason is required.'],
            ['cancellation_reason', 'string', 'max' => 255],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CANCEL] = ['cancellation_reason'];
        return $scenarios;
    }

    public function validateTimeRange($attribute, $params)
    {
        $currentTime = date('Y-m-d h:i:sa');
        $dateTime = date($this->appointment_date.' '.$this->start_time);

        if (strtotime($this->end_time) <= strtotime($this->start_time) || strtotime($dateTime) < strtotime($currentTime)) {
            $this->addError($attribute, 'invalid time range');
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

        if(!$appointment) {
            // return false;
            return [
                'success' => false,
                'message' => 'Appointment not found.'
            ];
        }

        $currentTime = date('Y-m-d H:i:s');
        $appointmentTime = date('Y-m-d H:i:s', strtotime($appointment->appointment_date.' '.$appointment->start_time));

        if ($currentTime < $appointmentTime) {
            return [
                'success' => false,
                'message' => 'You cannot check in before the appointment start time.'
            ];
        }

        $appointment->checked_in = !$appointment->checked_in;

        // $appointment->checked_in = true;
        // $appointment->save(false);

        if ($appointment->save(false)) {
            $message = $appointment->checked_in ? 'Appointment successfully checked in.':'Appointment successfully unchecked.';
            
            return [
                'success' => true,
                'message' => $message
            ];
        }

        // return true;
        // return [
        //     'success' => true,
        //     'message' => 'Appointment successfully checked in.'
        // ];
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

        $eventData = [
            'contactEmail' => $email,
            'contact_name' => $name,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'bookedUserEmail' => $bookedUserEmail,
            'cancellation_reason' => $this->cancellation_reason,
            'subject' => $subject,
        ];
        $this->on(self::EVENT_APPOINTMENT_CANCELLED, [EventHandler::class, 'onAppointmentCancelled'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_CANCELLED, $event);
    }

    public function sendAppointmentCreatedEvent($email, $name, $user_id, $date, $startTime, $endTime){

        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Created';

        $user = User::findOne($user_id);
        $bookedUserEmail = $user->profile->email_address;

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'contact_name' => $name,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'username' => $this->getUserName($user_id),
            'user_email' => $bookedUserEmail,
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
        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'name' => $name,
            'bookedUserName' => $userName
        ];

        $this->on(self::EVENT_APPOINTMENT_RESCHEDULE, [EventHandler::class, 'onAppointmentReschedule'], $eventData);
        $this->trigger(self::EVENT_APPOINTMENT_RESCHEDULE, $event); 
    }

    public function sendAppointmentRescheduledEvent($email, $date, $startTime, $endTime, $name)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = 'Appointment Rescheduled';
        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'date' => $date,
            'sartTime' => $startTime,
            'endTime' => $endTime,
            'name' => $name,
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

        $eventData = [
            'email' => $email,
            'subject' => $subject,
            'date' => $date,
            'startTime' => $start_time,
            'endTime' => $end_time,
            'contact_name' => $contact_name,
            'username' => $this->getUserName($user_id),
            'appointment_id' => $id,
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

        // Fetch upcoming appointments on the given date, ordered by start time
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
        // Check for time-slot-based unavailability within a date range
        return self::find()
        ->where(['user_id' => $user_id])
        ->andWhere([
            'AND',
            ['<=', 'start_date', $appointment_date],
            ['>=', 'end_date', $appointment_date],
        ])
        ->andWhere([
            'OR',
            // Check if the start time of the appointment overlaps with any unavailable slot
            ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
            // Check if the end time of the appointment overlaps with any unavailable slot
            ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
            // Check if the appointment fully overlaps an unavailable slot
            ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
            // Check if the appointment is within an unavailable slot
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

                /* checks if the start time of the new appointment ($start_time) falls within an existing appointment.
                */

                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],

                /*
                    checks if the end time of the new appointment ($end_time) falls within an existing appointment.
                 */
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],

                /*
                    checks if the existing appointment completely spans over the new appointment's time slot.
                 */
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],

                 // The requested appointment spans across an existing appointment
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ])
            // Exclude appointments that are marked as deleted
            ->andWhere(['is_deleted' => 0])
            // Exclude appointments with a status of 'cancelled'
            ->andWhere(['!=', 'status', self::STATUS_CANCELLED]);

            // Exclude the current appointment from the overlapping check during update
            if ($appointment_id !== null) {
                $query->andWhere(['!=', 'id', $appointment_id]);
            }

            // Find any overlapping appointments
            $overlappingAppointment = $query->one();

            // If no overlapping appointment is found, return false
            if (!$overlappingAppointment) {
                return false;
            }

            // Call checkPriority to handle priority comparison and rescheduling logic
            if($priority !== null){
                return self::checkPriority($overlappingAppointment, $priority);
            }
             
            // return true;
            return $query->exists();
    } 

    private static function checkPriority($overlappingAppointment, $newPriority)
    {
        if ($overlappingAppointment->priority >= $newPriority) {
            // If overlapping appointment has higher or equal priority, do not allow overriding
            return true;
        } else {
            // If the new appointment has higher priority, mark the existing one as "rescheduled"
            $overlappingAppointment->status = Appointments::STATUS_RESCHEDULED;
            $overlappingAppointment->save(false);
            return false; // Allow the new appointment to be created
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
            // Appointments within the unavailable date range
            ['>=', 'appointment_date', $start_date],
            ['<=', 'appointment_date', $end_date],
        ])
        ->andWhere([
            'OR',
            // user is unavailable for the entire day
            // Any appointment on the same date is affected
            ['AND', ['=', 'appointment_date', $start_date], ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],

            // Appointment starts within the unavailable time range
            ['AND', ['>=', 'start_time', $start_time], ['<', 'start_time', $end_time]],
            
            // Appointment ends within the unavailable time range
            ['AND', ['>', 'end_time', $start_time], ['<=', 'end_time', $end_time]],
            
            // Appointment completely overlaps with unavailable time range
            ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
            
            // Appointment starts before unavailability ends but ends after
            ['AND', ['<', 'start_time', $end_time], ['>', 'end_time', $end_time]],
        ])
        // ->andWhere(['!=', 'status', 'self'])
        ->orderBy(['created_at' => SORT_ASC])
        ->all();
    }
}
