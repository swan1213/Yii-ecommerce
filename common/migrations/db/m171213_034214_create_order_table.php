<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order`.
 */
class m171213_034214_create_order_table extends Migration
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

        $this->createTable('{{%order_fee}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'market_place_fee' => $this->double(),
            'credit_card_fee' => $this->double(),
            'processing_fee' => $this->double(),
            'base_shippping_cost' => $this->double(),
            'shipping_cost_tax' => $this->double(),
            'base_handling_cost' => $this->double(),
            'handling_cost_tax' => $this->double(),
            'base_wrapping_cost' => $this->double(),
            'wrapping_cost_tax' => $this->double(),
            'payment_method' => $this->string(255),
            'payment_provider' => $this->string(255),
            'payment_status' => $this->string(128),
            'refunded_amount' => $this->double(),
            'discount_amount' => $this->double(),
            'coupon_discount' => $this->double(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->createTable('{{%order}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'user_connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'customer_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_order_id' => $this->string(255),
            'status' => $this->string(),
            'product_quantity' => $this->string(255),
            'ship_street_1' => $this->string(255),
            'ship_street_2' => $this->string(255),
            'ship_city' => $this->string(255),
            'ship_state' => $this->string(255),
            'ship_zip' => $this->string(64),
            'ship_country' => $this->string(128),
            'ship_country_iso' => $this->string(64),
            'bill_street_1' => $this->string(255),
            'bill_street_2' => $this->string(255),
            'bill_city' => $this->string(255),
            'bill_state' => $this->string(255),
            'bill_zip' => $this->string(64),
            'bill_country' => $this->string(128),
            'bill_country_iso' => $this->string(64),
            'brand_logo_image' => $this->string(512),
            'tracking_link' => $this->string(512),
            'fee_id' => $this->bigInteger()->unsigned()->notNull(),
            'total_amount' => $this->double(),
            'order_createdAt' => $this->datetime(),
            'sf_data' => $this->text(),
            'visible' => "ENUM('active','in_active') NOT NULL DEFAULT 'active'",
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_order_user_id', '{{%order}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_user_connection_id', '{{%order}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_customer_id', '{{%order}}', 'customer_id', '{{%customer}}', 'id');
        $this->addForeignKey('fk_order_fee_id', '{{%order}}', 'fee_id', '{{%order_fee}}', 'id');


        $this->createTable('{{%order_connection}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'order_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);


        $this->addForeignKey('fk_order_connection_user_id', '{{%order_connection}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_connection_order_id', '{{%order_connection}}', 'order_id', '{{%order}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_connection_connecion_id', '{{%order_connection}}', 'connection_id', '{{%connection}}', 'id', 'cascade');

        $this->createTable('{{%order_product}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'order_id' => $this->bigInteger()->unsigned()->notNull(),
            'product_id' => $this->bigInteger()->unsigned()->notNull(),
            'order_product_sku' => $this->string(255),
            'price'=> $this->double(),
            'qty' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_order_product_user_id', '{{%order_product}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_product_order_id', '{{%order_product}}', 'order_id', '{{%order}}', 'id', 'cascade');
        $this->addForeignKey('fk_order_product_product_id', '{{%order_product}}', 'product_id', '{{%product}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order_product}}');
        $this->dropTable('{{%order_connection}}');
        $this->dropTable('{{%order}}');
        $this->dropTable('{{%order_fee}}');
    }
}
