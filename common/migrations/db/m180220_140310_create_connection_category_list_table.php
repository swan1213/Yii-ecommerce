<?php

use yii\db\Migration;

/**
 * Handles the creation of table `connection_category_list`.
 */
class m180220_140310_create_connection_category_list_table extends Migration
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


        $this->createTable('{{%connection_category_list}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(),
            'connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'category_connection_id' => $this->string(255),
            'name' => $this->string(512)->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_connection_category_list_connection_id', '{{%connection_category_list}}', 'connection_id', '{{%connection}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%connection_category_list}}');
    }
}
