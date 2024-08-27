<?php

namespace helpers\traits;


trait Mail
{
	/**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    public function send($email)
    {
        if ($this->validate()) {
            $message = Yii::$app->mailer->compose()
                ->setTo($this->email)
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['senderName']])
                ->setReplyTo([$this->email => $this->name])
                ->setSubject($this->subject)
                // ->setTextBody($this->body)
                ->setHtmlBody($this->body);


        if ($this->attachFile !== null) {
            $message->attach($this->attachFile->tempName, [
                    'fileName' => $this->attachFile->baseName . '.' . $this->attachFile->extension,
                    'contentType' => $this->attachFile->type,
                ]);
            }
            // $result = $message->send();
            $message->send();
                // ->send();

            return true;
        }
        return false;
    }
}
