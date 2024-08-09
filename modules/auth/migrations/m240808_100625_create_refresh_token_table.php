<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%refresh_token}}`.
 */
class m240808_100625_create_refresh_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = null;

        $this->createTable('{{%refresh_tokens}}', [
            'token_id' => $this->bigPrimaryKey(),
            'user_id' => $this->bigInteger(),
            'token' => $this->text()->unique()->notNull(),
            'ip' => $this->string(32)->notNull()->defaultValue('127.0.0.1'),
            'user_agent' => $this->string()->notNull(),
            'data' => $this->text()->notNull()->defaultValue('-- no data --'),
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%refresh_tokens}}');
    }
    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
