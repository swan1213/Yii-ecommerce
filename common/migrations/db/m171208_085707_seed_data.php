<?php

use yii\db\Migration;
use common\models\Connection;
/**
 * Class m171208_085707_seed_data
 */
class m171208_085707_seed_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%connection}}', [
            'id' => '1',
            'type_id' => Connection::CONNECTION_TYPE_ELLIOT,
            'name' => 'Elliot',
            'url' => 'https://elliot.global',
            'amount' => '0',
            'image_url' => '/img/elliot-logo_new.png',
            'enabled' => 'Yes',
        ]);
        $this->insert('{{%connection}}', [
            'id' => '2',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'BigCommerce',
            'url' => 'https://www.bigcommerce.com',
            'amount' => '0',
            'image_url' => '/img/connections/bigcommerce.png',
            'enabled' => 'Yes',
        ]);
        $this->insert('{{%connection}}', [
            'id' => '3',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'Shopify',
            'url' => 'https://www.shopify.com',
            'amount' => '0',
            'image_url' => '/img/connections/shopify.png',
            'enabled' => 'Yes',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '4',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'ShopifyPlus',
            'url' => 'https://www.shopify.com/plus',
            'amount' => '0',
            'image_url' => '/img/connections/shopifyplus.png',
            'enabled' => 'Yes',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '5',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'WooCommerce',
            'url' => 'https://www.woocommerce.com',
            'amount' => '0',
            'image_url' => '/img/connections/woocommerce.png',
            'enabled' => 'Yes',
        ]);
        $this->insert('{{%connection}}', [
            'id' => '6',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'Magento',
            'url' => 'https://www.magento.com',
            'amount' => '0',
            'image_url' => '/img/connections/magento.png',
            'enabled' => 'Yes',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '7',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'Magento2',
            'url' => 'https://www.magento2.com',
            'amount' => '0',
            'image_url' => '/img/connections/magento2.png',
            'enabled' => 'Yes',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '8',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'VTEX',
            'url' => 'https://en.vtex.com',
            'amount' => '0',
            'image_url' => '/img/connections/vtex.png',
            'enabled' => 'Yes',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '9',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'Salesforce Commerce Cloud',
            'url' => 'https://www.salesforce.com',
            'amount' => '0',
            'image_url' => '/img/connections/salesforcecommercecloud.png',
            'enabled' => 'No',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '10',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'Oracle Commerce Cloud Cloud',
            'url' => 'https://cloud.oracle.com/commerce-cloud',
            'amount' => '0',
            'image_url' => '/img/connections/oraclecommercecloud.png',
            'enabled' => 'No',
        ]);

        $this->insert('{{%connection}}', [
            'id' => '11',
            'type_id' => Connection::CONNECTION_TYPE_STORE,
            'name' => 'NetSuite SuiteCommerce',
            'url' => 'http://www.netsuite.com/portal/home.shtml',
            'amount' => '0',
            'image_url' => '/img/connections/netsuite.png',
            'enabled' => 'No',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171208_085707_seed_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171208_085707_seed_data cannot be reverted.\n";

        return false;
    }
    */
}
