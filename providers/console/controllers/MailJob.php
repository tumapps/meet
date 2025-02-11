<?php

namespace cmd\controllers;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use helpers\traits\Mail;
use scheduler\models\Appointments;

class MailJob extends BaseObject implements JobInterface
{
    use Mail;

    public $email;
    public $subject;
    public $body;
    public $type;
    public $id;

    public function execute($queue)
    {
        $date = date('Y-m-d H:i:s');

        if (self::send($this->email, $this->subject, $this->body)) {
            if ($this->type === 'reminder' && $this->id !== null || !empty($this->id)) {
                Appointments::updateReminder($this->id);
            }
            Yii::info("Email sent to {$this->email} at: {$date}", 'mailQueue');
        } else {
            Yii::error("Failed to send email to {$this->email}", 'mailQueue');

            Yii::$app->queue->delay(30)->push(new MailJob([
                'email' => $this->email,
                'subject' => $this->subject,
                'body' => $this->body,
                'type' => $this->type,
                'id' => $this->id,
            ]));
        }
    }
}
