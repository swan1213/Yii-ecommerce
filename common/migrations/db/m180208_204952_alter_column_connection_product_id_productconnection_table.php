<?php

use yii\db\Migration;

/**
 * Class m180208_204952_alter_column_connection_product_id_productconnection_table
 */
class m180208_204952_alter_column_connection_product_id_productconnection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%product_connection}}', 'connection_product_id', $this->string(512)->defaultValue('-1'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180208_204952_alter_column_connection_product_id_productconnection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180208_204952_alter_column_connection_product_id_productconnection_table cannot be reverted.\n";

        return false;
    }
    */
}
