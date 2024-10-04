<?php

namespace helpers\traits;


trait Mail
{
	/**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    public static function send($email, $subject, $body)
    {
        $message = \Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([\Yii::$app->params['adminEmail']])
            ->setSubject($subject)
            ->setHtmlBody($body);

        return $message->send();

    }
}
