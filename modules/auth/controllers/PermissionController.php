<?php

namespace auth\controllers;

use Yii;
use auth\models\AuthItem;
use auth\models\searches\AuthItemSearch;
use yii\rbac\Item;


class PermissionController extends \helpers\ApiController
{

	public function actionIndex()
	{
        $searchModel = new AuthItemSearch();
        $searchModel->type = Item::TYPE_PERMISSION;

        $search = $this->queryParameters(Yii::$app->request->queryParams,'AuthItemSearch');
        Yii::debug($search, 'searchParams');
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
        // Yii::$app->user->can('createRole');
         
        $model = new AuthItem();
        $dataRequest['Permission'] = Yii::$app->request->getBodyParams();

        $model->name = $dataRequest['Permission']['name'];
        $model->type = $dataRequest['Permission']['type'];
        $model->description = $dataRequest['Permission']['description'];
        $model->ruleName = $dataRequest['Permission']['ruleName'];
        $model->data = $dataRequest['Permission']['data'];


        if($model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Record added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('updateRole');
        $dataRequest['Permission'] = Yii::$app->request->getBodyParams();
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
            // Yii::$app->user->can('PermissionRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Record restored successfully']);
        } else {
            // Yii::$app->user->can('PermissionDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Record deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = AuthItem::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}