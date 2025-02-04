<?php
namespace helpers\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

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
        if ($this->owner->hasAttribute('appointment_date')) {
            $this->owner->appointment_date = date('Y-m-d', strtotime($this->owner->appointment_date));
        }

        if ($this->owner->hasAttribute('start_time')) {
            $this->owner->start_time = Date('H:i', strtotime($this->owner->start_time));
        }
        
        if ($this->owner->hasAttribute('end_time')) {
            $this->owner->end_time = Date('H:i', strtotime($this->owner->end_time));
        }
        
    }
}
