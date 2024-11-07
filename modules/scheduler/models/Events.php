<?php

namespace scheduler\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Events",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="title", type="string",title="Title", example="string"),
 *  @OA\Property(property="description", type="string",title="Description", example="string"),
 *  @OA\Property(property="event_date", type="string",title="Event date", example="string"),
 *  @OA\Property(property="start_time", type="string",title="Start time", example="string"),
 *  @OA\Property(property="end_time", type="string",title="End time", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class Events extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%events}}';
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
            'title',
            'description',
            'event_date',
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
            [['title', 'event_date', 'start_time', 'end_time'], 'required'],
            [['description'], 'string'],
            [['event_date', 'start_time', 'end_time'], 'safe'],
            [['start_time', 'end_time'], 'validateTimeRange'],
            [['is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }


    public function validateTimeRange($attribute, $params)
    {
        $currentTime = date('Y-m-d h:i:sa');
        $dateTime = date($this->event_date.' '.$this->start_time);

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
            'title' => 'Title',
            'description' => 'Description',
            'event_date' => 'Event Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getOverlappingEvents($date, $start_time, $end_time)
    {
        $overlappingEvents = self::find()
            ->where(['event_date' => $date])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['or',
                ['and', ['<=', 'start_time', $start_time], ['>=', 'end_time', $start_time]],
                ['and', ['<=', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['and', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
                ['and', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
            ])
            ->exists();

        return $overlappingEvents;
    }

}
