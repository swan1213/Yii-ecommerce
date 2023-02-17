<?php

use yii\db\Migration;

/**
 * Handles the creation of table `attribution`.
 */
class m171216_045601_create_attribution_table extends Migration
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

        $this->createTable('{{%attribution_type}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'name' => $this->string(255),
            'label' => $this->string(255),
            'description' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_attribution_type_user_id', '{{%attribution_type}}', 'user_id', '{{%user}}', 'id', 'cascade');


        $this->createTable('{{%attribution}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'name' => $this->string(255),
            'label' => $this->string(255),
            'description' => $this->string(1024),
            'attribution_type' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_attribution_user_id', '{{%attribution}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_attribution_attribution_type', '{{%attribution}}', 'attribution_type', '{{%attribution_type}}', 'id', 'cascade');

        $this->createTable('{{%product_attribution}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'attribution_id' => $this->bigInteger()->unsigned(),
            'product_id' => $this->bigInteger()->unsigned(),
            'item_name' => $this->string(255),
            'item_value' => $this->string(255),
            'connection_attribute_id' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_attribution_user_id', '{{%product_attribution}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_attribution_attribution_id', '{{%product_attribution}}', 'attribution_id', '{{%attribution}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_attribution_product_id', '{{%product_attribution}}', 'product_id', '{{%product}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%product_attribution}}');
        $this->dropTable('{{%attribution}}');
        $this->dropTable('{{%attribution_type}}');
    }
}
