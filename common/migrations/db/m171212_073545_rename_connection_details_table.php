<?php

use yii\db\Migration;

/**
 * Class m171212_073545_rename_connection_details_table
 */
class m171212_073545_rename_connection_details_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_connection_details_user_connection_id', '{{%connection_details}}');
        $this->renameTable('{{%connection_details}}', '{{%user_connection_details}}');
        $this->addForeignKey('fk_user_connection_details_user_connection_id', '{{%user_connection_details}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171212_073545_rename_connection_details_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171212_073545_rename_connection_details_table cannot be reverted.\n";

        return false;
    }
    */
}
