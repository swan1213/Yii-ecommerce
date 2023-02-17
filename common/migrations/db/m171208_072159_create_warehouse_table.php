<?php

use yii\db\Migration;
use common\models\Fulfillment;

/**
 * Handles the creation of table `warehouse`.
 */
class m171208_072159_create_warehouse_table extends Migration
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

        $this->createTable('warehouse', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'name' => $this->string(255),
            'fulfillment_id' => $this->bigInteger()->unsigned(),
            'key_data' => $this->string(255),
            'value_data' => $this->text(),
            'connected' => $this->integer(1)->defaultValue(Fulfillment::CONNECTED_NO),
            'fulfillment_link' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'

        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('warehouse');
    }
}
