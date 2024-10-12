<?php

require_once __DIR__ . '/wrapper.php';
$wrapper = new ConfigWrapper();
$config = [
    'id' => $_ENV['APP_CODE'],
    'name' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'main\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  $wrapper->load('aliases'),
    'modules' => $wrapper->load('modules'),
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'components' => [
        'assetManager' => [
            'basePath' => '@ui/assets/core',
            'baseUrl' => '@web/providers/interface/assets/core',
            'bundles' => [
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => []
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ],

            ],
        ],
        'view' => [
            'theme' => [
                'basePath' => '@ui/views',
                'pathMap' => [
                    '@app/views' => '@ui/views',
                    '@dashboard/views' => '@ui/views',
                ],
            ],
        ],
        'rabbitmq' => [
            'class' => \helpers\RabbitMqComponent::class,
            'host' => $_ENV['_HOST'],
            'port' => $_ENV['_PORT'],
            'user' => $_ENV['_USER'],
            'password' => $_ENV['_PASSWORD'],
            'vhost' => $_ENV['V_HOST'],
            'logFile' => '@app/providers/bin/logs/worker.log'
        ],
        'request' => [
            'cookieValidationKey' => hash_hmac('sha256', md5(date('DYM')), sha1(date('myd'))),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            /* Enable JSON Output: */
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && is_array($response->data)) {
                    /* delete code param */
                    if (array_key_exists('code', $response->data)) {
                        unset($response->data['code']);
                    }
                    /* change status to statusCode */
                    if (array_key_exists('status', $response->data)) {
                        $response->data['statusCode'] = $response->data['status'];
                        unset($response->data['status']);
                    }
                }
            },
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => \helpers\auth\AuthUser::class,
            'identityClass' => 'auth\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['dashboard/iam/login'],
            'identityCookie' => ['name' => '_identity-' . $_ENV['APP_CODE'], 'httpOnly' => true]
        ],
        'authManager' => [
            'class' => \helpers\auth\AuthManager::class,
            'cache' => 'cache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // 'mailer' => [
        //     'class' => \yii\symfonymailer\Mailer::class,
        //     'viewPath' => '@app/mail',
        //     'useFileTransport' => false,
        // ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtps',
                'host' => 'smtp.gmail.com',
                'username' => 'francisyuppie@gmail.com',
                'password' => 'utws lgpt hsjr jdec',
                'port' => 465,
                'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info', 'warning',],
                ],
                // [
                //     'class' => 'yii\log\FileTarget',
                //     'levels' => ['error', 'info', 'warning',],
                //     'categories' => ['email_worker'],
                //     'logFile' => '@app/providers/bin/logs/worker.log',
                //     'maxFileSize' => 1024 * 2,
                //     'maxLogFiles' => 5,
                // ],
            ],
        ],
        'db' => $wrapper->dbDriver('CORE_DB'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                $_ENV['APP_VERSION'] . '/about' => 'site/about',
                [
                    'pattern' => $_ENV['APP_VERSION'] . '/docs/openapi-json-resource',
                    'route' => 'site/json-docs',
                    'suffix' => '.json'
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'prefix' =>  $_ENV['APP_VERSION'],
                    'controller' => $wrapper->load('controllers'),
                    'extraPatterns' => $wrapper->load('routes'),
                    'tokens' =>  $wrapper->load('tokens'),
                ],
                // 'POST v1/auth/login' => 'auth/auth/login',
                // 'POST v1/auth/user' => 'auth/auth/me',
                // 'POST v1/auth/refresh' => 'auth/auth/refresh',
                // 'GET v1/auth/default' => 'auth/default/login',
            ],

            
        ],
    ],
    'params' => $wrapper->load('params'),
    // 'defaultRoute' => '/dashboard',
];
if ($_SERVER['ENVIRONMENT'] == 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'crud' => [
                'class'     =>  \coder\mono\crud\Generator::class,
                'baseControllerClass' =>  'helpers\DashboardController',
            ],
            /* 'module' => [
                'class'     =>  \coder\api\module\Generator::class,
            ] */
        ],
    ];
}
array_push($config,);
return $config;
