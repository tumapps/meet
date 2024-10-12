<?php

namespace cmd\controllers;

use Yii;
use yii\console\Controller;
use cmd\workers\EmailWorker;

class EmailWorkerController extends Controller
{
    /**
     * Runs the email worker.
     */
    public function actionRun()
    {
        $worker = new EmailWorker();
        $worker->run();
    }
}
