<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_integration`.
 */
class m180121_151733_create_user_integration_table extends Migration
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


        $this->createTable('{{%user_integration}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'integration_id' => $this->integer()->unsigned()->notNull(),
            'connected' => "ENUM('Yes', 'No') DEFAULT 'No'",
            'connection_info' => $this->string(1024)->notNull(),
            'extra_info' => $this->string(1024),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_user_integration_user_id', '{{%user_integration}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_user_integration_integration_id', '{{%user_integration}}', 'integration_id', '{{%integration}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('user_integration');
    }
}
