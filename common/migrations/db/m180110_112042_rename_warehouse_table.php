<?php

use yii\db\Migration;

/**
 * Class m180110_112042_rename_warehouse_table
 */
class m180110_112042_rename_warehouse_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameTable('{{%warehouse}}', '{{%fulfillment}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180110_112042_rename_warehouse_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180110_112042_rename_warehouse_table cannot be reverted.\n";

        return false;
    }
    */
}
