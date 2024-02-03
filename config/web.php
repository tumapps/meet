<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => $_ENV['APP_CODE'],
    'name' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'dashboard\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  require __DIR__ . '/aliases.php',
    'modules' => require __DIR__ . '/modules.php',
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'components' => [
        'assetManager' => [
            'basePath' => '@ui/assets/core',
            'baseUrl' => '@web/providers/interface/assets/core',
            'bundles' => [
                /* 'yii\web\JqueryAsset' => [
                    'js' => []
                ], */
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
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => hash_hmac('sha256', md5(date('DYM')), sha1(date('myd'))),
            // 'parsers' => [
            //     'application/json' => 'yii\web\JsonParser',
            // ],
        ],
        // 'response' => [
        //     /* Enable JSON Output: */
        //     'class' => 'yii\web\Response',
        //     'format' => \yii\web\Response::FORMAT_JSON,
        //     'charset' => 'UTF-8',
        //     'on beforeSend' => function ($event) {
        //         $response = $event->sender;
        //         if ($response->data !== null && is_array($response->data)) {
        //             /* delete code param */
        //             if (array_key_exists('code', $response->data)) {
        //                 unset($response->data['code']);
        //             }
        //             /* change status to statusCode */
        //             if (array_key_exists('status', $response->data)) {
        //                 $response->data['statusCode'] = $response->data['status'];
        //                 unset($response->data['status']);
        //             }
        //         }
        //     },
        // ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => \helpers\auth\AuthUser::class,
            'identityClass' => 'auth\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['iam/login'],
            'identityCookie' => ['name' => '_identity-' . $_ENV['APP_CODE'], 'httpOnly' => true]
        ],
        'authManager' => [
            'class' => \helpers\auth\AuthManager::class,
            'cache' => 'cache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['core'],
        'maintenance' => $db['maintenance'],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
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
                    'controller' => require __DIR__ . '/controllerExtracts.php',
                    'extraPatterns' => require __DIR__ . '/extraPatterns.php',
                    'tokens' => require __DIR__ . '/tokens.php',
                ],
            ],
        ],
    ],
    'params' => $params,
];
if ($_SERVER['ENVIRONMENT'] = 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'crud' => [
                'class'     =>  \coder\mono\crud\Generator::class,
                'baseControllerClass' =>  'helpers\ApiController',
            ],
            /* 'module' => [
                'class'     =>  \coder\api\module\Generator::class,
            ] */
        ],
    ];
}
array_push($config,);
return $config;
