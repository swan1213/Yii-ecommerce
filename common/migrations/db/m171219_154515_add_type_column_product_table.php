<?php

use yii\db\Migration;

/**
 * Class m171219_154515_add_type_column_product_table
 */
class m171219_154515_add_type_column_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'type', $this->string(255)->after('name'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171219_154515_add_type_column_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171219_154515_add_type_column_product_table cannot be reverted.\n";

        return false;
    }
    */
}
