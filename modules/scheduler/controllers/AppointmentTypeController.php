<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\AppointmentType;
use scheduler\models\searches\AppointmentTypeSearch;
/**
 * @OA\Tag(
 *     name="AppointmentType",
 *     description="Available endpoints for AppointmentType model"
 * )
 */
class AppointmentTypeController extends \helpers\ApiController{
    public $permissions = [
        'schedulerAppointment-typeList'=>'View AppointmentType List',
        'schedulerAppointment-typeCreate'=>'Add AppointmentType',
        'schedulerAppointment-typeUpdate'=>'Edit AppointmentType',
        'schedulerAppointment-typeDelete'=>'Delete AppointmentType',
        'schedulerAppointment-typeRestore'=>'Restore AppointmentType',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerAppointment-typeList');
                $searchModel = new AppointmentTypeSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'AppointmentTypeSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerAppointment-typeView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerAppointment-typeCreate');
        $model = new AppointmentType();
        $model->loadDefaultValues();
        $dataRequest['AppointmentType'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'AppointmentType added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerAppointment-typeUpdate');
        $dataRequest['AppointmentType'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'AppointmentType updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerAppointment-typeRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'AppointmentType restored successfully']);
        } else {
            Yii::$app->user->can('schedulerAppointment-typeDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'AppointmentType deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = AppointmentType::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
