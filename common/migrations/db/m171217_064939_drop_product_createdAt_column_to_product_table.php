<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `product_createdAt_column_to_product`.
 */
class m171217_064939_drop_product_createdAt_column_to_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%product}}', 'product_createdAt');
        $this->dropColumn('{{%product}}', 'product_updatedAt');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m171217_064939_drop_product_createdAt_column_to_product_table cannot be reverted.\n";

        return false;
    }
}
