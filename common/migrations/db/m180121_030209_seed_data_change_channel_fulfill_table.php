<?php

use yii\db\Migration;
use common\models\FulfillmentList;
/**
 * Class m180121_030209_seed_data_change_channel_fulfill_table
 */
class m180121_030209_seed_data_change_channel_fulfill_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->delete('{{%connection}}', ['parent_id' => 27]);//Shiphawk
        $this->delete('{{%connection_parent}}', ['id' => 27]);//Shiphawk

        $this->delete('{{%connection}}', ['parent_id' => 28]);//Shiphero
        $this->delete('{{%connection_parent}}', ['id' => 28]);//Shiphero

        $this->batchInsert('{{%fulfillment_list}}', ['name', 'link', 'image_link', 'type'], [
            ['Shiphawk', '/fulfillment/shiphawk', 'img/marketplace_logos/elliot-shiphawk.png', FulfillmentList::FULFILLMENT_TYPE_SOFTWARE],
            ['Shiphero', '/fulfillment/shiphero', 'img/marketplace_logos/elliot-shiphero.png', FulfillmentList::FULFILLMENT_TYPE_SOFTWARE],
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180121_030209_seed_data_change_channel_fulfill_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180121_030209_seed_data_change_channel_fulfill_table cannot be reverted.\n";

        return false;
    }
    */
}
