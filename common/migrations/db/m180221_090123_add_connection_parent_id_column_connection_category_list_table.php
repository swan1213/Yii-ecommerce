<?php

use yii\db\Migration;

/**
 * Class m180221_090123_add_connection_parent_id_column_connection_category_list_table
 */
class m180221_090123_add_connection_parent_id_column_connection_category_list_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%connection_category_list}}', 'connection_parent_id', $this->bigInteger()->unsigned()->after('connection_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180221_090123_add_connection_parent_id_column_connection_category_list_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180221_090123_add_connection_parent_id_column_connection_category_list_table cannot be reverted.\n";

        return false;
    }
    */
}
