<?php

use yii\db\Migration;

/**
 * Class m171220_015956_add_combine_count_to_variation_set_table
 */
class m171220_015956_add_item_count_to_variation_set_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%variation_set}}', 'item_count', $this->integer(2)->defaultValue(0)->after('description'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171220_015956_add_combine_count_to_variation_set_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171220_015956_add_combine_count_to_variation_set_table cannot be reverted.\n";

        return false;
    }
    */
}
