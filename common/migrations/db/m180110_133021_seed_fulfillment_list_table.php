<?php

use yii\db\Migration;

use common\models\FulfillmentList;
/**
 * Class m180110_133021_seed_fulfillment_list_table
 */
class m180110_133021_seed_fulfillment_list_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%fulfillment_list}}', ['name', 'link', 'image_link', 'type'], [
           ['3PL Central', '/tpl-central', '', FulfillmentList::FULFILLMENT_TYPE_SOFTWARE],
           ['SF Express', '/sfexpress', '', FulfillmentList::FULFILLMENT_TYPE_CARRIERS],
           ['ShipStation', '/fulfillment/shipstation', '', FulfillmentList::FULFILLMENT_TYPE_SOFTWARE],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180110_133021_seed_fulfillment_list_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180110_133021_seed_fulfillment_list_table cannot be reverted.\n";

        return false;
    }
    */
}
