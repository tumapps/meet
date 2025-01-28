<?php

namespace scheduler\models;

use Yii;

/**
 *@OA\Schema(
 *  schema="OperationReasons",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="entity_id", type="integer",title="Entity id", example="integer"),
 *  @OA\Property(property="type", type="string",title="Type", example="string"),
 *  @OA\Property(property="entity_type", type="integer",title="Entity type", example="integer"),
 *  @OA\Property(property="reason", type="string",title="Reason", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_by", type="integer",title="Created by", example="integer"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class OperationReasons extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operation_reasons}}';
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
                'entity_id',
                'type',
                'entity_type',
                'reason',
                'is_deleted',
                'created_by',
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
            [['entity_id', 'entity_type', 'created_by', 'created_at', 'updated_at'], 'required'],
            [['entity_id', 'entity_type', 'is_deleted', 'created_by', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['entity_id', 'entity_type', 'is_deleted', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['type', 'reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_id' => 'Entity ID',
            'type' => 'Type',
            'entity_type' => 'Entity Type',
            'affected_person_id' => 'Affected Person ID',
            'reason' => 'Reason',
            'is_deleted' => 'Is Deleted',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function saveActionReason($entityId, $reason, $type, $entityType, $affectedUserId, $createdBy)
    {
        $this->entity_id = $entityId;
        $this->reason = $reason;
        $this->type = $type;
        $this->entity_type = $entityType;
        $this->affected_user_id = $affectedUserId;
        $this->created_by = $createdBy;

        if (!$this->save()) {
            // throw new \Exception('Failed to save the reason for the action.');
            return false;
        }

        return true;
    }

    public static function getActionReason($entityId, $user_id)
    {
        $reason = OperationReasons::find()
            ->select('reason')
            ->where([
                'entity_id' => $entityId,
                'affected_user' => $user_id,
            ])
            ->scalar();

        return $reason ?: 'No reason found';
    }
}
