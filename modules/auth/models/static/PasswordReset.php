<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;
use auth\models\Tokens;
use helpers\traits\Keygen;
use yii\base\InvalidArgumentException;

class PasswordReset extends Model
{

    public $password;
    public $confirm_password;
    private $_user;
    private $_token;
    private $_isMobile = false;

    /**
     * Constructor to validate the token and initialize the user.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    // public function __construct($token,  $config = [])
    // {
    //     if (empty($token) || !is_string($token)) {
    //         throw new InvalidArgumentException('Password reset token cannot be blank.');
    //     }

    //     $this->_token = Tokens::findOne(['token' => $token, 'token_type' => 'password_reset_token', 'status' => 1]);
    //     if (!$this->_token || !$this->isPasswordResetTokenValid($this->_token->token)) {
    //         throw new InvalidArgumentException('Invalid or expired password reset token.');
    //     }

    //     $this->_user = User::findOne(['user_id' => $this->_token->user_id, 'status' => User::STATUS_ACTIVE]);
    //     if (!$this->_user) {
    //         throw new InvalidArgumentException('User associated with this token was not found or is inactive.');
    //     }

    //     parent::__construct($config);
    // }
    public function __construct($token = null, $isMobile = false, $config = [])
    {
        $this->_isMobile = $isMobile;

        if (!$this->_isMobile) {
            if (empty($token)) {
                throw new InvalidArgumentException('Password reset token cannot be blank.');
            }

            $this->_token = Tokens::findOne([
                'token' => $token,
                'token_type' => 'password_reset_token',
                'status' => 1
            ]);

            if (!$this->_token || !$this->isPasswordResetTokenValid($this->_token->token)) {
                throw new InvalidArgumentException('Invalid or expired password reset token.');
            }

            $this->_user = User::findOne([
                'user_id' => $this->_token->user_id,
                'status' => User::STATUS_ACTIVE
            ]);
        } else {
            parent::__construct($config);
            return;
        }

        if (!$this->_user) {
            throw new InvalidArgumentException('User not found or inactive.');
        }

        parent::__construct($config);
    }


    public function rules()
    {
        return [
            ['password', 'required', 'message' => 'This field can not be blank'],
            ['confirm_password', 'required', 'message' => 'This field can not be blank'],
            ['password', 'string', 'min' => 4],
            ['password', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],
        ];
    }

    public function isPasswordResetTokenValid($token)
    {
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Resets the user's password and updates the token status.
     *
     * @return bool
     */
    public function resetPassword()
    {
        $this->_user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        if ($this->_user->save()) {
            // Invalidate the token by setting its status to 0
            $this->_token->status = 0;
            return $this->_token->save();
        }
        return false;
    }

    public function resetPassword2($user_id)
    {
        if ($this->_isMobile) {
            return $this->resetPasswordMobile($user_id);
        }
        return false;
    }

    public function resetPasswordMobile($user_id)
    {
        if (!$this->_isMobile) {
            return false;
        }
        $user = User::findOne(['user_id' => $user_id, 'status' => User::STATUS_ACTIVE]);

        $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);

        if ($user->save()) {
            // Invalidate OTP
            $otp = \auth\models\Otp::find()
                ->where([
                    'user_id' => $user->user_id,
                    'type' => 'password_reset'
                ])
                ->andWhere(['is_used' => 0, 'is_deleted' => 0])
                ->one();

            if ($otp) {
                $otp->is_used = 1;
                return $otp->save();
            }
            return true;
        }
        return false;
    }
}
