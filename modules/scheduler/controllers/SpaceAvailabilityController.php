<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\SpaceAvailability;
use scheduler\models\searches\SpaceAvailabilitySearch;
/**
 * @OA\Tag(
 *     name="SpaceAvailability",
 *     description="Available endpoints for SpaceAvailability model"
 * )
 */
class SpaceAvailabilityController extends \helpers\ApiController{
    public $permissions = [
        'schedulerSpace-availabilityList'=>'View SpaceAvailability List',
        'schedulerSpace-availabilityCreate'=>'Add SpaceAvailability',
        'schedulerSpace-availabilityUpdate'=>'Edit SpaceAvailability',
        'schedulerSpace-availabilityDelete'=>'Delete SpaceAvailability',
        'schedulerSpace-availabilityRestore'=>'Restore SpaceAvailability',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerSpace-availabilityList');
        $searchModel = new SpaceAvailabilitySearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'SpaceAvailabilitySearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerSpace-availabilityView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerSpace-availabilityCreate');
        $model = new SpaceAvailability();
        $model->loadDefaultValues();
        $dataRequest['SpaceAvailability'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'SpaceAvailability added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerSpace-availabilityUpdate');
        $dataRequest['SpaceAvailability'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'SpaceAvailability updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerSpace-availabilityRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'SpaceAvailability restored successfully']);
        } else {
            Yii::$app->user->can('schedulerSpace-availabilityDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'SpaceAvailability deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = SpaceAvailability::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
