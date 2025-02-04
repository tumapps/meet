<?php

namespace  helpers;


class ActiveRecord extends \yii\db\ActiveRecord
{
	use \helpers\traits\Keygen;
	use \helpers\traits\Status;
	public $recordStatus;
	public function behaviors()
	{
		if ($this->tableName() == "{{%active_record}}") {
			return parent::behaviors();
		} else {
			$behaviors = [
				\helpers\behaviors\Delete::class,
				\helpers\behaviors\Creator::class,
				// \helpers\behaviors\DateFormatter::class,
			];

			if ($this->hasAttribute('created_at') && $this->hasAttribute('updated_at')) {
				$behaviors[] = \yii\behaviors\TimestampBehavior::class;
			}

			return array_merge(parent::behaviors(), $behaviors);
		}
	}
	public function afterFind()
	{
		if ($this->hasAttribute('status')) {
			$status = ($this->is_deleted == 1) ? $this->is_deleted : $this->status;
			$this->recordStatus = self::badge($status);
		}

		if ($this->owner->hasAttribute('appointment_date')) {
			$this->owner->appointment_date = date('Y-m-d', strtotime($this->owner->appointment_date));
		}

		if ($this->owner->hasAttribute('start_time')) {
			$this->owner->start_time = Date('H:i', strtotime($this->owner->start_time));
		}

		if ($this->owner->hasAttribute('end_time')) {
			$this->owner->end_time = Date('H:i', strtotime($this->owner->end_time));
		}

		return parent::afterFind();
	}
	public function attributeLabels()
	{
		return [
			'recordStatus' => 'Status',
		];
	}
}
