<?php
namespace helpers\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use Yii;

class DateFormatter extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'formatDates',
        ];
    }

    public function formatDates($event)
    {
        if ($this->owner->hasAttribute('created_at')) {
            $this->owner->created_at = Yii::$app->formatter->asDatetime($this->owner->created_at, 'php:d M Y, H:i');
        }

        if ($this->owner->hasAttribute('updated_at')) {
            $this->owner->updated_at = Yii::$app->formatter->asDatetime($this->owner->updated_at, 'php:d M Y, H:i');
        }

        if ($this->owner->hasAttribute('appointment_date')) {
            $this->owner->appointment_date = Yii::$app->formatter->asDatetime($this->owner->appointment_date, 'php:d M Y, H:i');
        }
    }
}
