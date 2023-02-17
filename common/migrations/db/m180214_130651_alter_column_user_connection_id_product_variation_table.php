<?php

use yii\db\Migration;

/**
 * Class m180214_130651_alter_column_user_connection_id_product_variation_table
 */
class m180214_130651_alter_column_user_connection_id_product_variation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_variation}}', 'user_connection_id', $this->bigInteger()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180214_130651_alter_column_user_connection_id_product_variation_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_130651_alter_column_user_connection_id_product_variation_table cannot be reverted.\n";

        return false;
    }
    */
}
