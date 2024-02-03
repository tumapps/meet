<?php

namespace mainstream\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Databanks",
 *  @OA\Property(property="databank_id", type="string",title="Databank id", example="string"),
 *  @OA\Property(property="databank_name", type="string",title="Databank name", example="string"),
 *  @OA\Property(property="category", type="string",title="Category", example="string"),
 *  @OA\Property(property="security_key", type="string",title="Security key", example="string"),
 *  @OA\Property(property="description", type="string",title="Description", example="string"),
 * )
 */

class Databanks extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%databanks}}';
    }
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(), 
            [
            'databank_id',
            'databank_name',
            'category',
            'security_key',
            'description',
            'status',
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
            [['databank_id', 'databank_name', 'category', 'security_key', 'created_at', 'updated_at'], 'required'],
            [['security_key', 'description'], 'string'],
            [['status', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['status', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['databank_id'], 'string', 'max' => 64],
            [['databank_name'], 'string', 'max' => 100],
            [['category'], 'string', 'max' => 5],
            [['databank_name'], 'unique'],
            [['security_key'], 'unique'],
            [['databank_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'databank_id' => 'Databank ID',
            'databank_name' => 'Databank Name',
            'category' => 'Category',
            'security_key' => 'Security Key',
            'description' => 'Description',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
