<?php

namespace scheduler\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Availability",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="user_id", type="int",title="User id", example="int"),
 *  @OA\Property(property="start_date", type="string",title="Start date", example="string"),
 *  @OA\Property(property="end_date", type="string",title="End date", example="string"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="is_full_day", type="boo",title="Is full day", example="boo"),
 *  @OA\Property(property="description", type="string",title="Description", example="string"),
 *  @OA\Property(property="created_at", type="string",title="Created at", example="string"),
 *  @OA\Property(property="updated_at", type="string",title="Updated at", example="string"),
 * )
 */

class Availability extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%unavailable_slots}}';
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
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'is_full_day',
            'description',
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
            [['start_date', 'end_date', 'start_time', 'end_time', 'description'], 'required'],
            [['start_date', 'end_date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['start_time', 'end_time'], 'validateDateTimeRange'],
            [['start_date', 'end_date'], 'validateDateRange'],
            [['is_full_day'], 'boolean'],
            [['description'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    public function validateDateTimeRange($attribute, $params)
    {
        $startDateTime = new \DateTime("{$this->start_date} {$this->start_time}");
        $endDateTime = new \DateTime("{$this->end_date} {$this->end_time}");

        // Check if the end DateTime is earlier or equal to the start DateTime
        if ($endDateTime <= $startDateTime) {
            $this->addError($attribute, 'End date and time must be later than start date and time.');
        }
    }


    public function validateDateRange($attribute, $params)
    {
        if (new \DateTime($this->end_date) < new \DateTime($this->start_date)) {
            $this->addError($attribute, 'End date must be later than start date.');
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
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_full_day' => 'Is Full Day',
            'description' => 'Description',
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
        return $this->hasOne(\auth\models\User::class, ['user_id' => 'user_id']);
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
        ])
        // exclude deleted availabilities
        ->andWhere(['is_deleted' => (int) 0]);
    }


    public static function getUnavailableSlots($vc_id, $appointment_date, $start_time, $end_time)
    {
        // Check if the entire day is unavailable
        $isFullDayUnavailable = self::find()
            ->where(['user_id' => $vc_id, 'is_full_day' => true, 'start_date' => $appointment_date])
            ->exists();

        if ($isFullDayUnavailable) {
            // If the entire day is unavailable, return true ie indicating unavailability
            return true;
        }

        // Check for time-slot-based unavailability within a date range
        $hasOverlappingSlots = self::getUnavailableSlotsQuery($vc_id, $appointment_date, $start_time, $end_time)->exists();

        // Return true if there are overlapping slots, otherwise false
        return $hasOverlappingSlots;
    }

    public static function getUnavailableSlotDetails($user_id, $appointment_date, $start_time, $end_time)
    {
        $unavailableSlots = [];

        // Check if the entire day is unavailable
        $isFullDayUnavailable = self::find()
        ->where(['user_id' => $user_id, 'is_full_day' => true])
        ->andWhere(['<=', 'start_date', $appointment_date])
        ->andWhere(['>=', 'end_date', $appointment_date])
        ->exists();

        // return $isFullDayUnavailable;

        if ($isFullDayUnavailable) {
            return [['is_full_day' => true]];
        }

        // Use the common query logic to get unavailable slots
        $slots = self::getUnavailableSlotsQuery($user_id, $appointment_date, $start_time, $end_time)->all(); 
        return $slots;

        foreach ($slots as $slot) {
            $unavailableSlots[] = [
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
            ];
        }

        return $unavailableSlots;
    }

    public static function isUnavailableSlot($user_id, $date, $start_time, $end_time)
    {
        $isFullDayUnavailable = self::find()
        ->where(['user_id' => $user_id, 'is_full_day' => true, 'start_date' => $date])
        ->exists();

        if ($isFullDayUnavailable) {
            return true;
        }

        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['<=', 'start_date', $date])
            ->andWhere(['>=', 'end_date', $date])
            ->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ])
            ->andWhere(['is_deleted' => (int) 0])
            ->exists();
    }
}


 
