<?php

use yii\db\Migration;

/**
 * Class m171218_005831_add_column_default_to_customer_address_table
 */
class m171218_005831_add_column_default_to_customer_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_address}}', 'default', $this->string(32)->defaultValue('No'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_005831_add_column_default_to_customer_address_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_005831_add_column_default_to_customer_address_table cannot be reverted.\n";

        return false;
    }
    */
}
