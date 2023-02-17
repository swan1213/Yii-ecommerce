<?php

use yii\db\Migration;

/**
 * Handles the creation of table `connection_parent`.
 */
class m171208_204321_create_connection_parent_table extends Migration
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

        $this->createTable('connection_parent', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'url' => $this->string(512)->null(),
            'amount' => $this->double()->defaultValue(0),
            'image_url' => $this->string(512)->null(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('connection_parent');
    }
}
