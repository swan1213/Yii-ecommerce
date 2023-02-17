<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `order_connection`.
 */
class m171218_173642_drop_order_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('{{%order_connection}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m171218_173642_drop_order_connection_table cannot be reverted.\n";
        return false;
    }
}
