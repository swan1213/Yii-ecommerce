<?php

use yii\db\Migration;

use common\models\Connection;
/**
 * Class m180130_120644_seed_data_update_connection_table
 */
class m180130_120644_seed_data_update_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%connection}}', ['name' => "Service Account"], ['type_id' => Connection::CONNECTION_TYPE_CHANNEL, 'name' => 'Service']);
        $this->update('{{%connection}}', ['name' => "Subscription Account"], ['type_id' => Connection::CONNECTION_TYPE_CHANNEL, 'name' => 'Subscription']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180130_120644_seed_data_update_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180130_120644_seed_data_update_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
