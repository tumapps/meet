<?php

namespace auth\controllers;

use Yii;
use auth\models\StudentProfile;
use auth\models\VerificationDetail;
use auth\models\searches\VerificationDetailSearch;

/**
 * @OA\Tag(
 *     name="VerificationDetail",
 *     description="Available endpoints for VerificationDetail model"
 * )
 */
class VerificationDetailController extends \helpers\ApiController
{
    public $permissions = [
        'authVerification-detailList' => 'View VerificationDetail List',
        'authVerification-detailCreate' => 'Add VerificationDetail',
        'authVerification-detailUpdate' => 'Edit VerificationDetail',
        'authVerification-detailDelete' => 'Delete VerificationDetail',
        'authVerification-detailRestore' => 'Restore VerificationDetail',
    ];
    public function actionIndex()
    {
        Yii::$app->user->can('authVerification-detailList');
        $searchModel = new VerificationDetailSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'VerificationDetailSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('authVerification-detailView');
        return $this->payloadResponse($this->findModel($id));
    }

    // public function actionCreate()
    // {
    //     // Yii::$app->user->can('authVerification-detailCreate');
    //     $model = new VerificationDetail();
    //     $model->loadDefaultValues();
    //     $dataRequest['VerificationDetail'] = Yii::$app->request->getBodyParams();
    //     if ($model->load($dataRequest) && $model->save()) {
    //         return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'VerificationDetail added successfully']);
    //     }
    //     return $this->errorResponse($model->getErrors());
    // }

    public function actionCreate()
    {
        $model = new VerificationDetail();
        $model->loadDefaultValues();
        $dataRequest['VerificationDetail'] = Yii::$app->request->getBodyParams();

        if ($model->load($dataRequest) && $model->save()) {
            $studentId = $model->student_id;

            $studentProfile = StudentProfile::findOne(['std_id' => $studentId]);
            if ($studentProfile !== null) {
                $studentProfile->verification_status = 'verified';
                if (!$studentProfile->save()) {
                    return $this->errorResponse([
                        'studentProfile' => $studentProfile->getErrors()
                    ]);
                }
            } else {
                return $this->errorResponse([
                    'student_id' => ['Student not found.']
                ]);
            }

            return $this->payloadResponse($model, [
                'statusCode' => 201,
                'message' => 'VerificationDetail added and student marked as verified.'
            ]);
        }

        return $this->errorResponse($model->getErrors());
    }


    public function actionUpdate($id)
    {
        Yii::$app->user->can('authVerification-detailUpdate');
        $dataRequest['VerificationDetail'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'VerificationDetail updated successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('authVerification-detailRestore');
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'VerificationDetail restored successfully']);
        } else {
            Yii::$app->user->can('authVerification-detailDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'VerificationDetail deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = VerificationDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
