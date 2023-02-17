<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `product_connection`.
 */
class m171218_180926_drop_product_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('{{%product_connection}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m171218_180926_drop_product_connection_table cannot be reverted.\n";
        return false;
    }
}
