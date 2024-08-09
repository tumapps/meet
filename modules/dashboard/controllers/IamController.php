<?php

namespace dashboard\controllers;

use Yii;
use auth\models\static\Login;

class IamController extends \helpers\DashboardController
{
    public function actionLogin()
    {
        $this->layout = 'auth';
        $model = new Login();
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

}
