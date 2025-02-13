<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Space;
use scheduler\models\searches\SpaceSearch;

/**
 * @OA\Tag(
 *     name="Space",
 *     description="Available endpoints for Space model"
 * )
 */
class SpaceController extends \helpers\ApiController
{

    public $permissions = [
        'schedulerSpaceList' => 'View Space List',
        'schedulerSpaceCreate' => 'Add Space',
        'schedulerSpaceUpdate' => 'Edit Space',
        'schedulerSpaceDelete' => 'Delete Space',
        'schedulerSpaceRestore' => 'Restore Space',
    ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerSpaceList');

        $currentUserId = Yii::$app->user->id;

        $roleFlags = $this->getRoleFlags($currentUserId);

        $searchModel = new SpaceSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'SpaceSearch');
        $dataProvider = $searchModel->search($search);


        if ($roleFlags['isUser']) {
            $dataProvider->query->andWhere(['OR', ['id' => $currentUserId], ['space_type' => Space::SPACE_TYPE_MANAGED]]);
        } elseif ($roleFlags['isSuperAdmin'] || $roleFlags['isRegistrar']) {
            $dataProvider->query->andWhere(['space_type' => Space::SPACE_TYPE_MANAGED]);
        }

        $spaceDetails = $dataProvider->getModels();

        foreach ($spaceDetails as $space) {
            $space->space_type = $space->space_type == Space::SPACE_TYPE_MANAGED ? 'Managed' : 'Unmanaged';
        }

        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    protected function getRoleFlags($userId)
    {
        $roles = Yii::$app->authManager->getRolesByUser($userId);
        return [
            'isSuperAdmin' => array_key_exists('su', $roles),
            'isRegistrar' => array_key_exists('registrar', $roles),
            'isUser' => array_key_exists('user', $roles),
        ];
    }


    public function actionView($id)
    {
        Yii::$app->user->can('schedulerSpaceList');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('registrar');
        $model = new Space();
        $model->loadDefaultValues();
        $dataRequest['Space'] = Yii::$app->request->getBodyParams();
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Space added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionLockSpace($id)
    {
        Yii::$app->user->can('registrar');

        $space = Space::findOne(['id' => $id]);

        if (!$space) {
            return $this->toastResponse(['statusCode' => 400, 'message' => 'Space not found.']);
        }

        $space->is_locked = !$space->is_locked;

        if ($space->save(false)) {
            return $this->toastResponse(['statusCode' => 200, 'message' => $space->is_locked ? 'Space has been locked.' : 'Space has been unlocked.']);
        } else {
            return $this->errorResponse($space->getErrors());
        }
    }

 

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('registrar');
        $dataRequest['Space'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);

        $roleFlags = $this->getRoleFlags(Yii::$app->user->id);
        $currentUserId = Yii::$app->user->id;

        if ($roleFlags['isUser']) {
            if ($model->id !== $currentUserId || $model->space_type !== Space::SPACE_TYPE_UNMANAGED) {
                return $this->errorResponse(['message' => ['You can only update your own unmanaged spaces']]);
            }
        } elseif ($roleFlags['isSuperAdmin'] || $roleFlags['isRegistrar']) {
            if (!($model->space_type === Space::SPACE_TYPE_MANAGED ||
                ($model->space_type === Space::SPACE_TYPE_UNMANAGED && $model->id === $currentUserId))) {
                return $this->errorResponse(['message' => ['You can only update managed spaces or your own unmanaged spaces']]);
            }
        }

        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'Space updated successfully']);
        }

        return $this->errorResponse($model->getErrors());
    }


    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerSpaceRestore');
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'Space restored successfully']);
        } else {
            Yii::$app->user->can('schedulerSpaceDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'Space deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = Space::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
