<?php

namespace auth\models;

use Yii;
use auth\models\User;

/**
 *@OA\Schema(
 *  schema="Otp",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="user_id", type="int",title="User id", example="int"),
 *  @OA\Property(property="code", type="integer",title="Code", example="integer"),
 *  @OA\Property(property="type", type="string",title="Type", example="string"),
 *  @OA\Property(property="expires_at", type="integer",title="Expires at", example="integer"),
 *  @OA\Property(property="is_used", type="int",title="Is used", example="int"),
 *  @OA\Property(property="is_deleted", type="integer",title="Is deleted", example="integer"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class Otp extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%otp_verification}}';
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
                'code',
                'type',
                'expires_at',
                'is_used',
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
            [['user_id', 'code', 'expires_at', 'is_used', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'code', 'expires_at', 'is_used', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['code', 'type', 'expires_at', 'created_at', 'updated_at'], 'required'],
            [['type'], 'string', 'max' => 20],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'user_id']],
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
            'code' => 'Code',
            'type' => 'Type',
            'expires_at' => 'Expires At',
            'is_used' => 'Is Used',
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
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    public  function getOtp($userId)
    {
        $otp = self::find()
            ->where(['user_id' => $userId, 'is_used' => 0, 'is_deleted' => 0])
            // ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if ($otp && $otp->expires_at > time()) {
            return $otp;
        }

        return null;
    }

    /**
     * Verifies an OTP code for a given user.
     *
     * @param int $userId
     * @param int|string $code
     * @return bool true if OTP is valid, false otherwise
     */
    public function verifyOtp($userId, $code)
    {
        $otp = self::find()
            ->where([
                'user_id' => $userId,
                'code' => $code,
                // 'is_used' => 0,
                // 'is_deleted' => 0
            ])
            ->andWhere(['>', 'expires_at', time()])
            ->one();

        if ($otp) {
            $otp->is_used = 1;
            $otp->updated_at = time();
            $otp->save(false);

            return true;
        }

        return false;
    }

    public function generateOtp()
    {
        return rand(100000, 999999);
    }
}
