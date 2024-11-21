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

        $search = $this->queryParameters(Yii::$app->request->queryParams, 'AuthItemSearch');
        Yii::debug($search, 'searchParams');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
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
        $model->type = Item::TYPE_PERMISSION;
        $model->description = $dataRequest['Permission']['description'];
        $model->ruleName = $dataRequest['Permission']['ruleName'];
        $model->data = $dataRequest['Permission']['data'];


        if ($model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Record added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('updateRole');
        $dataRequest['Permission'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'Record updated successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionUpdatePermission($id)
    {
        $auth = Yii::$app->authManager;
        $dataRequest['Permission'] = Yii::$app->request->getBodyParams();

        $name = $dataRequest['Permission']['name'];
        $description = $dataRequest['Permission']['description'];
        $name = $dataRequest['Permission']['name'];
        $data = $dataRequest['Permission']['data'];



        $permission = $auth->getPermission($id);

        if (!$permission) {
            return $this->toastResponse([
                'statusCode' => 404,
                'message' => "Permission '{$id}' does not exist."
            ]);
        }

        $permission->name = $name;
        $permission->description = $description;
        $permission->data = $data;


        if ($auth->update($id, $permission)) {
            return $this->toastResponse([
                'statusCode' => 200,
                'message' => "Permission '{$id}' successfully updated to '{$name}'."
            ]);
        } else {
            return $this->toastResponse([
                'statusCode' => 500,
                'message' => "Failed to update permission '{$id}'."
            ]);
        }
    }


    // public function actionDelete($id)
    // {
    //     $model = $this->findModel($id);
    //     if ($model->is_deleted) {
    //         // Yii::$app->user->can('PermissionRestore');
    //         $model->restore();
    //         return $this->toastResponse(['statusCode' => 202, 'message' => 'Record restored successfully']);
    //     } else {
    //         // Yii::$app->user->can('PermissionDelete');
    //         $model->delete();
    //         return $this->toastResponse(['statusCode' => 202, 'message' => 'Record deleted successfully']);
    //     }
    //     return $this->errorResponse($model->getErrors());
    // }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $auth = Yii::$app->authManager;
        $auth->remove($model->item);
        return $this->toastResponse(['statusCode' => 202, 'message' => 'Record deleted successfully']);
    }

    protected function findModel($id)
    {
        if (($model = AuthItem::find(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
