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
        // return false;

        // $this->seedLevel();
        $this->insert('{{%meeting_types}}', [
            'type' => 'Appintment',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%meeting_types}}', [
            'type' => 'group',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%meeting_types}}', [
            'type' => 'in person',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        return false;
    }

    private function seedLevel()
    {
        // for ($i = 1; $i <= 4; $i++) {
        //     $this->insert('{{%levels}}', [
        //         'name' => 'Level ' . $i,
        //         'code' => 'L' . $i . '00',
        //         'created_at' => time(),
        //         'updated_at' => time(),
        //     ]);
        // }
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
