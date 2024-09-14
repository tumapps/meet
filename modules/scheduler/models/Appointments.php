<?php

namespace scheduler\models;

use Yii;
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
    const STATUS_ACTIVE = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_RESCHEDULE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_RESCHEDULED = 5;


    protected static $statusLabels = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_RESCHEDULE => 'Reschedule',
        self::STATUS_RESCHEDULED => 'Rescheduled',
        self::STATUS_CANCELLED => 'Cancelled',
    ];


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
            'subject',
            'appointment_type',
            'status',
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
            [['appointment_date', 'email_address','start_time','end_time','user_id','subject', 'contact_name','mobile_number', 'appointment_type','description'], 'required'],
            [['appointment_date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['appointment_date'], 'date', 'format' => 'php:Y-m-d'],
            ['appointment_date', 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '>=', 'type' => 'date', 'message' => 'The date must not be in the past'],
            [['subject','description'], 'string'],
            [['contact_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            ['email_address', 'email'],
            ['mobile_number', 'string', 'max' => 13, 'tooLong' => 'Phone number must not exceed 13 digits.'],
            ['mobile_number', 'match', 'pattern' => '/^\+?[0-9]{7,15}$/', 'message' => 'Phone number must be a valid integer with a maximum of 13 digits.'],
            [['appointment_type'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
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

    public static function getStatusLabel($status)
    {
        return self::$statusLabels[$status] ?? 'Unknown';
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
     * @param int $vc_id The VC's ID
     * @param string $appointment_date The date of the appointment
     * @param string $start_time The start time of the appointment
     * @param string $end_time The end time of the appointment
     * @return bool True if an overlapping appointment exists, false otherwise
     */
    public static function hasOverlappingAppointment($vc_id, $date, $start_time, $end_time)
    {
        return self::find()
        ->where(['user_id' => $vc_id, 'appointment_date' => $date])
        ->andWhere([
            'OR',

            /* checks if the start time of the new appointment ($start_time) falls within an existing appointment.
            */

            ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],

            /*
                hecks if the end time of the new appointment ($end_time) falls within an existing appointment.
             */
            ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],

            /*
                checks if the existing appointment completely spans over the new appointment's time slot.
             */
            ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],

             // The requested appointment spans across an existing appointment
            ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
        ])
        ->exists();
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
// echo $query->createCommand()->getRawSql();
