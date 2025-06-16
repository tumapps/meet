<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\SystemSettings;
use scheduler\models\searches\SystemSettingsSearch;

/**
 * @OA\Tag(
 *     name="SystemSettings",
 *     description="Available endpoints for SystemSettings model"
 * )
 */
class SystemSettingsController extends \helpers\ApiController
{
    public $permissions = [
        'schedulerSystem-settingsList' => 'View SystemSettings List',
        'schedulerSystem-settingsCreate' => 'Add SystemSettings',
        'schedulerSystem-settingsUpdate' => 'Edit SystemSettings',
        'schedulerSystem-settingsDelete' => 'Delete SystemSettings',
        'schedulerSystem-settingsRestore' => 'Restore SystemSettings',
    ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerSystem-settingsList');
        $searchModel = new SystemSettingsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'SystemSettingsSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerSystem-settingsView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerSystem-settingsCreate');
        $model = new SystemSettings();
        $model->loadDefaultValues();
        $dataRequest['SystemSettings'] = Yii::$app->request->getBodyParams();
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'SystemSettings added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerSystem-settingsUpdate');
        $dataRequest['SystemSettings'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'SystemSettings updated successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerSystem-settingsRestore');
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'SystemSettings restored successfully']);
        } else {
            Yii::$app->user->can('schedulerSystem-settingsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'SystemSettings deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = SystemSettings::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
