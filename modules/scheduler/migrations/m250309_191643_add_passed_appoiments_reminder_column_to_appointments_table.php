<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%appointments}}`.
 */
class m250309_191643_add_passed_appoiments_reminder_column_to_appointments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('appointments', 'appointment_completed_reminder_sent', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('appointments', 'appointment_completed_reminder_sent');
    }
}
