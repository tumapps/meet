<?php

namespace scheduler\models;

use Yii;
use auth\models\User;
/**
 *@OA\Schema(
 *  schema="Settings",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="user_id", type="int",title="User id", example="int"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="slot_duration", type="integer",title="Slot duration", example="integer"),
 *  @OA\Property(property="booking_window", type="integer",title="Booking window", example="integer"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class Settings extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%settings}}';
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
            'start_time',
            'end_time',
            'slot_duration',
            'booking_window',
            'advanced_booking',
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
            [['user_id', 'slot_duration', 'booking_window', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'slot_duration', 'booking_window', 'advanced_booking', 'created_at', 'updated_at'], 'integer'],
            [['start_time', 'end_time', 'user_id'], 'required'],
            [['start_time', 'end_time'], 'safe'],
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    public function validateTimeRange($attribute, $params)
    {
        if (strtotime($this->end_time) <= strtotime($this->start_time)) {
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
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'slot_duration' => 'Slot Duration',
            'advanced_booking' => 'Advance Booking',
            'booking_window' => 'Booking Window',
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

    public static function getWorkingHours($user_id)
    {
        $hours = self::find()
        ->select(['start_time', 'end_time'])
        ->where(['user_id' => $user_id])
        ->asArray()
        ->one();

        if ($hours) {
        return [
            'start_time' => $hours['start_time'],
            'end_time' => $hours['end_time']
            ];
        } else {
            // If no hours are found, return default working hours (e.g., 08:00:00 to 17:00:00)
            return [
                'start_time' => '08:00:00',
                'end_time' => '17:00:00'
            ];
        }

    }

    public static function getAdvanceBookingDuration($user_id)
    {
        $duration = self::find()
                    ->select(['advanced_booking'])
                    ->where(['user_id' => $user_id])
                    ->scalar();

        return $duration;
    }
}
