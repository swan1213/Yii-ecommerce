<?php

use yii\db\Migration;

/**
 * Class m180121_032046_seed_data_connection_parent_image_table
 */
class m180121_032046_seed_data_connection_parent_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%connection_parent}}', ['image_url' => 'img/marketplace_logos/elliot-newegg-connection.png'], ['id' => 26]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180121_032046_seed_data_connection_parent_image_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180121_032046_seed_data_connection_parent_image_table cannot be reverted.\n";

        return false;
    }
    */
}
