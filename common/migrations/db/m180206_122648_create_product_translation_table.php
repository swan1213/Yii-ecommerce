<?php

use yii\db\Migration;

/**
 * Handles the creation of table `product_translation`.
 */
class m180206_122648_create_product_translation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%product_translation}}', [
            'id' => $this->bigPrimaryKey(),
            'smartling_id' => $this->bigInteger(),
            'product_id' => $this->bigInteger(),
            'name' => $this->string(255),
            'description' => $this->text(),
            'brand' => $this->string(255),
            'override' => $this->string(16)->defaultValue('No')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%product_translation}}');
    }
}
