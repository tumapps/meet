<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => $_ENV['APP_CODE'].'-console',
    'name' => $_ENV['APP_NAME'].' Terminal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'cmd\controllers',
    'timeZone' => 'Africa/Nairobi',
    'aliases' =>  require __DIR__ . '/aliases.php',
    'modules' => require __DIR__ . '/modules.php',
    'runtimePath' => dirname(__DIR__) . '/providers/bin',
    'controllerMap' => [
        'voyage' => [
            'class' => 'cmd\controllers\VoyageController',
            'migrationPath' => require __DIR__ . '/migrations.php',
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
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['core'],
        'maintenance' => $db['maintenance'],
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

// if (YII_ENV_DEV) {
//     // configuration adjustments for 'dev' environment
//     $config['bootstrap'][] = 'gii';
//     $config['modules']['gii'] = [
//         'class' => 'yii\gii\Module',
//     ];
//     // configuration adjustments for 'dev' environment
//     // requires version `2.1.21` of yii2-debug module
//     $config['bootstrap'][] = 'debug';
//     $config['modules']['debug'] = [
//         'class' => 'yii\debug\Module',
//         // uncomment the following to add your IP if you are not connecting from localhost.
//         //'allowedIPs' => ['127.0.0.1', '::1'],
//     ];
// }

if ($_SERVER['ENVIRONMENT'] = 'dev') {
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
