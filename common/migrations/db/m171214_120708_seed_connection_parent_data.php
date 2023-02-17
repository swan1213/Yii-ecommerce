<?php

use yii\db\Migration;

/**
 * Class m171214_120708_seed_connection_parent_data
 */
class m171214_120708_seed_connection_parent_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->batchInsert('{{%connection_parent}}', ['id', 'name', 'image_url'], [
                ['1', 'AliExpress', 'img/marketplace_logos/elliot-aliexpress-marketplace.png'],
                ['2', 'Amazon', 'img/marketplace_logos/elliot-amazon-connection.png'],
                ['3', 'Facebook', 'img/marketplace_logos/elliot-facebook-marketplace.png'],
                ['4', 'Flipkart', 'img/marketplace_logos/elliot-flipkart-connection.png'],
                ['5', 'Google Shopping', 'img/marketplace_logos/elliot-google-shopping-connection.png'],
                ['6', 'Iguama', 'img/marketplace_logos/elliot-iguama-connection.png'],
                ['7', 'Instagram', 'img/marketplace_logos/elliot-instagram-marketplace.png'],
                ['8', 'Jet', 'img/marketplace_logos/elliot-jet-connection.png'],
                ['9', 'Jumia', 'img/marketplace_logos/elliot-jumia-connection.png'],
                ['10', 'Lazada', 'img/marketplace_logos/elliot-lazada-connection.png'],
                ['11', 'Linio', 'img/marketplace_logos/elliot-linio-connection.png'],
                ['12', 'MarkaVIP', 'img/marketplace_logos/elliot-markavip-connection.png'],
                ['13', 'MercadoLibre', 'img/marketplace_logos/elliot-mercado-libre-connection.png'],
                ['14', 'Pinterest', 'img/marketplace_logos/elliot-pinterest-marketplace.png'],
                ['15', 'Rakuten', 'img/marketplace_logos/elliot-rakuten-connection.png'],
                ['16', 'Shopee', 'img/marketplace_logos/shopee-logo.png'],
                ['17', 'SnapDeal', 'img/marketplace_logos/elliot-snapdeal-connection.png'],
                ['18', 'Souq', 'img/marketplace_logos/elliot-souq-connection.png'],
                ['19', 'Square', 'img/marketplace_logos/square.png'],
                ['20', 'TMall', 'img/marketplace_logos/elliot-tmall-connection.png'],
                ['21', 'Tokopedia', 'img/marketplace_logos/tokopedia-logo.png'],
                ['22', 'WeChat', 'img/marketplace_logos/elliot-wechat-connection.png'],
                ['23', 'Xiao Hong Shu', 'img/marketplace_logos/Xiao-Hong-Shu.png'],
                ['24', 'Zalando', 'img/marketplace_logos/elliot-zalando-connection.png'],
                ['25', 'Zalora', 'img/marketplace_logos/elliot-zalora-connection.png'],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171214_120708_seed_connection_parent_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171214_120708_seed_connection_parent_data cannot be reverted.\n";

        return false;
    }
    */
}
