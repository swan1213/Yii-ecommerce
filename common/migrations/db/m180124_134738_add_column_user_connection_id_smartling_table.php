<?php

use yii\db\Migration;

/**
 * Class m180124_134738_add_column_user_connection_id_smartling_table
 */
class m180124_134738_add_column_user_connection_id_smartling_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%smartling}}', 'user_connection_id', $this->bigInteger()->notNull()->after('user_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180124_134738_add_column_user_connection_id_smartling_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_134738_add_column_user_connection_id_smartling_table cannot be reverted.\n";

        return false;
    }
    */
}
