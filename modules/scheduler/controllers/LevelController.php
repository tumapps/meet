<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Level;
use scheduler\models\searches\LevelSearch;
/**
 * @OA\Tag(
 *     name="Level",
 *     description="Available endpoints for Level model"
 * )
 */
class LevelController extends \helpers\ApiController{
    public $permissions = [
        'schedulerLevelList'=>'View Level List',
        'schedulerLevelCreate'=>'Add Level',
        'schedulerLevelUpdate'=>'Edit Level',
        'schedulerLevelDelete'=>'Delete Level',
        'schedulerLevelRestore'=>'Restore Level',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('registrar');
        $searchModel = new LevelSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'LevelSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('registrar');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('registrar');
        $model = new Level();
        $model->loadDefaultValues();
        $dataRequest['Level'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Level added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('registrar');
        $dataRequest['Level'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Level updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerLevelRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Level restored successfully']);
        } else {
            Yii::$app->user->can('schedulerLevelDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Level deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($id)
    {
        if (($model = Level::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
