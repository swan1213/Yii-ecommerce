<?php

use common\models\Integration;
use yii\db\Migration;

/**
 * Class m180206_082534_seed_smartling_integration_table
 */
class m180206_082534_seed_smartling_integration_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%integration}}', [
            'id' => 3,
            'type_id' => Integration::INTEGRATION_TYPE_TRANSLATE,
            'name' => 'Smartling',
            'url' => '',
            'image_url' => '',
            'enabled' => Integration::INTEGRATION_ENABLED_YES
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_082534_seed_smartling_integration_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180206_082534_seed_smartling_integration_table cannot be reverted.\n";

        return false;
    }
    */
}
