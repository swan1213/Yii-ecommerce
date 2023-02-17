<?php

use common\models\Connection;
use yii\db\Migration;

/**
 * Class m180207_110703_seed_square_connection_table
 */
class m180207_110703_seed_square_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->delete('{{%integration}}');

        $this->batchInsert('{{%connection_parent}}', ['id', 'name', 'image_url'], [
            ['19', 'Square', '/img/marketplace_logos/square.png'],
            ['27', 'NetSuite', '/img/marketplace_logos/netsuite.png'],
        ]);

        $this->batchInsert('{{%connection}}', ['type_id','parent_id', 'name', 'url', 'enabled'],
            [
                [Connection::CONNECTION_TYPE_POS, '19', 'Square', '', Connection::CONNECTED_ENABLED_YES],
                [Connection::CONNECTION_TYPE_ERP, '27', 'NetSuite', '', Connection::CONNECTED_ENABLED_YES],
            ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180207_110703_seed_square_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180207_110703_seed_square_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
