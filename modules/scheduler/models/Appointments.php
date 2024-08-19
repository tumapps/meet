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
            [['user_id'], 'integer'],
            [['appointment_date', 'email_address','start_time','end_time','user_id'], 'required'],
            [['appointment_date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['subject'], 'string'],
            [['contact_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            [['mobile_number'], 'string', 'max' => 15],
            [['appointment_type', 'status'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    public function validateTimeRange($attribute, $params)
    {
        if (strtotime($this->end_time) < strtotime($this->start_time)) {
            $this->addError($attribute, 'End time must be later than start time.');
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
            ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
            ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
            ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
            ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
        ])
        ->exists();
    } 
}
