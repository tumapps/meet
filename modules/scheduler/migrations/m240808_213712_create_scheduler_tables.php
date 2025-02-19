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
            'is_deleted' => $this->integer()->defaultValue(0),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);


        $this->createTable('{{%meeting_types}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'is_deleted' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);


        $this->createTable('{{%appointments}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger(),
            // 'appointment_type_id' => $this->integer(),
            'appointment_date' => $this->date()->notNull(),
            'start_time' => $this->time()->null(),
            'end_time' => $this->time()->null(),
            'contact_name' => $this->string(50),
            'email_address' => $this->string(128)->notNull(),
            'mobile_number' => $this->string(15),
            'subject' => $this->text()->null(),
            'appointment_type_id' => $this->string(), //personal or group
            'description' => $this->string(255)->null(),
            'status' => $this->integer()->notNull()->defaultValue(10),
            'priority' => $this->integer()->null(),
            'checked_in' => $this->integer()->defaultValue(0),
            'is_deleted' => $this->integer()->defaultValue(0),
            'reminder_sent_at' => $this->integer()->null()->defaultValue(null),
            'created_by' => $this->bigInteger(),
            'updated_by' => $this->bigInteger(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            // 'FOREIGN KEY ([[appointment_type_id]]) REFERENCES {{%meeting_types}} ([[id]])' .
            //     $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        //Add index to created_at column
        $this->createIndex(
            'idx-appointments-created_at',
            '{{%appointments}}',
            'created_at'
        );

        $this->createTable('{{%spaces}}', [
            'id' => $this->bigInteger()->unique(),
            'name' => $this->string()->notNull()->unique(),
            'space_type' => $this->integer()->defaultValue(1)->notNull(),
            'opening_time' => $this->time()->notNull(),
            'closing_time' => $this->time()->notNull(),
            'capacity' => $this->integer()->notNull(),
            'is_locked' => $this->boolean()->defaultValue(false),
            'location' => $this->string()->null(),
            'description' => $this->text()->null(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);


        $this->createTable('{{%space_availability}}', [
            'id' => $this->primaryKey(),
            'space_id' => $this->bigInteger()->notNull(),
            'appointment_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[space_id]]) REFERENCES {{%spaces}} ([[id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[appointment_id]]) REFERENCES {{%appointments}} ([[id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);


        $this->createTable('{{%events}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(10),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-events-start_date',
            '{{%events}}',
            'start_date'
        );

        // appointment attendees table
        $this->createTable('{{%appointment_attendees}}', [
            'id' => $this->primaryKey(),
            'appointment_id' => $this->integer()->notNull(),
            'attendee_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'start_time' => $this->time()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(11), //pending status
            'end_time' => $this->time()->notNull(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'is_removed' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[appointment_id]]) REFERENCES {{%appointments}} ([[id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        $this->createTable('{{%operation_reasons}}', [
            'id' => $this->primaryKey(),
            'entity_id' => $this->bigInteger()->notNull(),
            'type' => $this->string(),
            'entity_type' => $this->string()->notNull(),
            'reason' => $this->string(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'affected_user_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%appointment_files}}', [
            'id' => $this->primaryKey(),
            'appointment_id' => $this->integer()->notNull(),
            'file_url' => $this->string()->notNull(),
            'self_link' => $this->string()->notNull(),
            'file_name' => $this->string()->notNull(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[appointment_id]]) REFERENCES {{%appointments}} ([[id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%appointment_files}}');
        $this->dropTable('{{%appointment_attendees}}');
        $this->dropIndex('idx-events-start_date', '{{%events}}');
        $this->dropTable('{{%events}}');
        $this->dropTable('{{%space_availability}}');
        $this->dropTable('{{%spaces}}');
        $this->dropIndex('idx-appointments-created_at', '{{%appointments}}');
        $this->dropTable('{{%appointments}}');
        $this->dropTable('{{%meeting_types}}');
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
