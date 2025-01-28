<?php

namespace auth\controllers;

use Yii;
use auth\models\AuthItem;
use auth\models\searches\AuthItemSearch;
use yii\rbac\Item;

class RoleController extends \helpers\ApiController
{
    public $type = Item::TYPE_ROLE;
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch();
        $searchModel->type = Item::TYPE_ROLE;

        $search = $this->queryParameters(Yii::$app->request->queryParams, 'AuthItemSearch');
        Yii::debug($search, 'searchParams');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionView($name)
    {
        // Yii::$app->user->can('');
        // $dataRequest['Role'] = Yii::$app->request->getBodyParams();
        // $name = $dataRequest['Role']['name'] ?? null;

        if (!$name) {
            return $this->errorResponse(['message' => ['Role name is required']]);
        }

        $auth = Yii::$app->authManager;

        $role = $auth->getRole($name);

        $assignments = [];

        if (!$role) {
            return $this->toastResponse(['statusCode' => 202, 'message' => "Role Name '{$name}' does not exists"]);
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

        if (!$role) {
            return $this->toastResponse(['statusCode' => 202, 'message' => "Role Name '{$name}' does not exists"]);
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
        $model->type = Item::TYPE_ROLE;
        $dataRequest['Role'] = Yii::$app->request->getBodyParams();

        // return $dataRequest['Role'];
        $model->name = $dataRequest['Role']['name'];
        $model->description = $dataRequest['Role']['description'];
        $model->ruleName = $dataRequest['Role']['ruleName'];
        $model->data = $dataRequest['Role']['data'];


        if ($model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Record added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }



    public function actionUpdate($id)
    {
        $dataRequest['Role'] = Yii::$app->request->getBodyParams();
        $auth = Yii::$app->authManager;
        $name = $dataRequest['Role']['name'];
        $data = $dataRequest['Role']['data'];
        $description = $dataRequest['Role']['description'];

        $role = $auth->getRole($id);

        if (!$role) {
            return $this->toastResponse([
                'statusCode' => 404,
                'message' => "Role '{$id}' does not exist."
            ]);
        }

        if ($name !== $role->name && $auth->getRole($name)) {
            return $this->toastResponse([
                'statusCode' => 409,
                'message' => "Role name '{$name}' already exists. Please choose a different name."
            ]);
        }

        // Update the role's properties
        $role->name = $name;
        $role->data = $data;
        $role->description = $description;

        $protectedRoles = ['user', 'su', 'api'];
        if (in_array($role->name, $protectedRoles)) {
            return $this->errorResponse(['message' => 'This is a system defined role, cannot be updated']);
        }

        // Save the updated role
        if ($auth->update($id, $role)) {
            return $this->toastResponse([
                'statusCode' => 200,
                'message' => "Role '{$id}' successfully updated to '{$name}'."
            ]);
        } else {
            return $this->toastResponse([
                'statusCode' => 500,
                'message' => "Failed to update role '{$name}'."
            ]);
        }
    }


    public function actionDelete($id)
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($id);

        if (!$role) {
            return $this->errorResponse(['message' => ['Role not found']]);
        }

        $model = $this->findModel($id);

        $protectedRoles = ['user', 'su', 'api'];
        
        if (in_array($model->name, $protectedRoles)) {
            return $this->errorResponse(['message' => ['This is a system defined Role and cannot be deleted.']]);
        }

        $assignedUsers = $this->getUsersByRole($model->name);

        if (!empty($assignedUsers)) {
            foreach ($assignedUsers as $userId) {
                $auth->revoke($model, $userId);

                $defaultRole = $auth->getRole('user');
                $auth->assign($defaultRole, $userId);
            }
        }

        $auth->remove($model->item);
        return $this->toastResponse(['statusCode' => 202, 'message' => 'Record deleted successfully']);
    }

    protected function findModel($id)
    {
        $auth = Yii::$app->authManager;
        // $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        $item =  $auth->getRole($id);

        if ($item) {
            return new AuthItem($item);
        } else {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }
    }

    protected function getUsersByRole($role)
    {
        return Yii::$app->db->createCommand("SELECT user_id FROM auth_assignments WHERE item_name = :role")
            ->bindValue(':role', $role)
            ->queryColumn();
    }
}
