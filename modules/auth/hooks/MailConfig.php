<?php

namespace auth\hooks;

use scheduler\models\SystemSettings;
use yii\base\Exception;

class MailConfig
{
    public static function getMailConfig()
    {
        try {
            $settings = SystemSettings::find()->one();

            if ($settings) {
                return [
                    'scheme' => $settings->email_scheme,
                    'host' => $settings->email_smtps,
                    'username' => $settings->email_username,
                    'password' => $settings->email_password,
                    'port' => (int) $settings->email_port,
                    'encryption' => $settings->email_encryption,
                ];
            } else {
                throw new Exception('Mail configuration not found in the database.');
            }
        } catch (\Exception $e) {
            \Yii::error('Failed to fetch mail configuration: ' . $e->getMessage());

            return [
                'scheme' => $_SERVER['EMAIL_SCHEME'] ?? 'smtp',
                'host' => $_SERVER['EMAIL_HOST'] ?? 'smtp.gmail.com',
                'username' => $_SERVER['EMAIL_USERNAME'] ?? 'your-email@gmail.com',
                'password' => $_SERVER['EMAIL_PASSWORD'] ?? 'utws lgpt hsjr jdec',
                'port' => (int) ($_SERVER['EMAIL_PORT'] ?? 465),
                'encryption' => $_SERVER['EMAIL_ENCRYPTION'] ?? 'tls',
            ];
        }
    }
}
