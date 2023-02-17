<?php

use yii\db\Migration;

/**
 * Class m180207_095230_add_column_general_category_id_user_table
 */
class m180207_095230_add_column_general_category_id_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'general_category_id', $this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180207_095230_add_column_general_category_id_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180207_095230_add_column_general_category_id_user_table cannot be reverted.\n";

        return false;
    }
    */
}
