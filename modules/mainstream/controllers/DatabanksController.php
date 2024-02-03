<?php

namespace mainstream\controllers;

use Yii;
use iam\models\Databanks;
use mainstream\models\searches\DatabanksSearch;
/**
 * @OA\Tag(
 *     name="Databanks",
 *     description="Available endpoints for Databanks model"
 * )
 */
class DatabanksController extends \helpers\ApiController{

    public function actionIndex()
    {
        Yii::$app->user->can('databanks_list');
                $searchModel = new DatabanksSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'DatabanksSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('databanks_view');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate()
    {
        Yii::$app->user->can('databanks_create');
        $model = new Databanks();
        $model->loadDefaultValues();
        $dataRequest['Databanks'] = Yii::$app->request->getBodyParams();
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Databanks added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('databanks_update');
        $dataRequest['Databanks'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Databanks updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('databanks_restore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Databanks restored successfully']);
        } else {
            Yii::$app->user->can('databanks_delete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Databanks deleted successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    protected function findModel($databank_id)
    {
        if (($model = Databanks::findOne(['databank_id' => $databank_id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }
}
