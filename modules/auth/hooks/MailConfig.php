<?php

namespace auth\hooks;

use scheduler\models\SystemSettings;
use yii\base\Exception;
use Yii;

class MailConfig
{
    public static function getMailConfig(): ?array
    {
        try {
            $settings = SystemSettings::find()->one();

            if ($settings) {
                return [
                    'scheme' => $settings->scheme ?? 'smtp',
                    'host' => $settings->host ?? 'smtp.example.com',
                    'username' => $settings->username ?? '',
                    'password' => $settings->password ?? '',
                    'port' => $settings->port ?? 587,
                    'encryption' => $settings->encryption ?? 'tls',
                ];
            } else {
                throw new Exception('Mail configuration not found in the database.');
            }
        } catch (\Exception $e) {
            Yii::error('Failed to fetch mail configuration: ' . $e->getMessage());
            return null;
        }
    }
}
