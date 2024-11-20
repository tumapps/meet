<?php

namespace auth\controllers;

use Yii;
use auth\models\AuthItem;
use auth\models\searches\AuthItemSearch;
use yii\rbac\Item;

class RoleController extends \helpers\ApiController
{
	public function actionIndex()
    {
        $searchModel = new AuthItemSearch();
        $searchModel->type = Item::TYPE_ROLE;

        $search = $this->queryParameters(Yii::$app->request->queryParams,'AuthItemSearch');
        Yii::debug($search, 'searchParams');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

	public function actionView()
    {
        // Yii::$app->user->can('');
        // return $this->payloadResponse($this->findModel($id));
        $dataRequest['Role'] = Yii::$app->request->getBodyParams();
        $name = $dataRequest['Role']['name'] ?? null;

        if(!$name) {
            return $this->errorResponse(['message' => ['Role name is required']]);
        }

        $auth = Yii::$app->authManager;

        $role = $auth->getRole($name);

        $assignments = [];

        if(!$role){
            return $this->toastResponse(['statusCode'=>202,'message'=>"Role Name '{$name}' does not exists"]);
        }

        $rolePermissions = $auth->getPermissionsByRole($name);
        $userAssignments = $auth->getUserIdsByRole($name);

        $childRoles = $auth->getChildRoles($name);
        $childRoleNames = array_keys($childRoles);
        $childRoleNames = array_filter($childRoleNames, fn($childRole) => $childRole !== $name);

        $assignments[] = [
            'role' => $role,
            'description' => $role->description,
            'permissions' => array_keys($rolePermissions),
            'users' => $userAssignments,
            'child_roles' => $childRoleNames,
        ];

        return $this->payloadResponse($assignments);
    }

    public function actionGetRole($name)
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($name);

        $assignments = [];

        if(!$role){
            return $this->toastResponse(['statusCode'=>202,'message'=>"Role Name '{$name}' does not exists"]);
        }

        $rolePermissions = $auth->getPermissionsByRole($name);
        $userAssignments = $auth->getUserIdsByRole($name);

        $childRoles = $auth->getChildRoles($name);
        $childRoleNames = array_keys($childRoles);
        $childRoleNames = array_filter($childRoleNames, fn($childRole) => $childRole !== $name);

        $assignments[] = [
            'role' => $role,
            'description' => $role->description,
            'permissions' => array_keys($rolePermissions),
            'users' => $userAssignments,
            'child_roles' => $childRoleNames,
        ];

        return $this->payloadResponse($assignments);
    }

	public function actionCreate()
    {
        // Yii::$app->user->can('createRole');
        $model = new AuthItem();
        $dataRequest['Role'] = Yii::$app->request->getBodyParams();

        $model->name = $dataRequest['Role']['name'];
        $model->type = $dataRequest['Role']['type'];
        $model->description = $dataRequest['Role']['description'];
        $model->ruleName = $dataRequest['Role']['ruleName'];
        $model->data = $dataRequest['Role']['data'];


        if($model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Record added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('updateRole');
        $dataRequest['Role'] = Yii::$app->request->getBodyParams();
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
        if (($model = AuthItem::find($id)) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}