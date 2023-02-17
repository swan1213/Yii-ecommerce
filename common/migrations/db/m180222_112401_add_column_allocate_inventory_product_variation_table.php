<?php

use yii\db\Migration;

/**
 * Class m180222_112401_add_column_allocate_inventory_product_variation_table
 */
class m180222_112401_add_column_allocate_inventory_product_variation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_variation}}', 'allocate_inventory', $this->integer()->defaultValue(0)->after('weight_value'));
        $this->addColumn('{{%product_variation}}', 'allocate_percent', $this->float()->defaultValue(100)->after('allocate_inventory'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180222_112401_add_column_allocate_inventory_product_variation_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180222_112401_add_column_allocate_inventory_product_variation_table cannot be reverted.\n";

        return false;
    }
    */
}
