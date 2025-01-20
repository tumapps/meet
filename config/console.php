<?php

require_once __DIR__ . '/wrapper.php';
$wrapper = new ConfigWrapper();
$config = [
    'id' => $_ENV['APP_CODE'] . '-console',
    'name' => $_ENV['APP_NAME'] . ' Terminal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'cmd\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  $wrapper->load('aliases'),
    'modules' => $wrapper->load('modules'),
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'controllerMap' => [
        'voyage' => [
            'class' => 'cmd\controllers\VoyageController',
            'migrationPath' => $wrapper->load('migrationPaths'),
        ],
        'ws' => [
            'class' => 'cmd\controllers\WebSocketServerController',
        ],
        'appointment' => [
            'class' => 'cmd\controllers\AppointmentController',
        ],
        'email-worker' => [
            'class' => 'cmd\controllers\EmailWorkerController',
        ],
        'mail-queue' => [
            'class' => 'cmd\controllers\MailQueueController',
        ],
        'test-rabbitmq' => [
            'class' => 'cmd\controllers\TestRabbitmqController',
        ],
        'db-backup' => [
            'class' => 'cmd\controllers\BackupController',
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => \helpers\auth\AuthManager::class,
            'cache' => 'cache',
        ],
        'rabbitmq' => [
            'class' => \helpers\RabbitMqComponent::class,
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'root',
            'password' => 'toor',
            'vhost' => '/yii_vhost',
            'logFile' => '@app/providers/bin/logs/worker.log'
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_ENV['HOST_NAME'],
            'port' => $_ENV['REDIS_PORT'],
            'database' => $_ENV['DATABASE'],
            'on afterOpen' => function($event) {
                Yii::info('Connected to Redis', __METHOD__);
            },
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => $_ENV['SCHEME'],
                'host' => $_ENV['HOST'],
                'username' => $_ENV['USERNAME'],
                'password' => $_ENV['PASSWORD'],
                'port' =>$_ENV['MAIL_PORT'],
                'encryption' => $_ENV['ENCRYPTION'],
            ],
        ],
        'backup' => [
            'class' => 'amoracr\backup\Backup',
            'backupDir' => '@app/providers/bin',
            'compression' => 'zip',
            'databases' => ['db'],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                     // for mail worker
                    'categories' => ['email_worker'],
                    'logFile' => '@app/providers/bin/logs/worker.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 5,
                ],
            ],
        ],
        'db' => $wrapper->dbDriver('CORE_DB'),
    ],
    'params' => $wrapper->load('params'),
];

if ($_SERVER['ENVIRONMENT'] == 'dev') {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'model' => [
                'class'     =>  \coder\api\model\Generator::class,
                'useTablePrefix' => true,
            ],
            'crud' => [
                'class'     =>  \coder\api\crud\Generator::class,
                'baseControllerClass' =>  'helpers\ApiController',
            ],
            'module' => [
                'class'     =>  \coder\api\module\Generator::class,
            ]
        ],
    ];
}
return $config;
