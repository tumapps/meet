<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\MeetingTypes;
use scheduler\models\searches\MeetingTypesSearch;

/**
 * @OA\Tag(
 *     name="MeetingTypes",
 *     description="Available endpoints for MeetingTypes model"
 * )
 */
class MeetingTypeController extends \helpers\ApiController
{
    public $permissions = [
        'schedulerMeeting-typeList' => 'View MeetingTypes List',
        'schedulerMeeting-typeCreate' => 'Add MeetingTypes',
        'schedulerMeeting-typeUpdate' => 'Edit MeetingTypes',
        'schedulerMeeting-typeDelete' => 'Delete MeetingTypes',
        'schedulerMeeting-typeRestore' => 'Restore MeetingTypes',
    ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerMeeting-typeList');
        $searchModel = new MeetingTypesSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'MeetingTypesSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerMeeting-typeList');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('schedulerMeeting-typeCreate');
        $model = new MeetingTypes();
        $model->loadDefaultValues();
        $dataRequest['MeetingTypes'] = Yii::$app->request->getBodyParams();
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'MeetingTypes added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerMeeting-typeUpdate');
        $dataRequest['MeetingTypes'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'MeetingTypes updated successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerMeeting-typeRestore');
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'MeetingTypes restored successfully']);
        } else {
            Yii::$app->user->can('schedulerMeeting-typeDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'MeetingTypes deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = MeetingTypes::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
