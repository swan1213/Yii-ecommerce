<?php

use yii\db\Migration;

/**
 * Class m171216_143234_rename_shop_id_column_to_user_connection_table
 */
class m171216_143234_rename_shop_id_column_to_user_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('{{%user_connection}}', 'shop_id', 'market_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171216_143234_rename_shop_id_column_to_user_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171216_143234_rename_shop_id_column_to_user_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
