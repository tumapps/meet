<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Availability;
use scheduler\models\searches\AvailabilitySearch;
/**
 * @OA\Tag(
 *     name="Availability",
 *     description="Available endpoints for Availability model"
 * )
 */
class AvailabilityController extends \helpers\ApiController{
    public $permissions = [
        'schedulerAvailabilityList'=>'View Availability List',
        'schedulerAvailabilityCreate'=>'Add Availability',
        'schedulerAvailabilityUpdate'=>'Edit Availability',
        'schedulerAvailabilityDelete'=>'Delete Availability',
        'schedulerAvailabilityRestore'=>'Restore Availability',
        ];
    public function actionIndex()
    {
        // Yii::$app->user->can('schedulerAvailabilityList');
        $searchModel = new AvailabilitySearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'AvailabilitySearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerAvailabilityView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerAvailabilityCreate');
        $model = new Availability();
        $model->loadDefaultValues();
        $dataRequest['Availability'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Availability added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerAvailabilityUpdate');
        $dataRequest['Availability'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Availability updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerAvailabilityRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Availability restored successfully']);
        } else {
            Yii::$app->user->can('schedulerAvailabilityDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Availability deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Availability::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
