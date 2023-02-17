<?php

use yii\db\Migration;

/**
 * Class m171218_011716_add_column_phone_to_customer_table
 */
class m171218_011716_add_column_phone_to_customer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer}}', 'phone', $this->string(64)->after('gender'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_011716_add_column_phone_to_customer_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_011716_add_column_phone_to_customer_table cannot be reverted.\n";

        return false;
    }
    */
}
