<?php

use yii\db\Migration;

/**
 * Handles the creation of table `fulfillment_list`.
 */
class m171208_073011_create_fulfillment_list_table extends Migration
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

        $this->createTable('fulfillment_list', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(512),
            'link' => $this->string(512),
            'image_link' => $this->string(512),
            'type' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('fulfillment_list');
    }
}
