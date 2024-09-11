<?php

namespace scheduler\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="AppointmentType",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="type", type="string",title="Type", example="string"),
 *  @OA\Property(property="is_deleted", type="boo",title="Is deleted", example="boo"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class AppointmentType extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%appointment_type}}';
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
            'type',
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
            [['type'], 'required'],
            [['is_deleted'], 'boolean'],
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'integer'],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
