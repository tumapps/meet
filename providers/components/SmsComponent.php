<?php

namespace app\providers\components;

use Yii;
use yii\base\Component;
use AfricasTalking\SDK\AfricasTalking;

class SmsComponent extends Component
{
    public $apiKey;
    public $username;

    public $messageType = 'SMS'; // Can be SMS or flash
    public $enqueue = false;

    /**
     * @var AfricasTalking
     */

    private $AT;

    /**
     * Initializes the component and sets up the Africa's Talking SDK.
     */
    public function init()
    {
        parent::init();

        $this->apiKey = Yii::$app->params['africasTalkingApi'];
        $this->username = Yii::$app->params['africasTalkingUserName'];

        if (empty($this->apiKey)) {
            throw new \Exception('Africa\'s Talking API key is not configured');
        }

        $this->AT = new AfricasTalking($this->username, $this->apiKey);
    }

    /**
     * Sends an SMS message.
     *
     * @param string|array $recipient The recipient phone number(s).
     * @param string $message The message to send.
     * @return array|false The API response on success, or false on failure.
     */
    // public function send($recipient, string $message)
    // {
    //     try {
    //         $sms = $this->AT->sms();

    //         $response = $sms->send([
    //             'to'      => is_array($recipient) ? implode(',', $recipient) : $recipient,
    //             'message' => $message,
    //         ]);

    //         Yii::info("SMS sent: " . json_encode($response), __METHOD__);
    //         return $response;
    //     } catch (\Exception $e) {
    //         Yii::error("Failed to send SMS: " . $e->getMessage(), __METHOD__);
    //         return false;
    //     }
    // }

    public function send($recipient, string $message, array $options = [])
    {
        try {
            $sms = $this->AT->sms();

            // For promotional messages in Kenya, add FREE MSG prefix
            if (strpos($recipient, '+254') === 0 && $this->messageType === 'SMS') {
                $message = "FREE MSG " . $message;
            }

            $params = array_merge([
                'to' => is_array($recipient) ? implode(',', $recipient) : $recipient,
                'message' => $message,
                'enqueue' => $this->enqueue,
            ], $options);

            // Send SMS and convert response to array
            $response = $sms->send($params);
            $responseArray = json_decode(json_encode($response), true);

            Yii::info("SMS sent: " . json_encode($responseArray), __METHOD__);

            // Check for failed recipients - now using object syntax
            if (isset($response->data)) {
                $recipients = $response->data->SMSMessageData->Recipients ?? [];
                foreach ($recipients as $recipient) {
                    if ($recipient->statusCode != 101) {
                        Yii::warning(
                            "Failed to send to {$recipient->number}: {$recipient->status}",
                            __METHOD__
                        );
                    }
                }
            }

            return $responseArray;
        } catch (\Exception $e) {
            Yii::error("Failed to send SMS: " . $e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
