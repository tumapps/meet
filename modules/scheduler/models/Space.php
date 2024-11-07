<?php

namespace scheduler\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Space",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="level_id", type="integer",title="Level id", example="integer"),
 *  @OA\Property(property="name", type="string",title="Name", example="string"),
 *  @OA\Property(property="opening_time", type="string",title="Open Time", example="string"),
 *  @OA\Property(property="closing_time", type="string",title="Close Time", example="string"),
 *  @OA\Property(property="is_locked", type="boo",title="Is locked", example="boo"),
 *  @OA\Property(property="location", type="string",title="Location", example="string"),
 *  @OA\Property(property="description", type="string",title="Description", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class Space extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%spaces}}';
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
            'level_id',
            'name',
            'opening_time',
            'closing_time',
            'is_locked',
            'location',
            'description',
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
            [['level_id', 'name', 'opening_time', 'closing_time'], 'required'],
            [['level_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['level_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['opening_time', 'closing_time'], 'safe'],
            [['opening_time', 'closing_time'], 'validateTimeRange'],
            [['is_locked'], 'boolean'],
            [['description'], 'string'],
            [['name', 'location'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Level::class, 'targetAttribute' => ['level_id' => 'id']],
        ];
    }

    public function validateTimeRange($attribute, $params)
    {
        // $currentTime = date('Y-m-d h:i:sa');
        // $dateTime = date('Y-m-d' .' '.$this->opening_time);

        if (strtotime($this->closing_time) <= strtotime($this->opening_time) /*|| strtotime($dateTime) < strtotime($currentTime)*/) {
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
            'level_id' => 'Level ID',
            'name' => 'Name',
            'opening_time' => 'Open Time',
            'closing_time' => 'Close Time',
            'is_locked' => 'Is Locked',
            'location' => 'Location',
            'description' => 'Description',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLevel()
    {
        return $this->hasOne(Level::class, ['id' => 'level_id']);
    }

    /**
     * Gets query for [[SpaceAvailabilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpaceAvailabilities()
    {
        return $this->hasMany(SpaceAvailability::class, ['space_id' => 'id']);
    }
}
