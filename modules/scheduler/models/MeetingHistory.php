<?php

namespace scheduler\models;

// use Yii;

/**
 *@OA\Schema(
 *  schema="MeetingHistory",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="meeting_id", type="int",title="Meeting id", example="int"),
 *  @OA\Property(property="meeting_status", type="int",title="Meeting status", example="int"),
 *  @OA\Property(property="space_id", type="int",title="Space id", example="int"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 * )
 */

class MeetingHistory extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%meeting_history}}';
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
                'meeting_id',
                'meeting_status',
                'space_id',
                'is_deleted',
                'updated_at',
                'created_at',
            ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id', 'meeting_status', 'space_id', 'new_space_id', 'is_deleted'], 'default', 'value' => null],
            [['meeting_id', 'meeting_status', 'space_id', 'is_deleted',], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meeting_id' => 'Meeting ID',
            'meeting_status' => 'Meeting Status',
            'space_id' => 'Space ID',
            'is_deleted' => 'Is Deleted',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function saveHistory($appointmentId, $previousSpaceId, $previousStatus, $newSpceId)
    {
        $this->meeting_id = $appointmentId;
        $this->space_id = $previousSpaceId;
        $this->new_space_id = $newSpceId;
        $this->meeting_status = $previousStatus;
        return $this->save(false);

        // if ($this->validate()) {
        //     return $this->save(false);
        // }

        // return false;
    }
}
