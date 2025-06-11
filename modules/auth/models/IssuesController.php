<?php

namespace auth\controllers;

use Yii;
use auth\models\Issues;
use auth\models\searches\IssuesSearch;
/**
 * @OA\Tag(
 *     name="Issues",
 *     description="Available endpoints for Issues model"
 * )
 */
class IssuesController extends \helpers\ApiController{
    public $permissions = [
        'mainIssuesList'=>'View Issues List',
        'mainIssuesCreate'=>'Add Issues',
        'mainIssuesUpdate'=>'Edit Issues',
        'mainIssuesDelete'=>'Delete Issues',
        'mainIssuesRestore'=>'Restore Issues',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('mainIssuesList');
                $searchModel = new IssuesSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'IssuesSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('mainIssuesView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('mainIssuesCreate');
        $model = new Issues();
        $model->loadDefaultValues();
        $dataRequest['Issues'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Issues added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('mainIssuesUpdate');
        $dataRequest['Issues'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Issues updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('mainIssuesRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Issues restored successfully']);
        } else {
            Yii::$app->user->can('mainIssuesDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Issues deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Issues::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
