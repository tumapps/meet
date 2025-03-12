<?php

namespace scheduler\models;

use Yii;
use scheduler\models\Space;

/**
 *@OA\Schema(
 *  schema="SpaceAvailability",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="space_id", type="integer",title="Space id", example="integer"),
 *  @OA\Property(property="date", type="string",title="Date", example="string"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class SpaceAvailability extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%space_availability}}';
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
                'space_id',
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
            [['space_id', 'appointment_id', 'date', 'start_time', 'end_time'], 'required'],
            [['space_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['space_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['date', 'start_time', 'end_time'], 'safe'],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::class, 'targetAttribute' => ['space_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'space_id' => 'Space ID',
            'date' => 'Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getSpaceAvailability($spaceId, $date)
    {
        return self::find()
            ->select(['space_id', 'date', 'start_time', 'end_time'])
            ->where([
                'space_id' => $spaceId,
                'date' => $date,
                'is_deleted' => 0
            ])
            ->asArray()
            ->all();
    }

    public static function getOverlappingSpace($spaceId, $date, $start_time, $end_time)
    {
        $overlapExists = SpaceAvailability::find()
            ->where(['space_id' => $spaceId])
            ->andWhere(['date' => $date])
            ->andWhere([
                'or',
                ['between', 'start_time', $start_time, $end_time],
                ['between', 'end_time', $start_time, $end_time],
                ['and', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]]
            ])
            ->exists();

        return $overlapExists;
    }

    // public function validateOverlappingSpace1($attribute, $params)
    // {
    //     if (!$this->space_id) {
    //         return;
    //     }

    //     $overlapExists = SpaceAvailability::find()
    //         ->where(['space_id' => $this->space_id])
    //         ->andWhere(['date' => $this->appointment_date]) 
    //         ->andWhere([
    //             'or',
    //             ['between', 'start_time', $this->start_time, $this->end_time],
    //             ['between', 'end_time', $this->start_time, $this->end_time],
    //             ['and', ['<=', 'start_time', $this->start_time], ['>=', 'end_time', $this->end_time]]
    //         ])
    //         ->exists();

    //     if ($overlapExists) {
    //         $this->addError($attribute, 'The selected space is already booked during the specified time.');
    //     }
    // }


    /**
     * Gets query for [[Space]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }
}
