<?php

use yii\db\Migration;

/**
 * Class m180212_111227_alter_is_subscription_connection_table
 */
class m180212_111227_alter_is_subscription_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%connection}}', 'is_subscription', "ENUM('Yes', 'No') DEFAULT 'No'");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180212_111227_alter_is_subscription_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180212_111227_alter_is_subscription_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
