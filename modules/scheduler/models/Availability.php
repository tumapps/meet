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
            [['start_date', 'end_date'], 'required'],
            [['start_date', 'end_date', 'start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['is_full_day'], 'boolean'],
            [['description'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
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
        return $this->hasOne(Users::class, ['user_id' => 'user_id']);
    }

    public static function getUnavailableSlots($vc_id, $appointment_date, $start_time, $end_time)
    {
       return self::find()
        ->where(['user_id' => $vc_id])
        ->andWhere([
            'OR',
            ['is_full_day' => true, 'start_date' => $appointment_date],
            [
                'AND',
                ['<=', 'start_date', $appointment_date],
                ['>=', 'end_date', $appointment_date],
                [
                    'OR',
                    ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $start_time]],
                    ['AND', ['<=', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                    ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                    ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
                ]
            ]
        ])
        ->all();
    }
}


// return self::find()
//             ->where([
//                 'user_id' => $vc_id,
//                 'date' => $appointment_date,
//             ])
//             ->andWhere([
//                 'OR',
//                 ['<=', 'start_time', $start_time],
//                 ['>=', 'end_time', $end_time],
//             ])
//             ->all();