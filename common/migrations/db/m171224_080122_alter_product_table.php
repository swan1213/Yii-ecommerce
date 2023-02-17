<?php

use yii\db\Migration;
use common\models\Product;
/**
 * Class m171224_080122_alter_product_table
 */
class m171224_080122_alter_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%product}}', 'permanent_hidden');

        $this->addColumn('{{%product}}', 'permanent_hidden', $this->string(16)->defaultValue('No')->after('status'));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171224_080122_alter_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171224_080122_alter_product_table cannot be reverted.\n";

        return false;
    }
    */
}
