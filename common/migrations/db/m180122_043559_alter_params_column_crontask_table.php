<?php

use yii\db\Migration;

/**
 * Class m180122_043559_alter_params_column_crontask_table
 */
class m180122_043559_alter_params_column_crontask_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%crontask}}', 'params', $this->string(1024));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180122_043559_alter_params_column_crontask_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180122_043559_alter_params_column_crontask_table cannot be reverted.\n";

        return false;
    }
    */
}
