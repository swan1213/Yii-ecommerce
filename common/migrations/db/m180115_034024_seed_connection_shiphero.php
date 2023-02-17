<?php

use common\models\Connection;
use yii\db\Migration;

/**
 * Class m180115_034024_seed_connection_shiphero
 */
class m180115_034024_seed_connection_shiphero extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%connection_parent}}', [
            'id' => '28',
            'name' => 'Shiphero',
            'amount' => '0',
            'image_url' => 'img/marketplace_logos/elliot-shiphero.png'
        ]);

        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '28',
            'name' => 'Shiphero',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180115_034024_seed_connection_shiphero cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180115_034024_seed_connection_shiphero cannot be reverted.\n";

        return false;
    }
    */
}
