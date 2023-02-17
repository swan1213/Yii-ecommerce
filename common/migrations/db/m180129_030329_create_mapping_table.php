<?php

use yii\db\Migration;

/**
 * Handles the creation of table `mapping`.
 */
class m180129_030329_create_mapping_table extends Migration
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
        $this->createTable('mapping', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->unsigned(),
            'elliot_id' => $this->bigInteger()->unsigned(),
            'connection_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('mapping');
    }
}
