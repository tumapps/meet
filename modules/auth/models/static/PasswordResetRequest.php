<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\User;
use auth\models\Profiles;
use auth\models\Tokens;
// use helpers\traits\Mail;
use yii\base\Event;
use helpers\EventHandler;


class PasswordResetRequest extends Model
{
	// use Mail;

	public $username;
    const EVENT_PASSWORD_RESET_REQUEST = 'passwordResetRequest';

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
                'message' => 'The provided username does not exists.'
            ],
        ];
    }

    public function sendEmail()
    {

        $userId = User::find()->select('user_id')->where([
            'username' => $this->username, 'status'=> User::STATUS_ACTIVE
        ])->scalar();

        if (!$userId) {
            return false;
        }

        $email = Profiles::findOne([
            'user_id' => $userId,
        ]);

        if (!$email) {
            return false;
        }

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
      
        $body = Yii::$app->view->render('@ui/views/emails/passwordReset', [
            'username' => $this->username,
            'resetLink' => $resetLink,
        ]);

        $eventData = [
            'email' => $email->email_address,
            'subject' => $subject,
            'body' => $body
        ];
       
        $this->on(self::EVENT_PASSWORD_RESET_REQUEST, [EventHandler::class, 'handlePasswordResetRequest'], $eventData);

        $event = new Event();
        $this->trigger(self::EVENT_PASSWORD_RESET_REQUEST, $event);

        if ($event->handled) {
            return true;
        }

        // if (self::send($email->email_address, $subject, $body)) {
        //     return true;
        // }

        return false;
    }

}