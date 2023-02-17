<?php

use yii\db\Migration;

/**
 * Class m180217_081108_add_columns_extra_fields_product_connection_table
 */
class m180217_081108_add_columns_extra_fields_product_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_connection}}', 'extra_fields', $this->text()->after('product_id'));
        $this->addColumn('{{%product_connection}}', 'json_data', $this->text()->after('extra_fields'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180217_081108_add_columns_extra_fields_product_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180217_081108_add_columns_extra_fields_product_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
