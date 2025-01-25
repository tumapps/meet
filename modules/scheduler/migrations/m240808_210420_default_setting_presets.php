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
            'user_id' => $this->bigInteger()->unique(),
            // 'data' => $this->json()->notNull(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'slot_duration' => $this->integer()->notNull()->defaultValue(30), // minutes
            'booking_window' => $this->integer()->notNull()->defaultValue(12), // months
            'advanced_booking' => $this->integer()->notNull()->defaultValue(30), // minutes
            'reminder_time' => $this->integer()->notNull()->defaultValue(30), // minutes
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ],  $tableOptions);

        $this->createTable('{{%system_settings}}', [
            'id' => $this->primaryKey(),
            'app_name' => $this->string(255)->null(),
            'system_email' =>  $this->string(128)->notNull(),
            'category' => $this->string(20)->notNull()->defaultValue('GENERAL'),
            'email_scheme' => $this->string(128)->notNull(),
            'email_smtps' => $this->string(128)->notNull(),
            'email_port' => $this->integer()->notNull(),
            'email_encryption' => $this->string(10)->notNull(),
            'email_password' => $this->string(128)->notNull(),
            'description' => $this->text()->null(), // description column can be useful for storing metadata or explanations about each setting.
            'email_username' => $this->string(128)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'is_deleted' => $this->boolean()->defaultValue(false),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_settings}}');
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
