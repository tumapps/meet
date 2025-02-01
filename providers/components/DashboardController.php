<?php

namespace helpers;

use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;

class DashboardController extends  \yii\web\Controller
{
    public $layout = 'dashboard';
    public function behaviors()
    {
        $auth     = isset(\Yii::$app->params['activateAuth']) ? \Yii::$app->params['activateAuth'] : FALSE;
        $origins  = isset(\Yii::$app->params['allowedDomains']) ? \Yii::$app->params['allowedDomains'] : "*";
        
        $behavior = [
            'class' => Cors::class,
            'cors'  => [
                'Origin'                           => [$origins],
                'Access-Control-Allow-Origin'      => [$origins],
                'Access-Control-Request-Headers'   => ['*'],
                'Access-Control-Request-Method'    => ['POST', 'PUT', 'PATCH', 'GET', 'DELETE', 'HEAD'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age'           => 3600,
            ],
        ];

        if ($auth) {
            $behaviors = parent::behaviors();
            unset($behaviors['authenticator']);
            $behaviors['corsFilter'] = $behavior;
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::className(),
            ];
            $behaviors['authenticator']['except'] = \Yii::$app->params['safeEndpoints'];
        } else {
            $behaviors['corsFilter'] = $behavior;
        }

        $access = [
            'as access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['register', 'login', 'error', 'request-password-reset', 'reset-password'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
        return array_merge(parent::behaviors(), $access, $behavior);
    }
}
