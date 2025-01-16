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
class SpaceController extends \helpers\ApiController{
    public $permissions = [
        'schedulerSpaceList'=>'View Space List',
        'schedulerSpaceCreate'=>'Add Space',
        'schedulerSpaceUpdate'=>'Edit Space',
        'schedulerSpaceDelete'=>'Delete Space',
        'schedulerSpaceRestore'=>'Restore Space',
        ];
    public function actionIndex()
    {
        Yii::$app->user->can('schedulerSpaceList');
        $searchModel = new SpaceSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'SpaceSearch');
        $dataProvider = $searchModel->search($search);

        $spaceDetails = $dataProvider->getModels();

        foreach ($spaceDetails as $spaceDetail) {
            $spaceDetail->level_id = $spaceDetail->level ? $spaceDetail->level->name : 'Unknown Level';
        }

        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
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
        if($model->load($dataRequest) && $model->save()) {
            return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Space added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionLockSpace($id)
    {
        Yii::$app->user->can('registrar');

        $space = Space::findOne($id);
        
        if (!$space) {
            return $this->toastResponse(['statusCode'=>400,'message'=>'Space not found.']);
        }

        $space->is_locked = !$space->is_locked;

        if ($space->save(false)) {
             return $this->toastResponse(['statusCode'=>200,'message'=> $space->is_locked ? 'Space has been locked.' : 'Space has been unlocked.']);
        } else {
            return $this->errorResponse($space->getErrors()); 
        }
    }


    public function actionUpdate($id)
    {
        Yii::$app->user->can('registrar');
        $dataRequest['Space'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Space updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerSpaceRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Space restored successfully']);
        } else {
            Yii::$app->user->can('schedulerSpaceDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Space deleted successfully']);
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
