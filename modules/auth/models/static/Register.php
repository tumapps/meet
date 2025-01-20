<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;
use auth\models\Profiles;
use scheduler\models\Settings;
use helpers\traits\Keygen;
use yii\base\Event;
use helpers\EventHandler;

class Register extends Model
{
    use Keygen;

    const ACCOUNT_CREATED_EVENT = 'passwordResetRequest';

    public $username;
    public $password;
    public $confirm_password;

    // profile fields
    public $first_name;
    public $middle_name;
    public $last_name;
    public $full_name;
    public $email_address;
    public $mobile_number;


    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required', 'message' => 'Username is required'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'An account with similar username already exists.'],
            ['email_address', 'unique', 'targetClass' => Profiles::class, 'message' => 'An account with similar email already exists.'],

            // ['confirm_password', 'required', 'message' => 'This field can not be blank'],
            // ['password', 'required', 'message' => 'Please choose a password you can remember'],
            // ['password', 'string', 'min' => 8],
            // ['password', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
            // ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],

            //profile validation
            [['first_name', 'last_name', 'email_address'], 'required'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            [['email_address'], 'email'],
            [['full_name'], 'safe'],
            [['mobile_number'], 'string', 'max' => 13],
            // ['mobile_number', 'match', 'pattern' => '/^\+?[0-9]{7,15}$/', 'message' => 'Phone number must be a valid integer with a maximum of 15 digits.'],
            ['mobile_number', 'validateMobileNumber'],
            // [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }
    //Abc#123456
    // public function save1()
    // {
    //     $uid = $this->uid('USERS', true);
    //     $user = new User();
    //     $user->user_id = $uid;
    //     $user->username = $this->username;
    //     $user->setPassword($this->password);
    //     $user->generateAuthKey();
    //     if($this->validate()){
    //         if ($user->save(false)) {
    //             return $user;
    //         }
    //     }
    // }

    public function validateMobileNumber($attribute, $params)
    {
        $pattern = '/^(07|01|\+254)[0-9]{8}$/';

        if (!preg_match($pattern, $this->$attribute)) {
            $this->addError($attribute, 'Invalid phone number');
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $uid = $this->uid('USERS', true);
        $user = new User();
        $user->user_id = $uid;
        $user->username = $this->username;
        $user->setPassword($this->username);
        $user->generateAuthKey();

        $loginUrl = \Yii::$app->params['loginUrl'];

        if ($user->save()) {

            $profile = new Profiles();
            $profile->user_id = $user->user_id;
            $profile->first_name = $this->first_name;
            $profile->middle_name = $this->middle_name;
            $profile->last_name = $this->last_name;
            $profile->full_name = $this->full_name;
            $profile->email_address = $this->email_address;
            $profile->mobile_number = $this->mobile_number;
            // $profile->save(false);
            if ($profile->save(false)) {
                $settings = new Settings();
                $settings->user_id = $user->user_id;
                $settings->start_time = '08:00:00';
                $settings->end_time = '17:00:00';
                $settings->slot_duration = 30; // 30 mins by default
                $settings->booking_window = 12; // 12 months by default
                $settings->advanced_booking = 30; //dfault 30 min

                if ($settings->save()) {

                    $auth = Yii::$app->authManager;
                    $defaultRole = $auth->getRole('user');

                    if ($defaultRole) {
                        $auth->assign($defaultRole, $user->user_id);
                    }

                    $subject = 'Account Created';

                    $eventData = [
                        'username' => $this->username,
                        'email' => $this->email_address,
                        'subject' => $subject,
                        'loginLink' => $loginUrl,
                        'contact_name' => ucfirst($this->first_name) . ' ' . ucfirst($this->last_name),
                    ];

                    $this->on(self::ACCOUNT_CREATED_EVENT, [EventHandler::class, 'onAccountCreation'], $eventData);

                    $event = new Event();
                    $this->trigger(self::ACCOUNT_CREATED_EVENT, $event);
                    return $user;
                }
            } else {
                $user->delete();
                return false;
            }
        } else {
            return false;
        }
    }
}
