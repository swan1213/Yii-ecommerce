<?php

use yii\db\Migration;

/**
 * Handles adding connection_image_id to table `product_image`.
 */
class m171217_161826_add_connection_image_id_column_to_product_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%product_image}}', 'connection_image_id', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
