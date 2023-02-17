<?php

use yii\db\Migration;

/**
 * Class m171217_014826_rename_type_column_to_notification_table
 */
class m171217_014826_rename_type_column_to_notification_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('{{%notification}}', 'type', 'title');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171217_014826_rename_type_column_to_notification_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171217_014826_rename_type_column_to_notification_table cannot be reverted.\n";

        return false;
    }
    */
}
