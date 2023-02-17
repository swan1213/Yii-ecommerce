<?php

use yii\db\Migration;

/**
 * Handles the creation of table `category`.
 */
class m171212_180254_create_category_table extends Migration
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

        $this->createTable('{{%category}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'description' => $this->text(),
            'parent_id' => $this->bigInteger()->unsigned()->defaultValue(0),
            'user_id' => $this->bigInteger()->unsigned(),
            'user_connection_id' => $this->bigInteger()->unsigned(),
            'connection_category_id' => $this->string(),
            'connection_parent_id' => $this->string(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_category_user_id', '{{%category}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_category_user_connection_id', '{{%category}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%category}}');
    }
}
