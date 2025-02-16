<?php

namespace scheduler\models;

use Yii;
use auth\models\user;

/**
 *@OA\Schema(
 *  schema="ManagedUsers",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="secretary_id", type="integer",title="Secretary id", example="integer"),
 *  @OA\Property(property="user_id", type="integer",title="User id", example="integer"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class ManagedUsers extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%managed_users}}';
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
                'secretary_id',
                'user_id',
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
            [['secretary_id', 'user_id'], 'required'],
            [['secretary_id', 'user_id', 'is_deleted'], 'default', 'value' => null],
            [['secretary_id', 'user_id', 'is_deleted'], 'integer'],
            [['secretary_id', 'user_id'], 'validateAssignment'],
            [['secretary_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['secretary_id' => 'user_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \auth\models\User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'secretary_id' => 'Secretary ID',
            'user_id' => 'User ID',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validateAssignment($attribute, $params)
    {
        if ($this->user_id == $this->secretary_id) {
            $this->addError($attribute, 'A user cannot be assigned as their own secretary.');
        }

        $authManager = Yii::$app->authManager;
        $secretaryRoles = array_keys($authManager->getRolesByUser($this->secretary_id));

        if (!in_array('secretary', $secretaryRoles)) {
            $this->addError('secretary_id', 'The assigned secretary does not have the required role.');
        }

        $alreadyAssigned = self::find()
            ->where(['user_id' => $this->user_id, 'secretary_id' => $this->secretary_id])
            ->exists();

        if ($alreadyAssigned) {
            $this->addError($attribute, 'This user is already assigned to the selected secretary.');
        }
    }

    /**
     * Gets query for [[Secretary]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSecretary()
    {
        return $this->hasOne(User::class, ['user_id' => 'secretary_id']);
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
}
