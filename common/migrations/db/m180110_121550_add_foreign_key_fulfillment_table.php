<?php

use yii\db\Migration;

/**
 * Class m180110_121550_add_foreign_key_fulfillment_table
 */
class m180110_121550_add_foreign_key_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addForeignKey('fk_fulfillment_fulfillment_id', '{{%fulfillment}}', 'fulfillment_id', '{{%fulfillment_list}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180110_121550_add_foreign_key_fulfillment_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180110_121550_add_foreign_key_fulfillment_table cannot be reverted.\n";

        return false;
    }
    */
}
