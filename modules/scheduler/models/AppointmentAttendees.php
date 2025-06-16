<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
use yii\base\Event;
use helpers\EventHandler;
use scheduler\models\Appointments;

/**
 *@OA\Schema(
 *  schema="AppointmentAttendees",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="appointment_id", type="integer",title="Appointment id", example="integer"),
 *  @OA\Property(property="staff_id", type="integer",title="Staff id", example="integer"),
 *  @OA\Property(property="date", type="string",title="Date", example="string"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class AppointmentAttendees extends BaseModel
{

    const STATUS_DECLINED = 15;
    const STATUS_CONFIRMED = 14;
    const STATUS_PENDING = 11;
    const STATUS_REMOVED = 1;


    const EVENT_ATTENDEE_UPDATE = 'attendeeUpdate';

    const SCENARIO_REMOVE = 'removed';

    public $attendees = [];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%appointment_attendees}}';
    }

    protected static $statusLabels = [
        self::STATUS_DECLINED => 'DECLINED',
        self::STATUS_CONFIRMED => 'CONFIRMED',
        self::STATUS_PENDING => 'PENDING',
        self::STATUS_REMOVED => 'REMOVED',
    ];
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(),
            [
                'id',
                'appointment_id',
                'attendee_id',
                'date',
                'start_time',
                'end_time',
                'is_deleted',
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
            [['appointment_id', 'attendee_id', 'date', 'start_time', 'end_time'], 'required'],
            [['appointment_id', 'attendee_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['appointment_id', 'attendee_id', 'is_deleted',], 'integer'],
            [['date', 'start_time', 'end_time'], 'safe'],
            [['appointment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Appointments::class, 'targetAttribute' => ['appointment_id' => 'id']],

            // ['removal_reason', 'required', 'on' => self::SCENARIO_REMOVE, 'message' => 'Reason is required.'],
            ['attendee_id', 'required', 'on' => self::SCENARIO_REMOVE, 'message' => 'Staff id is required.'],

        ];
    }

    // public function scenarios()
    // {
    //     $scenarios = parent::scenarios();
    //     $scenarios[self::SCENARIO_REMOVE] = ['removal_reason'];
    //     return $scenarios;
    // }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'appointment_id' => 'Appointment ID',
            'attendee_id' => 'Attendee ID',
            'date' => 'Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getStatusLabel($status)
    {
        return self::$statusLabels[$status] ?? 'Unknown';
    }


    public function sendAttendeeUpdateEvent($appointment_id, $attendee_id, $reason = '', $is_removed = false)
    {
        $event = new Event();
        $event->sender = $this;
        $subject = $is_removed ? 'Meeting Updates' : 'Meeting Invitation';

        $user = User::find()->where(['user_id' => $attendee_id])->one();
        $email = $user->profile->email_address;

        $appointmentData = Appointments::find()
            ->select(['subject', 'appointment_date', 'start_time', 'end_time'])
            ->where(['id' => $appointment_id])
            ->one();

        $eventData = [
            'meeting_id' => $appointment_id,
            'attendee_id' => $attendee_id,
            'meeting_subject' => $appointmentData->subject,
            'appointment_date' => $appointmentData->appointment_date,
            'start_time' => $appointmentData->start_time,
            'end_time' => $appointmentData->end_time,
            'reason' => $reason,
            'subject' => $subject,
            'email' => $email,
            'is_removed' => $is_removed,
        ];

        $this->on(self::EVENT_ATTENDEE_UPDATE, [EventHandler::class, 'onAttendeeUpdate'], $eventData);
        $this->trigger(self::EVENT_ATTENDEE_UPDATE, $event);
    }


    /**
     * Gets query for [[Appointment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppointment()
    {
        return $this->hasOne(Appointments::class, ['id' => 'appointment_id']);
    }

    public static function isAttendeeUnavailable($staffId, $date, $startTime, $endTime)
    {
        $overlap = self::find()
            ->where(['staff_id' => $staffId, 'date' => $date, 'is_removed' => 0])
            ->andWhere([
                'or',
                ['between', 'start_time', $startTime, $endTime],
                ['between', 'end_time', $startTime, $endTime],
                [
                    'and',
                    ['<=', 'start_time', $startTime],
                    ['>=', 'end_time', $endTime]
                ],
            ])
            ->andWhere(['is_deleted' => 0])
            ->exists();

        return $overlap;
    }

    public function addAttendee($id, $attendee_id, $date, $startTime, $endTime)
    {
        $this->appointment_id = $id;
        $this->attendee_id = $attendee_id;
        $this->date = $date;
        $this->start_time = $startTime;
        $this->end_time = $endTime;

        $this->save();
        // if($this->save()){
        //     return true;
        // }

        // return false;
    }



    public static function getAttendeesEmailsByAppointmentId($appointmentId, $includeStaffId = false, $confirmedOnly = false)
    {
        $query = self::find()->where(['appointment_id' => $appointmentId, 'is_removed' => 0]);

        if ($confirmedOnly) {
            $query->andWhere(['status' => self::STATUS_CONFIRMED]);
        }

        $attendees = $query->all();

        $results = [];

        foreach ($attendees as $attendee) {
            $user = User::findOne(['user_id' => $attendee->attendee_id]);
            if ($user && !empty($user->profile->email_address)) {
                // $email = $user->profile->email_address;
                if ($includeStaffId) {
                    $results[] = [
                        'staff_id' => $attendee->attendee_id,
                        'email' => $user->profile->email_address,
                    ];
                } else {
                    $results[] = $user->profile->email_address;
                }
            }
        }

        return $results;
    }
}
