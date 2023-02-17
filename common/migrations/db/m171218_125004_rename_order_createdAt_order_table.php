<?php

use yii\db\Migration;

/**
 * Class m171218_125004_rename_order_createdAt_order_table
 */
class m171218_125004_rename_order_createdAt_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('{{%order}}', 'order_createdAt', 'order_date');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_125004_rename_order_createdAt_order_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_125004_rename_order_createdAt_order_table cannot be reverted.\n";

        return false;
    }
    */
}
