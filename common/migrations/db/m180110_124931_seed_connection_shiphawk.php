<?php

use yii\db\Migration;

use common\models\Connection;
/**
 * Class m180110_124931_seed_connection_shiphawk
 */
class m180110_124931_seed_connection_shiphawk extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%connection_parent}}', [
            'id' => '27',
            'name' => 'Shiphawk',
            'amount' => '0',
            'image_url' => 'img/marketplace_logos/elliot-shiphawk.png'
        ]);

        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '27',
            'name' => 'Shiphawk',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180110_124931_seed_connection_shiphawk cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180110_124931_seed_connection_shiphawk cannot be reverted.\n";

        return false;
    }
    */
}
