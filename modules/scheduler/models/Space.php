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
 *  @OA\Property(property="capacity", type="integer",title="Capacity", example="Integer"),
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


    const SPACE_TYPE_MANAGED = 1;
    const SPACE_TYPE_UNMANAGED = 0;


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
                'name',
                'opening_time',
                'closing_time',
                'capacity',
                'space_type',
                'is_locked',
                'location',
                'description',
                'is_deleted',
                'created_at',
                'updated_at',
            ]
        );
    }

    public static function primaryKey()
    {
        return ['id'];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'opening_time', 'closing_time', 'capacity'], 'required'],
            [['is_deleted'], 'default', 'value' => null],
            [['is_deleted'], 'integer'],
            ['capacity', 'integer', 'min' => 1, 'message' => 'Capacity must be a positive number greater than zero.'],
            [['opening_time', 'closing_time'], 'safe'],
            [['opening_time', 'closing_time'], 'validateTimeRange'],
            [['is_locked'], 'boolean'],
            [['description'], 'string'],
            [['name', 'location'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->id)) {
            $this->id = $this->uid('SPACE', true);
        }

        return parent::beforeSave($insert);
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
            'name' => 'Name',
            'opening_time' => 'Open Time',
            'closing_time' => 'Close Time',
            'capacity' => 'Capacity',
            'is_locked' => 'Is Locked',
            'location' => 'Location',
            'description' => 'Description',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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

    public static function getSpaceName($spaceId)
    {
        $space = self::findOne(['id' => $spaceId]);
        if ($space) {
            return [
                'name' => $space->name,
                'location' => $space->location,
            ];
        }
        return null;
    }

    public static function getSpaceNameDetails($spaceId)
    {
        return self::find()
            ->select(['name', 'location', 'id'])
            ->where(['id' => $spaceId, /*'space_type' => self::SPACE_TYPE_MANAGED*/])
            ->asArray()
            ->one();
    }
}
