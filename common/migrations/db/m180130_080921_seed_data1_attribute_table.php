<?php

use yii\db\Migration;

/**
 * Class m180130_080921_seed_data1_attribute_table
 */
class m180130_080921_seed_data1_attribute_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%attribution}}', ['name', 'default'], [
            ['Name', 1],
            ['SKU', 1],
            ['Brand', 1],
            ['Weight', 1],
            ['Availability', 1],
            ['Description', 1],
            ['Package Length', 1],
            ['Package Height', 1],
            ['Package Width', 1],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180130_080921_seed_data1_attribute_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180130_080921_seed_data1_attribute_table cannot be reverted.\n";

        return false;
    }
    */
}
