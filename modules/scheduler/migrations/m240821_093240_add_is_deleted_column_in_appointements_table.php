<?php

use yii\db\Migration;

/**
 * Class m240821_093240_add_is_deleted_column_in_appointements_table
 */
class m240821_093240_add_is_deleted_column_in_appointements_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('appointments', 'is_deleted', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('appointments', 'is_deleted');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240821_093240_add_is_deleted_column_in_appointements_table cannot be reverted.\n";

        return false;
    }
    */
}
