<?php

use yii\db\Migration;

/**
 * Class m240911_074621_appointemnts_type_table
 */
class m240911_074621_appointemnts_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        $this->createTable('{{%appointment_type}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'is_deleted' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->insert('{{%appointment_type}}', [
            'type' => 'in person',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%appointment_type}}', [
            'type' => 'group',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%appointment_type}}', [
            'type' => 'meeting',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m240911_074621_appointemnts_type_table cannot be reverted.\n";

        // return false;
        $this->dropTable('{{%appointment_type}}');
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
        echo "m240911_074621_appointemnts_type_table cannot be reverted.\n";

        return false;
    }
    */
}
