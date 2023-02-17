<?php

use yii\db\Migration;

/**
 * Class m180219_011916_seed_woocommerce_connection_attribute_table
 */
class m180219_011916_seed_woocommerce_connection_attribute_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [
            ["slug", "", "", 5],
            ["date_created", "", "", 5],
            ["date_modified", "", "", 5],
            ["length", "", "", 5],
            ["width", "", "", 5],
            ["height", "", "", 5],
            ["categories", "", "", 5],
            ["images", "", "", 5],
            ["variations", "", "", 5],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180219_011916_seed_woocommerce_connection_attribute_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180219_011916_seed_woocommerce_connection_attribute_table cannot be reverted.\n";

        return false;
    }
    */
}
