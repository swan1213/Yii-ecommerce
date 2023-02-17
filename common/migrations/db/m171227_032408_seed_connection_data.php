<?php

use yii\db\Migration;

use common\models\Connection;

/**
 * Class m171227_032408_seed_connection_data
 */
class m171227_032408_seed_connection_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->insert('{{%connection_parent}}', [
            'id' => '26',
            'name' => 'Newegg',
            'amount' => '0',
            'image_url' => 'img/marketplace_logos/elliot-newegg.png'
        ]);

        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '26',
            'name' => 'United States',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);
        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '26',
            'name' => 'Canada',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);
        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '26',
            'name' => 'Business',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171227_032408_seed_connection_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171227_032408_seed_connection_data cannot be reverted.\n";

        return false;
    }
    */
}
