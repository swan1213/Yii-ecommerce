<?php

use yii\db\Migration;

/**
 * Class m171208_054553_seed_data
 */
class m171208_054553_seed_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%trial_period}}', [
            'trial_days' => '7'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171208_054553_seed_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171208_054553_seed_data cannot be reverted.\n";

        return false;
    }
    */
}
