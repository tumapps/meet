<?php

use yii\db\Migration;

/**
 * Class m240808_210420_default_setting_presets
 */
class m240808_210420_default_setting_presets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'slot_duration' => $this->integer()->notNull()->defaultValue(30),
            'booking_window' => $this->integer()->notNull()->defaultValue(12),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ],  $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropTable('{{%settings}}');
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240808_210420_default_setting_presets cannot be reverted.\n";

        return false;
    }
    */
}
