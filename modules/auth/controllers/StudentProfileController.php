<?php

namespace auth\controllers;

use Yii;
use auth\models\StudentProfile;
use auth\models\searches\StudentProfileSearch;

/**
 * @OA\Tag(
 *     name="StudentProfile",
 *     description="Available endpoints for StudentProfile model"
 * )
 */
class StudentProfileController extends \helpers\ApiController
{
    public $permissions = [
        'authStudent-profileList' => 'View StudentProfile List',
        'authStudent-profileCreate' => 'Add StudentProfile',
        'authStudent-profileUpdate' => 'Edit StudentProfile',
        'authStudent-profileDelete' => 'Delete StudentProfile',
        'authStudent-profileRestore' => 'Restore StudentProfile',
    ];
    public function actionIndex()
    {
        // Yii::$app->user->can('authStudent-profileList');
        $searchModel = new StudentProfileSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'StudentProfileSearch');
        $dataProvider = $searchModel->search($search);
        foreach ($dataProvider->models as $model) {
            $model->calculateFeePercentages();
        }

        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    // public function actionView($id)
    // {
    //     // Yii::$app->user->can('authStudent-profileView');
    //     return $this->payloadResponse($this->findModel($id));
    // }
    public function actionView($id)
    {
        // Yii::$app->user->can('authStudent-profileView');
        $model = $this->findModel($id);
        $model->calculateFeePercentages();
        return $this->payloadResponse($model);
    }




    public function actionCreate()
    {
        // Yii::$app->user->can('authStudent-profileCreate');
        $model = new StudentProfile();
        $model->loadDefaultValues();
        $dataRequest['StudentProfile'] = Yii::$app->request->getBodyParams();
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'StudentProfile added successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('authStudent-profileUpdate');
        $dataRequest['StudentProfile'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'StudentProfile updated successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('authStudent-profileRestore');
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'StudentProfile restored successfully']);
        } else {
            Yii::$app->user->can('authStudent-profileDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'StudentProfile deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($std_id)
    {
        if (($model = StudentProfile::findOne(['std_id' => $std_id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
