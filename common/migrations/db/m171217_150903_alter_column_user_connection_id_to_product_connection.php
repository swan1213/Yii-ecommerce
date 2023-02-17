<?php

use yii\db\Migration;

/**
 * Class m171217_150903_alter_column_user_connection_id_to_product_connection
 */
class m171217_150903_alter_column_user_connection_id_to_product_connection extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_product_connection_connection_id', '{{%product_connection}}');
        $this->renameColumn('{{%product_connection}}', 'connection_id', 'user_connection_id');
        $this->addForeignKey('fk_product_connection_user_connection_id', '{{%product_connection}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171217_150903_alter_column_user_connection_id_to_product_connection cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171217_150903_alter_column_user_connection_id_to_product_connection cannot be reverted.\n";

        return false;
    }
    */
}
