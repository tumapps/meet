<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;
use auth\models\Profiles;
use helpers\traits\Keygen;

class Register extends Model
{
    use Keygen;

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

            ['confirm_password', 'required', 'message' => 'This field can not be blank'],
            ['password', 'required', 'message' => 'Please choose a password you can remember'],
            ['password', 'string', 'min' => 8],
            ['password', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],

            //profile validation
            [['first_name', 'last_name', 'email_address'], 'required'],
            [['first_name', 'last_name','middle_name'], 'string', 'max' => 50],
            [['email_address'], 'string', 'max' => 128],
            [['email_address'], 'email'],
            [['full_name'], 'safe'],
            [['mobile_number'], 'string', 'max' => 15],
            ['mobile_number', 'match', 'pattern' => '/^\+?[0-9]{7,15}$/', 'message' => 'Phone number must be a valid integer with a maximum of 15 digits.'],
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
    
    public function save()
    {
        if(!$this->validate()){
            return false;
        }

        $uid = $this->uid('USERS', true);
        $user = new User();
        $user->user_id = $uid;
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->generateAuthKey();

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
            if($profile->save(false)){
                return $user;
            }
            else {
                $user->delete();
                return false;
            }

        } else {
            return false;
        }
        
    }

}
