<?php

use yii\db\Migration;
use common\models\Connection;
/**
 * Class m171221_140628_seed_data_amazon_us
 */
class m171221_140628_seed_data_amazon_us extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->insert('{{%connection}}', [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => '2',
            'name' => 'United States',
            'url' => '',
            'enabled' => Connection::CONNECTED_ENABLED_YES
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171221_140628_seed_data_amazon_us cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171221_140628_seed_data_amazon_us cannot be reverted.\n";

        return false;
    }
    */
}
