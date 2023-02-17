<?php

use yii\db\Migration;

/**
 * Class m180207_094253_seed_general_category_table
 */
class m180207_094253_seed_general_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%general_category}}', ['name'], [
            ['Animals & Pet Supplies'],
            ['Apparel & Accessories'],
            ['Arts & Entertainment'],
            ['Baby & Toddler'],
            ['Business & Industrial'],
            ['Cameras & Optics'],
            ['Electronics'],
            ['Food, Beverages & Tobacco'],
            ['Furniture'],
            ['Hardware'],
            ['Health & Beauty'],
            ['Home & Garden'],
            ['Mature'],
            ['Media'],
            ['Office Supplies'],
            ['Religious & Ceremonial'],
            ['Software'],
            ['Sporting Goods'],
            ['Toys & Games'],
            ['Vehicles & Parts'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180207_094253_seed_general_category_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180207_094253_seed_general_category_table cannot be reverted.\n";

        return false;
    }
    */
}
