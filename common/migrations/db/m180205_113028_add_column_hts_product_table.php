<?php

use yii\db\Migration;

/**
 * Class m180205_113028_add_column_hts_product_table
 */
class m180205_113028_add_column_hts_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'hts', $this->string(255)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180205_113028_add_column_hts_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_113028_add_column_hts_product_table cannot be reverted.\n";

        return false;
    }
    */
}
