<?php

use yii\db\Migration;

/**
 * Handles the creation of table `integrations`.
 */
class m171226_131536_create_integration_table extends Migration
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


        $this->createTable('{{%integration}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(512),
            'user_id'=> $this->bigInteger()->unsigned(),
            'key_name' => $this->string(512),
            'value' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);

        $this->addForeignKey('fk_integrations_user_id', '{{%integration}}', 'user_id', '{{%user}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%integration}}');
    }
}
