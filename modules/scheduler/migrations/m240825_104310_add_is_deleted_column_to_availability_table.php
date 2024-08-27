<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%availability}}`.
 */
class m240825_104310_add_is_deleted_column_to_availability_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('unavailable_slots', 'is_deleted', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('unavailable_slots', 'is_deleted');
    }
}
