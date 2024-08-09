<?php

namespace auth\controllers;

use Yii;
/**
 * Default controller for the `auth` module
 */
class DefaultController extends \helpers\ApiController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return [];
    }

    public function actionLogin()
    {
    	Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	
    	return [
    		'text' => 'welcome to MeetVc App'
    	];
    }
}
