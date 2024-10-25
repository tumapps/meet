<?php

use yii\db\Migration;

/**
 * Class m240808_213712_create_scheduler_tables
 */
class m240808_213712_create_scheduler_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        $this->createTable('{{%unavailable_slots}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'start_time' => $this->time()->null(),
            'end_time' => $this->time()->null(),
            'is_full_day' => $this->boolean()->notNull()->defaultValue(false),
            'description' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            // 'is_deleted' => $this->boolean()->defaultValue(false),
            'is_deleted' => $this->integer()->defaultValue(0),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);


        $this->createTable('{{%appointments}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger(),
            'appointment_date' => $this->date()->notNull(),
            'start_time' => $this->time()->null(),
            'end_time' => $this->time()->null(),
            'contact_name' => $this->string(50),
            'email_address' => $this->string(128)->notNull(),
            'mobile_number' => $this->string(15),
            'subject' => $this->text()->null(),
            'appointment_type' => $this->string(), //personal or group
            'description' => $this->string(255)->null(),
            'status' => $this->integer()->notNull()->defaultValue(10),
            'cancellation_reason' => $this->string(255)->null(),
            'priority' => $this->integer()->notNull(),
            'checked_in' => $this->boolean()->defaultValue(false),
            // 'is_deleted' => $this->boolean()->defaultValue(false),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        //Add index to created_at column
        $this->createIndex(
            'idx-appointments-created_at',
            '{{%appointments}}',
            'created_at'
        );

        // $this->createTable('{{%spaces}}', [
        //     'id' => $this->primaryKey(),
        //     'is_deleted' => $this->integer()->defaultValue(0),
        //     'created_at' => $this->integer()->notNull(),
        //     'updated_at' => $this->integer()->notNull(),
        // ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {   
        // $this->dropTable('{{%spaces}}');
        $this->dropIndex('idx-appointments-created_at','{{%appointments}}');
        $this->dropTable('{{%appointments}}');
        $this->dropTable('{{%unavailable_slots}}');
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
        echo "m240808_213712_create_scheduler_tables cannot be reverted.\n";

        return false;
    }
    */
}
