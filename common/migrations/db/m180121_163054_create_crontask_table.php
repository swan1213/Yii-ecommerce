<?php

use yii\db\Migration;

/**
 * Handles the creation of table `crontask`.
 */
class m180121_163054_create_crontask_table extends Migration
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

        $this->createTable('{{%crontask}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'action' => $this->string(255)->notNull(),
            'params' => $this->string(1024)->notNull(),
            'completed' => "ENUM('Yes', 'No') DEFAULT 'No'",
            'enabled' => "ENUM('Yes', 'No') DEFAULT 'Yes'",
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%crontask}}');
    }
}
