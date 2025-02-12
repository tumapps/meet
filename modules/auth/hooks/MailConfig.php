<?php

namespace auth\hooks;

use scheduler\models\SystemSettings;
use yii\base\Exception;

class MailConfig
{
    public static function getMailConfig()
    {
        $cacheKey = 'mail_config';
        $cache = \Yii::$app->cache;

        $mailConfig = $cache->get($cacheKey);

        if ($mailConfig === false) {
            try {
                $settings = SystemSettings::find()->one();

                if ($settings) {
                    $mailConfig = [
                        'scheme' => $settings->email_scheme,
                        'host' => $settings->email_smtps,
                        'username' => $settings->email_username,
                        'password' => $settings->email_password,
                        'port' => $settings->email_port,
                        'encryption' => $settings->email_encryption,
                    ];
                    // Cache for 1 hour
                    $cache->set($cacheKey, $mailConfig, 3600);
                } else {
                    throw new Exception('Mail configuration not found in the database.');
                }
            } catch (\Exception $e) {
                \Yii::error('Failed to fetch mail configuration: ' . $e->getMessage());

                $mailConfig = [
                    'scheme' => $_SERVER['EMAIL_SCHEME'] ?? 'smtp',
                    'host' => $_SERVER['EMAIL_HOST'] ?? 'smtp.gmail.com',
                    'username' => $_SERVER['EMAIL_USERNAME'] ?? 'your-email@gmail.com',
                    'password' => $_SERVER['EMAIL_PASSWORD'] ?? 'utws lgpt hsjr jdec',
                    'port' => $_SERVER['EMAIL_PORT'] ?? 465,
                    'encryption' => $_SERVER['EMAIL_ENCRYPTION'] ?? 'tls',
                ];
            }
        }

        return [
            'scheme' => $mailConfig['scheme'],
            'host' => $mailConfig['host'],
            'username' => $mailConfig['username'],
            'password' => $mailConfig['password'],
            'port' => (int) $mailConfig['port'],
            'encryption' => $mailConfig['encryption'],
        ];
    }
}
