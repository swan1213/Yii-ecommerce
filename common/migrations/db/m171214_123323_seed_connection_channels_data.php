<?php

use yii\db\Migration;

use common\models\Connection;
/**
 * Class m171214_123323_seed_connection_channels_data
 */
class m171214_123323_seed_connection_channels_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%connection}}', ['type_id','parent_id', 'name', 'url', 'enabled'],
            [
                [Connection::CONNECTION_TYPE_STORE, '0', 'Reaction', 'https://reactioncommerce.com/', Connection::CONNECTED_ENABLED_NO],
                [Connection::CONNECTION_TYPE_CHANNEL, '1', 'AliExpress', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'China', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'France', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'Germany', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'India', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'Italy', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'Japan', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'Mexico', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'Spain', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '2', 'United-Kingdom', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '3', 'Facebook', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '4', 'Flipkart', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '5', 'Google Shopping', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '6', 'Iguama', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '7', 'Instagram', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '8', 'Jet', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '9', 'Jumia', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Indonesia', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Malaysia', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Philippines', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Singapore', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Thailand', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '10', 'Vietnam', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '11', 'Linio', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '12', 'MarkaVIP', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '13', 'MercadoLibre', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '14', 'Pinterest', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '15', 'Rakuten', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '16', 'Shopee', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '17', 'SnapDeal', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'Bahrain', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'Kuwait', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'Oman', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'Qatar', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'Saudi Arabia', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '18', 'UAE', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '19', 'Square', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '20', 'TMall', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '21', 'Tokopedia', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '22', 'Service', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '22', 'Subscription', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '23', 'Xiao Hong Shu', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '24', 'Zalando', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_CHANNEL, '25', 'Zalora', '', Connection::CONNECTED_ENABLED_YES],

            ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171214_123323_seed_connection_channels_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171214_123323_seed_connection_channels_data cannot be reverted.\n";

        return false;
    }
    */
}
