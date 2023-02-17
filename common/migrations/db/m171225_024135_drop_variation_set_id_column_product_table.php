<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `variation_set_id_column_product`.
 */
class m171225_024135_drop_variation_set_id_column_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%product}}', 'variation_set_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m171225_024135_drop_variation_set_id_column_product_table cannot be reverted.\n";

        return false;

    }
}
