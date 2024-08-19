<?php

namespace auth\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="RefreshToken",
 *  @OA\Property(property="token_id", type="integer",title="Token id", example="integer"),
 *  @OA\Property(property="user_id", type="int",title="User id", example="int"),
 *  @OA\Property(property="token", type="string",title="Token", example="string"),
 *  @OA\Property(property="ip", type="string",title="Ip", example="string"),
 *  @OA\Property(property="user_agent", type="string",title="User agent", example="string"),
 *  @OA\Property(property="data", type="string",title="Data", example="string"),
 *  @OA\Property(property="is_deleted", type="integer",title="Is deleted", example="integer"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class RefreshToken extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%refresh_tokens}}';
    }
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(), 
            [
            'token_id',
            'user_id',
            'token',
            'ip',
            'user_agent',
            'data',
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
            [['user_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['token', 'user_agent', 'created_at', 'updated_at'], 'required'],
            [['token', 'data'], 'string'],
            [['ip'], 'string', 'max' => 32],
            [['user_agent'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token_id' => 'Token ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'ip' => 'Ip',
            'user_agent' => 'User Agent',
            'data' => 'Data',
            'is_deleted' => 'Is Deleted',
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
}
