<?php

namespace auth\models;

use Yii;
use auth\models\User;
use borales\extensions\phoneInput\PhoneInputValidator;


/**
 * This is the model class for table "profiles".
 *
 * @property int $profile_id
 * @property int|null $user_id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string $email_address
 * @property string|null $mobile_number
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class Profiles extends \helpers\ActiveRecord
{
    public $full_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profiles}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email_address', 'mobile_number'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 50, 'min' => 3],
            [['middle_name'], 'string', 'max' => 50, 'min' => 1],
            [['email_address'], 'string', 'max' => 128],
            [['email_address'], 'email'],
            [['full_name'], 'safe'],

            // [['mobile_number'], PhoneInputValidator::className(), 'region' => ['KE']],
            [['mobile_number'], 'validateMobileNumber'],
            [['first_name', 'last_name','middle_name'], 'match', 'pattern' => "/^[a-zA-Z']+$/", 'message' => 'The name can only contain alphabetic characters and apostrophes'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }


    public function validateMobileNumber($attribute, $params)
    {
        $pattern = '/^(07|01|\+2547)[0-9]{8}$/';

        if (!preg_match($pattern, $this->$attribute)) {
            $this->addError($attribute, 'Invalid phone number');
        }
    }

    public function validateName($attribute, $params)
    {
        $name = $this->$attribute;

        if (!preg_match("/^[a-zA-Z']+$/", $name)) {
            $this->addError($attribute, 'The name can only contain alphabetic characters');
        }
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'full_name' => 'Full Name',
            'email_address' => 'Email Address',
            'mobile_number' => 'Mobile Number',
        ];
    }
    public function afterFind()
	{
		$this->full_name = $this->first_name.' '.$this->middle_name.' '.$this->last_name;
		return parent::afterFind();
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