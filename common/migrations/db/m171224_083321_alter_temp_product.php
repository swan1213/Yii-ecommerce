<?php

use yii\db\Migration;

/**
 * Class m171224_083321_alter_temp_product
 */
class m171224_083321_alter_temp_product extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%temp_product}}', 'permanent_hidden');

        $this->addColumn('{{%temp_product}}', 'permanent_hidden', $this->string(16)->defaultValue('No')->after('status'));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171224_083321_alter_temp_product cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171224_083321_alter_temp_product cannot be reverted.\n";

        return false;
    }
    */
}
