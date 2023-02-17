<?php

use yii\db\Migration;

/**
 * Class m180111_005109_rename_column_user_connection_table
 */
class m180111_005109_rename_column_user_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%user_connection}}', 'fullfill_status');

        $this->addColumn('{{%user_connection}}', 'fulfillment_list_id', $this->integer()->after('smartling_status'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180111_005109_rename_column_user_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180111_005109_rename_column_user_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
