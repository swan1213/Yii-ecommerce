<?php

use yii\db\Migration;

use common\models\Integration;

/**
 * Class m180121_160335_seed_data_integration_table
 */
class m180121_160335_seed_data_integration_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->delete('{{%connection}}', ['parent_id' => 19]);
        $this->delete('{{%connection_parent}}', ['id' => 19]);


        $this->insert('{{%integration}}', [
            'id' => 1,
            'type_id' => Integration::INTEGRATION_TYPE_ERP,
            'name' => 'NetSuite',
            'url' => '',
            'image_url' => '',
            'enabled' => Integration::INTEGRATION_ENABLED_YES
        ]);

        $this->insert('{{%integration}}', [
            'id' => 2,
            'type_id' => Integration::INTEGRATION_TYPE_POS,
            'name' => 'Square',
            'url' => '',
            'image_url' => '/img/marketplace_logos/square.png',
            'enabled' => Integration::INTEGRATION_ENABLED_YES
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180121_160335_seed_data_integration_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180121_160335_seed_data_integration_table cannot be reverted.\n";

        return false;
    }
    */
}
