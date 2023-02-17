<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `temp_product`.
 */
class m180208_160504_drop_temp_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropForeignKey('fk_temp_product_user_id', '{{%temp_product}}');

        $this->dropTable('{{%temp_product}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {

        echo "m180208_160504_drop_temp_product_table cannot be reverted.\n";

        return false;


    }
}
