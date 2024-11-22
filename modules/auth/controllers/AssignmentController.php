<?php

namespace auth\controllers;

use Yii;
use auth\models\Assignment;
use auth\models\AuthItem;
use auth\models\User;
use yii\rbac\Item;
use auth\hooks\Configs;
use auth\models\searches\AssignmentSearch;


class AssignmentController extends \helpers\ApiController
{
    protected $type = Item::TYPE_ROLE;

    public function actionAssing() {}

    public function actionManageRole2($id)
    {
        // Yii::$app->user->can('manage-roles');
        $model = $this->findModel($id);
        return $model;
    }

    public function actionManageRole($id)
    {
        $authManager = Yii::$app->authManager;

        // Find the role by ID or name
        $role =  $authManager->getRole($id);

        if (!$role) {
            return $this->errorResponse('Role not found', 404);
        }

        // Fetch all roles and permissions
        $allRoles = $authManager->getRoles();
        $allPermissions = $authManager->getPermissions();

        // Fetch assigned roles and permissions for this role
        $assignedRoles = $authManager->getChildRoles($role->name);
        $assignedPermissions = $authManager->getPermissionsByRole($role->name);

        // Filter available roles and permissions
        $availableRoles = array_diff_key($allRoles, $assignedRoles);
        $availablePermissions = array_diff_key($allPermissions, $assignedPermissions);

        return $this->payloadResponse([
            'available' => [
                'roles' => $availableRoles,
                'permissions' => $availablePermissions,
            ],
            'assigned' => [
                'roles' => $assignedRoles,
                'permissions' => $assignedPermissions,
            ],
        ]);
    }


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

        if (empty($roleName) || empty($permissionName)) {
            return $this->errorResponse(['message' => ['Role and Permission name is required']]);
        }

        $auth = Yii::$app->authManager;

        $role = $auth->getRole($roleName);
        $permission = $auth->getPermission($permissionName);

        if (!$role || !$permission) {
            return $this->errorResponse(['message' => ['Role or Permission not found.']]);
        }

        $auth->addChild($role, $permission);

        return $this->toastResponse(['statusCode' => 202, 'message' => "Permission '{$permissionName}' assigned to role '{$roleName}'."]);
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

        if (empty($childRoleName) || empty($childRoleName)) {
            return $this->errorResponse(['message' => ['Child and Parent Role is required']]);
        }

        $auth = Yii::$app->authManager;

        $parentRole = $auth->getRole($parentRoleName);
        $childRole = $auth->getRole($childRoleName);

        if (!$parentRole || !$childRole) {
            return $this->errorResponse(['message' => ['Parent Role or Child Role not found.']]);
        }

        $auth->addChild($parentRole, $childRole);
        return $this->toastResponse(['statusCode' => 202, 'message' => "Role '{$childRoleName}' assigned to role '{$parentRoleName}'."]);
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

        if (empty($userId) || empty($roleName)) {
            return $this->errorResponse(['message' => ['User Id and Role is required']]);
        }

        if (!is_numeric($userId)) {
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
        return $this->toastResponse(['statusCode' => 202, 'message' => "Role '{$roleName}' assigned to user with ID '{$userId}'."]);
    }

    public function actionRemove($id)
    {
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();
        $items =$dataRequest['Assignment']['items'];

        $model = $this->findModel($id);
        $success = $model->removeChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Lists all roles, permissions, and their assignments
     * @return \yii\web\Response
     */
    public function actionListAssignments()
    {
        $auth = Yii::$app->authManager;

        $roles = $auth->getRoles();

        $assignments = [];

        foreach ($roles as $roleName => $role) {
            $rolePermissions = $auth->getPermissionsByRole($roleName);
            $userAssignments = $auth->getUserIdsByRole($roleName);

            $childRoles = $auth->getChildRoles($roleName);
            $childRoleNames = array_keys($childRoles);
            $childRoleNames = array_filter($childRoleNames, fn($childRole) => $childRole !== $roleName);

            $assignments[] = [
                'role' => $roleName,
                'description' => $role->description,
                'permissions' => array_keys($rolePermissions),
                'users' => $userAssignments,
                'child_roles' => $childRoleNames,
            ];
        }

        return $this->payloadResponse($assignments);
    }

    public function actionGetItems($id)
    {
        $model = new Assignment($id);

        $items = $model->getItems();

        return $this->payloadResponse($items);
    }

    public function actionSyncPermissions($toastResponse = true)
    {
        $auth = Yii::$app->authManager;
        $manager = \auth\hooks\Configs::authManager();
        $processedPermissions = [];
        $failedPermissions = [];

        foreach ((new AuthItem(null))->scanPermissions() as $key => $value) {
            // Check if permission already exists
            if ($manager->getPermission(['name' => $key])) {
                $failedPermissions[] = "Permission '{$key}' already exists.";
                continue;
            }

            $model = new AuthItem(null);
            $model->type = Item::TYPE_PERMISSION;
            $model->name = $key;
            $model->data = $value;

            // Validate model before saving
            if (!$model->validate()) {
                $failedPermissions[] = "Validation failed for '{$key}': " . implode(', ', $model->getFirstErrors());
                continue;
            }

            if ($model->save(false)) {
                $str = str_replace('-', ' ', $model->name);
                try {
                    $roleName = '';
                    if (!str_contains($model->name, '-')) {
                        $roleName = 'api';
                    } elseif (str_contains($str, 'create') || str_contains($str, 'update')) {
                        $roleName = 'editor';
                    } elseif (str_contains($str, 'list')) {
                        $roleName = 'viewer';
                    } else {
                        $roleName = 'su';
                    }

                    $role = $auth->getRole($roleName);
                    if ($role) {
                        (new AuthItem($role))->addChildren([$model->name]);
                        $processedPermissions[] = "Permission '{$model->name}' assigned to '{$roleName}' role.";
                    } else {
                        $failedPermissions[] = "Role '{$roleName}' does not exist.";
                    }
                } catch (\Exception $e) {
                    $failedPermissions[] = "Failed to assign permission '{$model->name}': " . $e->getMessage();
                }
            } else {
                $failedPermissions[] = "Failed to save permission '{$model->name}'.";
            }
        }

        // Generate response
        if ($toastResponse) {
            if (empty($failedPermissions)) {
                return $this->toastResponse([
                    'statusCode' => 200,
                    'message' => 'All permissions were successfully synced and assigned to roles.',
                ]);
            } else {
                return $this->toastResponse([
                    'statusCode' => 500,
                    'message' => 'Some permissions failed to sync. Details: ' . implode(', ', $failedPermissions),
                ]);
            }
        }
    }

    /**
     * Bulk assigns roles and permissions to a specific role
     * @return \yii\web\Response
     */
    public function actionBulkAssign($role)
    {
        $dataRequest['Assignment'] = Yii::$app->request->getBodyParams();

        // $parentRoleName = $dataRequest['Assignment']['role'];
        $items = $dataRequest['Assignment']['items']; 

        if (empty($items)) {
            return $this->errorResponse(['message' => ['Parent Role and Items are required']]);
        }

        $auth = Yii::$app->authManager;
        $parentRole = $auth->getRole($role);

        if (!$parentRole) {
            return $this->errorResponse(['message' => ["Parent Role '{$role}' not found."]]);
        }

        $errors = [];
        $success = [];

        foreach ($items as $itemName) {
            $role = $auth->getRole($itemName);
            $permission = $auth->getPermission($itemName);

            if ($role) {
                try {
                    $auth->addChild($parentRole, $role);
                    $success[] = "Role '{$itemName}' assigned to '{$role}'.";
                } catch (\Exception $e) {
                    $errors[] = "Failed to assign Role '{$itemName}' to '{$role}': " . $e->getMessage();
                }
            } elseif ($permission) {
                try {
                    $auth->addChild($parentRole, $permission);
                    $success[] = "Permission '{$itemName}' assigned to '{$role}'.";
                } catch (\Exception $e) {
                    $errors[] = "Failed to assign Permission '{$itemName}' to '{$role}': " . $e->getMessage();
                }
            } else {
                $errors[] = "Item '{$itemName}' not found as Role or Permission.";
            }
        }

        // Generate response
        if (empty($errors)) {
            return $this->toastResponse([
                'statusCode' => 200,
                'message' => implode(' ', $success),
            ]);
        }

        return $this->toastResponse([
            'statusCode' => 500,
            'message' => implode(' ', $errors),
        ]);
    }

    protected function findModel($id)
    {
        // $auth = Yii::$app->authManager;
        // $item =  $auth->getRole($id);

        // if ($item) {
        //     return new AuthItem($item);
        // } else {
        //     throw new \yii\web\NotFoundHttpException('Record not found.');
        // }

        $auth = Configs::authManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            return new AuthItem($item);
        } else {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }
    }
}
