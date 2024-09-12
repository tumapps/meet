<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;
use auth\models\Profiles;
use auth\models\Tokens;
use helpers\traits\Mail;


class PasswordResetRequest extends Model
{
	use Mail;

	public $username;

	/**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string'],
            ['username', 'exist',
                'targetClass' => '\auth\models\User',
                'message' => 'There is no user with this username.'
            ],
        ];
    }

    public function sendEmail()
    {
        $userId = User::find()->select('user_id')->where(['username' => $this->username, 'status'=> User::STATUS_ACTIVE])->scalar();

        if (!$userId) {
            return false;
        }

        // get user email_address from profiles model
        $email = Profiles::findOne([
            'user_id' => $userId,
        ]);

        if (!$email) {
            return false;
        }
        // return $email->email_address;
        // get user token ie password reset token;
        $user = new User();
        $password_reset_token = Yii::$app->security->generateRandomString(7) . '_' . time();

        $tokens = new Tokens();
        $tokens->user_id = $userId;
        $tokens->token = $password_reset_token;
        $tokens->token_type = 'password_reset_token';
        $tokens->token_id = $tokens->uid('TOKENS', true);
        $tokens->save();


        // $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $password_reset_token]);
        $resetLink = Yii::$app->params['passwordResetLink'].$password_reset_token;
         
        // Email subject and body
        $subject = 'Password Reset Request';
        $body = "
            <p>Hello, '{ $this->username }'</p>
            <p>You have requested a password reset. Please click the link below to reset your password:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        if ($this->send($email->email_address, $subject, $body)) {
            return true;
        }

        return false;
    }

}