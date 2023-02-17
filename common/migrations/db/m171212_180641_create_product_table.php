<?php

use yii\db\Migration;

use common\models\UserProfile;
use common\models\ProductImage;
use common\models\ProductConnection;
use common\models\Product;
/**
 * Handles the creation of table `product`.
 */
class m171212_180641_create_product_table extends Migration
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

        $this->createTable('{{%product}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'name' => $this->string(255),
            'sku' => $this->string(255),
            'url' => $this->string(512),
            'upc' => $this->string(255),
            'ean' => $this->string(255),
            'jan' => $this->string(255),
            'isbn' => $this->string(255),
            'mpn' => $this->string(255),
            'description' => $this->text(),
            'adult' => "ENUM('No', 'Yes') DEFAULT 'No'",
            'age_group' => "ENUM('Newborn','Infant','Toddler','Kids','Adult') DEFAULT NULL",
            'brand' => $this->string(255),
            'condition' => "ENUM('New','Used','Refurbished') DEFAULT 'New'",
            'gender' => $this->string(32)->defaultValue(UserProfile::GENDER_UNISEX),
            'weight' => $this->string(64),
            'package_length' => $this->string(128),
            'package_height' => $this->string(128),
            'package_width' => $this->string(128),
            'package_box' => $this->string(128),
            'variation_set_id' => $this->bigInteger()->unsigned(),
            'stock_quantity' => $this->bigInteger(),
            'allocate_inventory' => $this->double(),
            'currency' => $this->string(64),
            'country_code' => $this->string(64),
            'stock_level' => "ENUM('In Stock','Out of Stock') DEFAULT 'Out of Stock'",
            'stock_status' => "ENUM('Visible','Hidden') DEFAULT 'Hidden'",
            'low_stock_notification' => $this->integer()->defaultValue(Product::LOW_STOCK_NOTIFICATION),
            'price' => $this->double()->defaultValue(0),
            'sales_price' => $this->double()->defaultValue(0),
            'schedule_sales_date' => $this->dateTime(),
            'status' => "enum('active','in_active') NOT NULL DEFAULT 'active'",
            'permanent_hidden' => "enum('active','in_active') NOT NULL DEFAULT 'active'",
            'stock_manage' => "enum('Yes','No') NOT NULL DEFAULT 'No'",
            'user_connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_product_id' => $this->string(512)->defaultValue('0'),
            'warranty_type' => $this->string(255),
            'warranty_period' => $this->string(255),
            'translate_status' => "enum('Yes','No') NOT NULL DEFAULT 'No'",
            'product_createdAt' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'product_updatedAt' => 'datetime on update current_timestamp',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_user_id', '{{%product}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_user_connection_id', '{{%product}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');

        $this->createTable('{{%product_image}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'product_id' => $this->bigInteger()->unsigned(),
            'label' => $this->string(255),
            'default_link' => $this->string(512),
            'alternative_link' => $this->string(512),
            'html_video_link' => $this->string(512),
            'degree_360_video_link' => $this->string(512),
            'tag_status' => $this->string(255),
            'tag' => $this->string(255),
            'priority' => $this->string(255),
            'visible' => $this->string(32)->defaultValue(ProductImage::DEFAULT_IMAGE_YES),
            'image_status' => $this->integer(1)->defaultValue(1),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_image_product_id', '{{%product_image}}', 'product_id', '{{%product}}', 'id', 'cascade');


        $this->createTable('{{%product_connection}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'connection_id' => $this->bigInteger()->unsigned(),
            'product_id' => $this->bigInteger()->unsigned(),
            'status' => $this->string(32)->defaultValue('Yes'),
            'fulfillment_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_connection_user_id', '{{%product_connection}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_connection_connection_id', '{{%product_connection}}', 'connection_id', '{{%connection}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_connection_product_id', '{{%product_connection}}', 'product_id', '{{%product}}', 'id', 'cascade');


        $this->createTable('{{%product_category}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'category_id' => $this->bigInteger()->unsigned(),
            'product_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_category_user_id', '{{%product_category}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_category_category_id', '{{%product_category}}', 'category_id', '{{%category}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_category_product_id', '{{%product_category}}', 'product_id', '{{%product}}', 'id', 'cascade');


        $this->createTable('{{%variation_type}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'description' => $this->string(512),
            'product_type' => $this->bigInteger()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_type_product_type', '{{%variation_type}}', 'product_type', '{{%product_type}}', 'id');
        $this->addForeignKey('fk_variation_type_user_id', '{{%variation_type}}', 'user_id', '{{%user}}', 'id', 'cascade');


        $this->createTable('{{%variation_item}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'type_id' => $this->bigInteger()->unsigned()->notNull(),
            'item_label' => $this->string(255),
            'item_value' => $this->string(255),
            'connection_item_id' => $this->string(512)->defaultValue('0'),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_item_type_id', '{{%variation_item}}', 'type_id', '{{%variation_type}}', 'id');
        $this->addForeignKey('fk_variation_item_user_id', '{{%variation_item}}', 'user_id', '{{%user}}', 'id', 'cascade');

        $this->createTable('{{%variation}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'items' => $this->text(),
            'description' => $this->text(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_user_id', '{{%variation}}', 'user_id', '{{%user}}', 'id', 'cascade');

        $this->createTable('{{%variation_set}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'items' => $this->text(),
            'description' => $this->string(512),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_set_user_id', '{{%variation_set}}', 'user_id', '{{%user}}', 'id', 'cascade');

        $this->createTable('{{%product_variation}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'variation_id' => $this->bigInteger()->unsigned()->notNull(),
            'product_id' => $this->bigInteger()->unsigned(),
            'sku_key' => $this->string(255),
            'sku_value' => $this->string(512),
            'inventory_key' => $this->string(255),
            'inventory_value' => $this->string(255)->defaultValue('0'),
            'price_key' => $this->string(255),
            'price_value' => $this->string(255),
            'weight_key' => $this->string(255),
            'weight_value' => $this->string(255),
            'variation_set_id' => $this->bigInteger()->unsigned(),
            'connection_variation_id' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_variation_user_id', '{{%product_variation}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_variation_variation_id', '{{%product_variation}}', 'variation_id', '{{%variation}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_variation_product_id', '{{%product_variation}}', 'product_id', '{{%product}}', 'id', 'cascade');



    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%product_variation}}');
        $this->dropTable('{{%variation_set}}');
        $this->dropTable('{{%variation}}');
        $this->dropTable('{{%variation_item}}');
        $this->dropTable('{{%variation_type}}');
        $this->dropTable('{{%product_category}}');
        $this->dropTable('{{%product_connection}}');
        $this->dropTable('{{%product_image}}');
        $this->dropTable('{{%product}}');
    }
}
