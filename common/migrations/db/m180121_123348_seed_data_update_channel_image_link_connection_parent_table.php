<?php

use yii\db\Migration;

/**
 * Class m180121_123348_seed_data_update_channel_image_link_connection_parent_table
 */
class m180121_123348_seed_data_update_channel_image_link_connection_parent_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-aliexpress-marketplace.png'], ['id' => 1]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-amazon-connection.png'], ['id' => 2]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-facebook-marketplace.png'], ['id' => 3]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-flipkart-connection.png'], ['id' => 4]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-google-shopping-connection.png'], ['id' => 5]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-iguama-connection.png'], ['id' => 6]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-instagram-marketplace.png'], ['id' => 7]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-jet-connection.png'], ['id' => 8]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-jumia-connection.png'], ['id' => 9]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-lazada-connection.png'], ['id' => 10]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-linio-connection.png'], ['id' => 11]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-markavip-connection.png'], ['id' => 12]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-mercado-libre-connection.png'], ['id' => 13]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-pinterest-marketplace.png'], ['id' => 14]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-rakuten-connection.png'], ['id' => 15]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/shopee-logo.png'], ['id' => 16]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-snapdeal-connection.png'], ['id' => 17]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-souq-connection.png'], ['id' => 18]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/square.png'], ['id' => 19]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-tmall-connection.png'], ['id' => 20]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/tokopedia-logo.png'], ['id' => 21]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-wechat-connection.png'], ['id' => 22]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/Xiao-Hong-Shu.png'], ['id' => 23]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-zalando-connection.png'], ['id' => 24]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-zalora-connection.png'], ['id' => 25]);
        $this->update('{{%connection_parent}}', ['image_url' => '/img/marketplace_logos/elliot-newegg-connection.png'], ['id' => 26]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180121_123348_seed_data_update_channel_image_link_connection_parent_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180121_123348_seed_data_update_channel_image_link_connection_parent_table cannot be reverted.\n";

        return false;
    }
    */
}
