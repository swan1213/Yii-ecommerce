<?php

use yii\db\Migration;

/**
 * Class m171217_023443_seed_connection_data
 */
class m171217_023443_seed_connection_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%connection}}', [
            'image_url' => '/img/connections/reaction.png',
            'enabled' => 'Yes',
        ], ['id' => '12']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171217_023443_seed_connection_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171217_023443_seed_connection_data cannot be reverted.\n";

        return false;
    }
    */
}
