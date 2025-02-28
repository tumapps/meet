<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\ManagedUsers;
use scheduler\models\searches\ManagedUsersSearch;
use auth\models\User;
use auth\models\searches\UserSearch;

/**
 * @OA\Tag(
 *     name="ManagedUsers",
 *     description="Available endpoints for ManagedUsers model"
 * )
 */
class ManagedUsersController extends \helpers\ApiController
{
    public $permissions = [
        'schedulerManaged-usersList' => 'View ManagedUsers List',
        'schedulerManaged-usersCreate' => 'Add ManagedUsers',
        'schedulerManaged-usersUpdate' => 'Edit ManagedUsers',
        'schedulerManaged-usersDelete' => 'Delete ManagedUsers',
        'schedulerManaged-usersRestore' => 'Restore ManagedUsers',
    ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerManaged-usersList');
        $searchModel = new ManagedUsersSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'ManagedUsersSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerManaged-usersView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerManaged-usersCreate');
        $model = new ManagedUsers();

        $model->loadDefaultValues();
        $dataRequest['ManagedUsers'] = Yii::$app->request->getBodyParams();
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'User assigned to secretary successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionManagedUsers($id)
    {
        $searchModel = new UserSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'UserSearch');
        $dataProvider = $searchModel->search($search);

        // $users = $dataProvider->getModels();
        $users = User::find()
            ->select(['users.user_id', 'username', 'profiles.first_name', 'profiles.last_name'])
            ->joinWith('profile')
            ->asArray()
            ->all();

        // return $users;
        $authManager = Yii::$app->authManager;

        $secretaryId = Yii::$app->request->get('id');

        // Fetch users specifically assigned to the given secretary
        $secretaryAssignedUserIds = ManagedUsers::find()
            ->select('user_id')
            ->where(['secretary_id' => $secretaryId])
            ->column();

        $assignedUsers = [];
        $availableUsers = [];


        foreach ($users as $user) {
            $roles = array_keys($authManager->getRolesByUser($user['user_id']));

            // Skip users with "secretary" or "admin" roles
            if (in_array('secretary', $roles) || in_array('su', $roles)) {
                continue;
            }

            $userData = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'fullname' => $user['first_name'] . ' ' . $user['last_name'],
            ];

            // Check assignment status
            if (in_array($user['user_id'], $secretaryAssignedUserIds)) {
                $assignedUsers[] = $userData;
            } else {
                $availableUsers[] = $userData;
            }
        }

        return $this->payloadResponse([
            'assigned' => $assignedUsers,
            'available' => $availableUsers
        ]);
    }

    public function actionReassign($secretary_id, $user_id)
    {
        Yii::$app->user->can('schedulerManaged-usersUpdate');

        $managedUser = ManagedUsers::findOne(['user_id' => $user_id, 'secretary_id' => $secretary_id]);

        if (!$managedUser) {
            return $this->toastResponse(['statusCode' => 202, 'message' => 'This secretary does not manage the specified user.']);
        }

        $model = $this->findModel($managedUser->id);
        if (!$model) {
            return $this->errorResponse(['message' => ['Record not found.']]);
        }
        if ($this->removeAssignedUser($user_id, $secretary_id)) {
            return $this->toastResponse(['statusCode' => 202, 'message' => 'User successfully unassigned and permanently deleted.']);
        } else {
            return $this->toastResponse(['statusCode' => 500, 'message' => 'Failed to remove assigned user.']);
        }
    }

    private function removeAssignedUser($user_id, $secretary_id)
    {
        $sql = "DELETE FROM managed_users WHERE user_id = :user_id AND secretary_id = :secretary_id";

        $deletedRows = Yii::$app->db->createCommand($sql)
            ->bindValue(':user_id', $user_id)
            ->bindValue(':secretary_id', $secretary_id)
            ->execute();

        return $deletedRows > 0;

        // $getUser = ManagedUsers::findOne(['user_id' => $user_id, 'secretary_id' => $secretary_id]);
        // $model = $this->findModel($getUser->idate);
        // $model->forceDelete();
    }




    public function actionUpdate($id)
    {
        return $this->payloadResponse(['statusCode' => 202, 'message' => "No implementation for this endpoint:  {$id}"]);

        // Yii::$app->user->can('schedulerManaged-usersUpdate');
        // $dataRequest['ManagedUsers'] = Yii::$app->request->getBodyParams();
        // $model = $this->findModel($id);
        // if ($model->load($dataRequest) && $model->save()) {
        //     return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'ManagedUsers updated successfully']);
        // }
        // return $this->errorResponse($model->getErrors());
    }

    // public function actionDelete($id)
    // {
    //     $model = $this->findModel($id);
    //     if ($model->is_deleted) {
    //         Yii::$app->user->can('schedulerManaged-usersRestore');
    //         $model->restore();
    //         return $this->toastResponse(['statusCode' => 202, 'message' => 'ManagedUsers restored successfully']);
    //     } else {
    //         Yii::$app->user->can('schedulerManaged-usersDelete');
    //         $model->delete();
    //         return $this->toastResponse(['statusCode' => 202, 'message' => 'ManagedUsers deleted successfully']);
    //     }
    //     return $this->errorResponse($model->getErrors());
    // }

    protected function findModel($id)
    {
        if (($model = ManagedUsers::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
