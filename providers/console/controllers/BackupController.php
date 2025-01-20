<?php

namespace cmd\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\providers\components\GoogleStorageComponent;
use app\providers\components\MailQueueManager;


class BackupController extends Controller
{

    private $backup;
    private $database;
    private $googleStorage;
    private $destination;
    private $adminEmail;
    private  $mailQueue;

    const EVENT_BACKUPFILE_UPLOADED = 'databaseBackupFileUploaded';


    public function init()
    {
        parent::init();
        $this->backup =  Yii::$app->backup;
        $this->database = 'db';
        $this->googleStorage = new GoogleStorageComponent();
        $this->mailQueue = new MailQueueManager();
        $this->destination = 'playground/francis/backups/';
        $this->adminEmail = Yii::$app->params['adminEmail'];
    }

    public function actionIndex()
    {
        $this->stdout('Running backup script...' . PHP_EOL);

        $this->stdout('Backup process completed.' . PHP_EOL);
        return ExitCode::OK;
    }

    public function actionBackup()
    {
        $databases = [$this->database];
        foreach ($databases as $k => $db) {
            $index = (string)$k;
            $this->backup->fileName = 'tumeet_backup';
            $this->backup->fileName .= str_pad($index, 3, '0', STR_PAD_LEFT);
            $this->backup->directories = [];
            $this->backup->databases = [$db];
            $file = $this->backup->create();


            if ($this->uploadBackupFile($file)) {
                $this->stdout('Backup file uploaded successfully: ' . $file . PHP_EOL, \yii\helpers\Console::FG_GREEN);
                unlink($file);
                $this->stdout('Backup file deleted from server: ' . $file . PHP_EOL, \yii\helpers\Console::FG_GREEN);
            } else {
                $this->stdout('Failed to upload backup to Google Cloud' . PHP_EOL, \yii\helpers\Console::FG_RED);
            }
        }
    }

    private function uploadBackupFile($file)
    {
        try {

            $object = $this->googleStorage->uploadFile($file, $this->destination . basename($file), true);

            $file_url = $object->info()['mediaLink'];
            $self_link = $object->info()['selfLink'];

            $this->sendBackupUploadEvent($this->adminEmail, $file_url, $self_link);

            return true;
        } catch (\Exception $e) {
            Yii::error('Google Cloud upload failed: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'Failed to upload file to Google Cloud.'];
        }
    }

    public function sendBackupUploadEvent($email, $file_url, $self_link)
    {
        $subject = 'Database Backup Upload';

        $body = "Dear Admin,\n\n";
        $body .= "The database backup has been successfully uploaded.\n\n";
        $body .= "You can view or download the backup file using the following links:\n";
        $body .= "Download Link: {$file_url}\n";
        $body .= "Self Link: {$self_link}\n\n";
        $body .= "Thank you for managing the system backup.";


        $emailData = [
            'email' => $email,
            'subject' => $subject,
            'body' => $body,
        ];

        if ($this->mailQueue->addToQueue($emailData)) {
            Yii::info('Email queued successfully for: ' . $email, __METHOD__);
        } else {
            Yii::error('Failed to queue email for: ' . $email, __METHOD__);
        }
    }
}
