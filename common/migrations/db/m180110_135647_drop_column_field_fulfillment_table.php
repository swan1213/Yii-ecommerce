<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `column_field_fulfillment`.
 */
class m180110_135647_drop_column_field_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {

        $this->dropColumn('{{%fulfillment}}', 'key_data');
        $this->dropColumn('{{%fulfillment}}', 'value_data');
        $this->addColumn('{{%fulfillment}}', 'connection_info', $this->string(1024));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m180110_135647_drop_column_field_fulfillment_table cannot be reverted.\n";

        return false;

    }
}
