<?php

namespace auth\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Tokens",
 *  @OA\Property(property="token_id", type="string",title="Token id", example="string"),
 *  @OA\Property(property="user_id", type="integer",title="User id", example="integer"),
 *  @OA\Property(property="token", type="string",title="Token", example="string"),
 *  @OA\Property(property="token_type", type="string",title="Token type", example="string"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class Tokens extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tokens}}';
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
            'token_type',
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
            [['token_id', 'user_id', 'token', 'token_type'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['token_id'], 'string', 'max' => 64],
            [['token'], 'string', 'max' => 32],
            [['token_type'], 'string', 'max' => 20],
            [['token_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
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
            'token_type' => 'Token Type',
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
