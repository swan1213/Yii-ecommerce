<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_fulfillment`.
 */
class m171214_152605_create_order_fulfillment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_fulfillment}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'order_id' => $this->bigInteger()->unsigned(),
            'fulfillment_id' => $this->integer(),
            'name' => $this->string(255),
            'key_data' => $this->string(255),
            'value_data' => $this->text(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_order_fulfillment_user_id', '{{%order_fulfillment}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_fulfillment_order_id', '{{%order_fulfillment}}', 'order_id', '{{%order}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order_fulfillment}}');
    }
}
