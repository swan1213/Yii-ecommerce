<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `order_fulfillment`.
 */
class m180113_161603_drop_order_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {

        $this->dropForeignKey('fk_order_fulfillment_user_id', '{{%order_fulfillment}}');
        $this->dropForeignKey('fk_order_fulfillment_order_id', '{{%order_fulfillment}}');

        $this->dropTable('{{%order_fulfillment}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m180113_161603_drop_order_fulfillment_table cannot be reverted.\n";
        return false;
    }
}
