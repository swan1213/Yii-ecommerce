<?php

use yii\db\Migration;

/**
 * Class m180112_014549_add_foreign_key_fulfillment_table
 */
class m180112_014549_add_foreign_key_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->addForeignKey('fk_fulfillment_user_id', '{{%fulfillment}}', 'user_id', '{{%user}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180112_014549_add_foreign_key_fulfillment_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180112_014549_add_foreign_key_fulfillment_table cannot be reverted.\n";

        return false;
    }
    */
}
