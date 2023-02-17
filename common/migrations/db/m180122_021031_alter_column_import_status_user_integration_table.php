<?php

use yii\db\Migration;

/**
 * Class m180122_021031_alter_column_import_status_user_integration_table
 */
class m180122_021031_alter_column_import_status_user_integration_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%user_integration}}', 'connected', $this->integer(1)->defaultValue(0));
        $this->addColumn('{{%user_integration}}', 'import_status', $this->integer(2)->defaultValue(0));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180122_021031_alter_column_import_status_user_integration_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180122_021031_alter_column_import_status_user_integration_table cannot be reverted.\n";

        return false;
    }
    */
}
