<?php

use yii\db\Migration;

/**
 * Class m250217_152015_meeting_history
 */
class m250217_152015_meeting_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meeting_history}}', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer(),
            'meeting_status' => $this->integer(),
            'space_id' => $this->bigInteger(),
            'new_space_id' => $this->bigInteger(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%meeting_history}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250217_152015_meeting_history cannot be reverted.\n";

        return false;
    }
    */
}
