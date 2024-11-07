<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Events;
use scheduler\models\searches\EventsSearch;
/**
 * @OA\Tag(
 *     name="Events",
 *     description="Available endpoints for Events model"
 * )
 */
class EventsController extends \helpers\ApiController{
    public $permissions = [
        'schedulerEventsList'=>'View Events List',
        'schedulerEventsCreate'=>'Add Events',
        'schedulerEventsUpdate'=>'Edit Events',
        'schedulerEventsDelete'=>'Delete Events',
        'schedulerEventsRestore'=>'Restore Events',
        ];
    public function actionIndex()
    {
        // Yii::$app->user->can('schedulerEventsList');
        $searchModel = new EventsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'EventsSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerEventsView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        // Yii::$app->user->can('schedulerEventsCreate');
        $model = new Events();
        $model->loadDefaultValues();
        $dataRequest['Events'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Events added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerEventsUpdate');
        $dataRequest['Events'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Events updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerEventsRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Events restored successfully']);
        } else {
            Yii::$app->user->can('schedulerEventsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Events deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Events::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
