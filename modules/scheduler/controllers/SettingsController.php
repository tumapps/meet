<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Settings;
use scheduler\models\searches\SettingsSearch;
/**
 * @OA\Tag(
 *     name="Settings",
 *     description="Available endpoints for Settings model"
 * )
 */
class SettingsController extends \helpers\ApiController
{
    public $permissions = [
        'schedulerSettingsList'=>'View Settings List',
        'schedulerSettingsCreate'=>'Add Settings',
        'schedulerSettingsUpdate'=>'Edit Settings',
        'schedulerSettingsDelete'=>'Delete Settings',
        'schedulerSettingsRestore'=>'Restore Settings',
        ];
    public function actionIndex()
    {
        // Yii::$app->user->can('schedulerSettingsList');
        $searchModel = new SettingsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'SettingsSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        // Yii::$app->user->can('schedulerSettingsView');
        $setting_id = Settings::getSettingId($id);

        if(!$setting_id) {
            return $this->payloadResponse(['message' => 'Settings Not Found for the current User']);
        }

        return $this->payloadResponse($this->findModel($setting_id->id));
    }

    public function actionCreate()
    {
        // Yii::$app->user->can('schedulerSettingsCreate');
        $model = new Settings();
        $model->loadDefaultValues();
        $dataRequest['Settings'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Settings added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('schedulerSettingsUpdate');
        $dataRequest['Settings'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Settings updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerSettingsRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Settings restored successfully']);
        } else {
            Yii::$app->user->can('schedulerSettingsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Settings deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Settings::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
