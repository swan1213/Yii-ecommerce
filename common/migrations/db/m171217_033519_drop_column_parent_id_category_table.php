<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `column_parent_id_category`.
 */
class m171217_033519_drop_column_parent_id_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%category}}', 'parent_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m171217_033519_drop_column_parent_id_category_table cannot be reverted.\n";

        return false;
    }
}
