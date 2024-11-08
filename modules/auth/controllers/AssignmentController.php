<?php

namespace auth\controllers;

use Yii;
use auth\models\Assignment;
use auth\models\User;
use auth\models\searches\AssignmentSearch;


class AssignmentController extends \helpers\ApiController {

	/**
    * Assigns a permission to a role
    * @param string $roleName
    * @param string $permissionName
    * @return \yii\web\Response
    */
    public function actionPermissionToRole()
    {
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();

        $roleName = $dataRequest['Assignment']['role'];
        $permissionName = $dataRequest['Assignment']['permission'];

        if(empty($roleName) || empty($permissionName)) {
            return $this->errorResponse(['message' => ['Role and Permission name is required']]);
        }

        $auth = Yii::$app->authManager;
        
        $role = $auth->getRole($roleName);
        $permission = $auth->getPermission($permissionName);
        
        if (!$role || !$permission) {
            return $this->errorResponse(['message' => ['Role or Permission not found.']]);
        }
        
        $auth->addChild($role, $permission);
         
        return $this->toastResponse(['statusCode'=>202,'message'=>"Permission '{$permissionName}' assigned to role '{$roleName}'."]);
    }

    /**
     * Assigns a role to another role (parent role and child role)
     * @param string $parentRoleName
     * @param string $childRoleName
     * @return \yii\web\Response
     */
    public function actionRoleToRole()
    {
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();

        $childRoleName = $dataRequest['Assignment']['child_role'];
        $parentRoleName = $dataRequest['Assignment']['parent_role'];

        if(empty($childRoleName) || empty($childRoleName)) {
            return $this->errorResponse(['message' => ['Child and Parent Role is required']]);
        }

        $auth = Yii::$app->authManager;

        $parentRole = $auth->getRole($parentRoleName);
        $childRole = $auth->getRole($childRoleName);

        if (!$parentRole || !$childRole) {
            return $this->errorResponse(['message' => ['Parent Role or Child Role not found.']]);
        }

        $auth->addChild($parentRole, $childRole);
        return $this->toastResponse(['statusCode'=>202,'message'=>"Role '{$childRoleName}' assigned to role '{$parentRoleName}'."]);
    }

    /**
     * Assigns a role to a user
     * @param string $userId
     * @param string $roleName
     * @return \yii\web\Response
     */
    public function actionRoleToUser()
    {
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();

        $userId = $dataRequest['Assignment']['user_id'];
        $roleName = $dataRequest['Assignment']['role_name'];

        if(empty($userId) || empty($roleName)) {
            return $this->errorResponse(['message' => ['User Id and Role is required']]);
        }

        if(!is_numeric($userId)){
           return $this->errorResponse(['message' => ['User id must be an integer']]); 
        }

        $auth = Yii::$app->authManager;

        $role = $auth->getRole($roleName);

        if (!$role) {
            return $this->errorResponse(['message' => ['Role not found.']]);
        }

        $id = User::findOne(['user_id' => (int)$userId]);

        if (!$id) {
            return $this->errorResponse(['message' => ['Provided user id does not exists']]);
        }

        $auth->assign($role, $userId);
        return $this->toastResponse(['statusCode'=>202,'message'=>"Role '{$roleName}' assigned to user with ID '{$userId}'."]);
    }

    /**
     * Lists all roles, permissions, and their assignments
     * @return \yii\web\Response
     */
    public function actionListAssignments()
    {
        $auth = Yii::$app->authManager;

        $roles = $auth->getRoles();
        $permissions = $auth->getPermissions();

        $assignments = [
            'roles' => array_keys($roles),
            'permissions' => array_keys($permissions),
        ];

        return $this->payloadResponse($assignments);
    }
     
}