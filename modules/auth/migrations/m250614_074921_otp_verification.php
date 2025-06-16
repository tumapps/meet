<?php

use yii\db\Migration;

/**
 * Class m250614_074921_otp_verification
 */
class m250614_074921_otp_verification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        $this->createTable('{{%otp_verification}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->bigInteger(),
            'code' => $this->integer(6)->notNull(),
            'type' => $this->string(20)->notNull(), // e.g., 'email_verification', 'password_reset'
            'expires_at' => $this->integer()->notNull(), // Unix timestamp
            'is_used' => $this->integer()->defaultValue(0),
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%otp_verification}}');
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
        echo "m250614_074921_otp_verification cannot be reverted.\n";

        return false;
    }
    */
}
