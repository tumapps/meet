<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
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
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%appointment_attendees}}';
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
            'appointment_id',
            'staff_id',
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
            [['appointment_id', 'staff_id', 'date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'required'],
            [['appointment_id', 'staff_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['appointment_id', 'staff_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['date', 'start_time', 'end_time'], 'safe'],
            [['appointment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Appointments::class, 'targetAttribute' => ['appointment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'appointment_id' => 'Appointment ID',
            'staff_id' => 'Staff ID',
            'date' => 'Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
            ->where(['staff_id' => $staffId, 'date' => $date])
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

    public function save($id, $staffId, $date, $startTime, $endTime)
    {
        $this->appointment_id = $id;
        $this->staff_id = $staffId;
        $this->date = $date;
        $this->start_time = $startTime;
        $this->end_time = $endTime;

        $this->save();
        // if($this->save()){
        //     return true;
        // }

        // return false;
    }

    public static function getAttendeesEmailsByAppointmentId($appointmentId)
    {
        $attendees = self::find()
            ->where(['appointment_id' => $appointmentId])
            ->all();

        $emails = [];

        foreach ($attendees as $attendee) {
            $user = User::find()->where(['username' => $attendee->staff_id])->one();

            if ($user && $user->profile->email_address) {
                $emails[] = $user->profile->email_address;
            }
        }

        return $emails;
    }
}
