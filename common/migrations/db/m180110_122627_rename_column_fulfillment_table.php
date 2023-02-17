<?php

use yii\db\Migration;

/**
 * Class m180110_122627_rename_column_fulfillment_table
 */
class m180110_122627_rename_column_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_fulfillment_fulfillment_id', '{{%fulfillment}}');

        $this->renameColumn('{{%fulfillment}}', 'fulfillment_id', 'fulfillment_list_id');

        $this->addForeignKey('fk_fulfillment_fulfillment_list_id', '{{%fulfillment}}', 'fulfillment_list_id', '{{%fulfillment_list}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180110_122627_rename_column_fulfillment_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180110_122627_rename_column_fulfillment_table cannot be reverted.\n";

        return false;
    }
    */
}
