<?php

namespace app\providers\components;

use Yii;
use yii\base\Component;
use AfricasTalking\SDK\AfricasTalking;

class SmsComponent extends Component
{
    public $apiKey;
    public $username;

    private $AT;

    /**
     * Initializes the component and sets up the Africa's Talking SDK.
     */
    public function init()
    {
        parent::init();

        $this->apiKey = Yii::$app->params['africasTalkingApi'];
        $this->username = Yii::$app->params['africasTalkingUserName'];

        $this->AT = new AfricasTalking($this->username, $this->apiKey);
    }

    /**
     * Sends an SMS message.
     *
     * @param string|array $recipient The recipient phone number(s).
     * @param string $message The message to send.
     * @return array|false The API response on success, or false on failure.
     */
    public function send($recipient, string $message)
    {
        try {
            $sms = $this->AT->sms();

            $response = $sms->send([
                'to'      => is_array($recipient) ? implode(',', $recipient) : $recipient,
                'message' => $message,
            ]);

            Yii::info("SMS sent: " . json_encode($response), __METHOD__);
            return $response;
        } catch (\Exception $e) {
            Yii::error("Failed to send SMS: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
