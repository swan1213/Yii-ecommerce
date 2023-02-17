<?php

use yii\db\Migration;

/**
 * Class m180124_143519_seed_delete_attribution_table
 */
class m180124_143519_seed_delete_attribution_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->delete('{{%attribution}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180124_143519_seed_delete_attribution_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_143519_seed_delete_attribution_table cannot be reverted.\n";

        return false;
    }
    */
}
