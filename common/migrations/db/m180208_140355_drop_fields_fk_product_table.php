<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `fields_fk_product`.
 */
class m180208_140355_drop_fields_fk_product_table extends Migration
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

        $this->dropForeignKey('fk_product_user_connection_id', '{{%product}}');

        $this->dropColumn('{{%product}}', 'user_connection_id');
        $this->dropColumn('{{%product}}', 'other_connections');
        $this->dropColumn('{{%product}}', 'connection_product_id');

        $this->createTable('{{%product_connection}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'user_connection_id' => $this->bigInteger()->unsigned(),
            'connection_product_id' => $this->string(512)->defaultValue('0'),
            'product_id' => $this->bigInteger()->unsigned(),
            'status' => $this->string(32)->defaultValue('Yes'),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_connection_user_id', '{{%product_connection}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_connection_user_connection_id', '{{%product_connection}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_connection_product_id', '{{%product_connection}}', 'product_id', '{{%product}}', 'id', 'cascade');


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m180208_140355_drop_fields_fk_product_table cannot be reverted.\n";

        return false;

    }
}
