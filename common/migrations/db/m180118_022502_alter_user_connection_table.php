<?php

use yii\db\Migration;

/**
 * Class m180118_022502_alter_user_connection_table
 */
class m180118_022502_alter_user_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_connection}}', 'mapping_status', $this->integer(1)->defaultValue(0)->after('smartling_status'));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180118_022502_alter_user_connection_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180118_022502_alter_user_connection_table cannot be reverted.\n";

        return false;
    }
    */
}
