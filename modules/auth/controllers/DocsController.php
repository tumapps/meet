<?php

namespace auth\controllers;

use Yii;
use yii\web\Response;


/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Available endpoints for Appointments model"
 * )
 */
class DocsController extends \helpers\ApiController
{
    public function actionDocs($mod = 'dashboard')
    {
        //$this->viewPath = '@swagger';
        return $this->render('docs', [
            'mod' => $mod
        ]);
    }
    public function actionJsonDocs($mod = 'dashboard')
    {
        $roothPath = Yii::getAlias('@webroot/');
        $openapi = \OpenApi\Generator::scan(
            [
                $roothPath . 'modules/' . $mod,
                $roothPath . 'providers/swagger/config',
            ]
        );
        Yii::$app->response->headers->set('Access-Control-Allow-Origin', ['*']);
        Yii::$app->response->headers->set('Content-Type', 'application/json');
        $file =  $roothPath . 'modules/dashboard/docs/' . $mod . '-openapi-json-resource.json';
        if (file_exists($file)) {
            unlink($file);
            file_put_contents($file, $openapi->toJson());
        } else {
            file_put_contents($file, $openapi->toJson());
        }
        Yii::$app->response->sendFile($file, false, ['mimeType' => 'json', 'inline' => true]);
        return true;
    }

    public function actionDoc()
    {
        return 'hello';
    }
}
