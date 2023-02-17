<?php

use yii\db\Migration;
use common\models\Connection;

/**
 * Class m180122_125326_seed_data_connection_unitied_kingdom
 */
class m180122_125326_seed_data_connection_united_kingdom extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%connection}}', ['name' => 'United Kingdom'], [
            'type_id' => Connection::CONNECTION_TYPE_CHANNEL,
            'parent_id' => 2,
            'name' => 'United-Kingdom'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180122_125326_seed_data_connection_unitied_kingdom cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180122_125326_seed_data_connection_unitied_kingdom cannot be reverted.\n";

        return false;
    }
    */
}
