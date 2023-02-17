<?php

use yii\db\Migration;

/**
 * Handles the creation of table `connection_attribution`.
 */
class m180124_141234_create_connection_attribution_table extends Migration
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

        $this->createTable('{{%connection_attribution}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'label' => $this->string(512),
            'description' => $this->string('1024'),
            'connection_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%connection_attribution}}');
    }
}
