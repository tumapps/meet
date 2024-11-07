<?php

namespace auth\controllers;

use Yii;
use auth\models\Assignment;
use auth\models\searches\AssignmentSearch;


class AssignmentController extends \helpers\ApiController {

	public function actionIndex()
	{
		$searchModel = new AssignmentSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'AssignmentSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
	}

	public function actionView($id)
    {
        // Yii::$app->user->can('');
        return $this->payloadResponse($this->findModel($id));
    }


	public function actionCreate()
    {
        // Yii::$app->user->can('assignRole');
        $model = new Assignment();
        $model->loadDefaultValues();
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Record added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('updateRoleAssignment');
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Record updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            // Yii::$app->user->can('RoleAssignmentRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Record restored successfully']);
        } else {
            // Yii::$app->user->can('RoleAssignmentDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Record deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Assignment::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}