<?php

use yii\db\Migration;

/**
 * Class m180116_142444_alter_column_package_box_table
 */
class m180116_142444_alter_column_package_box_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%product}}', 'package_box', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180116_142444_alter_column_package_box_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180116_142444_alter_column_package_box_table cannot be reverted.\n";

        return false;
    }
    */
}
