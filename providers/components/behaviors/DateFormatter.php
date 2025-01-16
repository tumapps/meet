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
        // if ($this->owner->hasAttribute('created_at')) {
        //     $this->owner->created_at = Date('d M Y, H:i', $this->owner->created_at);
        //     // $this->owner->created_at = Yii::$app->formatter->asRelativeTime($this->owner->created_at);
        //     // $this->owner->created_at = Yii::$app->formatter->asDateTime($this->owner->created_at, 'php: M Y, H:i');

        // }

        // if ($this->owner->hasAttribute('updated_at')) {
        //     $this->owner->updated_at = Date('M Y, H:i', $this->owner->updated_at);
        // }

        if ($this->owner->hasAttribute('appointment_date')) {
            $this->owner->appointment_date = Date('d M Y', strtotime($this->owner->appointment_date));
        }

        if ($this->owner->hasAttribute('start_time')) {
            $this->owner->start_time = Date('H:i', strtotime($this->owner->start_time));
        }
        
        if ($this->owner->hasAttribute('end_time')) {
            $this->owner->end_time = Date('H:i', strtotime($this->owner->end_time));
        }
        
    }
}
