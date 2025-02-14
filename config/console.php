<?php

require_once __DIR__ . '/wrapper.php';
$wrapper = new ConfigWrapper();
$config = [
    'id' => $_SERVER['APP_CODE'] . '-console',
    'name' => $_SERVER['APP_NAME'] . ' Terminal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
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

        // 'mail-queue' => [
        //     'class' => 'cmd\controllers\MailQueueController',
        // ],

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
        'queue' => [
            'class' => \yii\queue\amqp_interop\Queue::class,
            'driver' => yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_BUNNY,
            'dsn' => 'amqp://' . $_SERVER['BROKER_USERNAME'] . ':' . $_SERVER['BROKER_PASSWORD'] . '@' . $_SERVER['BROKER_HOST'] . ':' . $_SERVER['BROKER_PORT'] . '/' . $_SERVER['BROKER_VHOST'],
            // 'dsn' => 'amqp://' . 'guest' . ':' . 'guest' . '@' . 'localhost' . ':' . '5672'. '/' . '%2F',
            'as log' => \yii\queue\LogBehavior::class,
            'heartbeat' => 60,
            'queueName' => $_SERVER['APP_CODE'] . '-queue',
            'exchangeName' => $_SERVER['APP_CODE'] . '-exchange',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_SERVER['HOST_NAME'],
            'port' => $_SERVER['REDIS_PORT'],
            'database' => $_SERVER['DATABASE'],
            'on afterOpen' => function ($event) {
                Yii::info('Connected to Redis', __METHOD__);
            },
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => $_SERVER['SCHEME'],
                'host' => $_SERVER['HOST'],
                'username' => $_SERVER['USERNAME'],
                'password' => $_SERVER['PASSWORD'],
                'port' => $_SERVER['MAIL_PORT'],
                'encryption' => $_SERVER['ENCRYPTION'],
            ],
            // 'transport' => function () {
            //     $settings = \auth\hooks\MailConfig::getMailConfig();

            //     if (!$settings) {
            //         throw new \yii\base\InvalidConfigException('Mail transport configuration is missing.');
            //     }

            //     return [
            //         'scheme' => $settings['scheme'],
            //         'host' => $settings['host'],
            //         'username' => $settings['username'],
            //         'password' => $settings['password'],
            //         'port' => $settings['port'],
            //         'encryption' => $settings['encryption'],
            //     ];
            // },
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
