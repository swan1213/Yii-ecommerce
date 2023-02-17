<?php

use yii\db\Migration;
use common\models\UserProfile;
use common\models\Product;
/**
 * Handles the creation of table `temp_product`.
 */
class m171222_013234_create_temp_product_table extends Migration
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

        $this->createTable('{{%temp_product}}', [
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
            'stock_quantity' => $this->string(255),
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
            'user_connection_id' => $this->bigInteger()->unsigned()->defaultValue(0),
            'connection_product_id' => $this->string(512)->defaultValue('0'),
            'warranty_type' => $this->string(255),
            'warranty_period' => $this->string(255),
            'translate_status' => "enum('Yes','No') NOT NULL DEFAULT 'No'",
            'product_createdAt' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'product_updatedAt' => 'datetime on update current_timestamp',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
            'published' => $this->string(16)->defaultValue('No'),
        ], $tableOptions);

        $this->addForeignKey('fk_temp_product_user_id', '{{%temp_product}}', 'user_id', '{{%user}}', 'id', 'cascade');


        $this->addColumn('{{%product}}', 'published', $this->string(16)->defaultValue('Yes'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%temp_product}}');
    }
}
