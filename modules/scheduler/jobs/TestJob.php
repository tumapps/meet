<?php
namespace scheduler\jobs;
use yii\base\BaseObject;


class TestJob extends BaseObject implements \yii\queue\JobInterface
{
    public $message;

    public function execute($queue)
    {
        echo $this->message . "\n";
    }
}