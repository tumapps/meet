<?php

use yii\db\Migration;

/**
 * Class m241031_090730_seeding_migration
 */
class m241031_090730_seeding_migration extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         
        $this->seedLevel();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m241031_090730_seeding_migration cannot be reverted.\n";

        return false;
    }

    private function seedLevel()
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->insert('{{%levels}}', [
                'name' => 'Level ' . $i,
                'code' => 'L' . $1 . '00',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241031_090730_seeding_migration cannot be reverted.\n";

        return false;
    }
    */
}
