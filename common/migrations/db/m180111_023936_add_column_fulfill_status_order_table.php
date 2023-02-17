<?php

use yii\db\Migration;

/**
 * Class m180111_023936_add_column_fulfill_status_order_table
 */
class m180111_023936_add_column_fulfill_status_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'fulfill_status', $this->string(16)->notNull()->defaultValue('No')->after('sf_data'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180111_023936_add_column_fulfill_status_order_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180111_023936_add_column_fulfill_status_order_table cannot be reverted.\n";

        return false;
    }
    */
}
