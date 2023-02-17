<?php

use yii\db\Migration;

use common\models\UserProfile;
/**
 * Handles the creation of table `customer`.
 */
class m171212_063525_create_customer_table extends Migration
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

        $this->createTable('{{%customer}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_customerId' => $this->string(255),
            'first_name'=> $this->string(255),
            'last_name' => $this->string(255),
            'email' => $this->string(255),
            'photo_image' => $this->string(512),
            'cover_image' => $this->string(512),
            'dob' => $this->date(),
            'gender' => $this->string(32)->defaultValue(UserProfile::GENDER_UNISEX),
            'visible' => "ENUM('Yes', 'No') DEFAULT 'Yes'",
            'user_connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'customer_created' => $this->dateTime(),
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);

        $this->addForeignKey('fk_customer_user_connection_id', '{{%customer}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');
        $this->addForeignKey('fk_customer_user_id', '{{%customer}}', 'user_id', '{{%user}}', 'id', 'cascade');

        $this->createTable('{{%customer_address}}', [
            'customer_id' => $this->bigPrimaryKey()->unsigned(),
            'first_name' => $this->string(255),
            'last_name' => $this->string(255),
            'company' => $this->string(255),
            'country' => $this->string(255),
            'country_iso' => $this->string(255),
            'street_1' => $this->string(255),
            'street_2' => $this->string(255),
            'state' => $this->string(255),
            'city' => $this->string(255),
            'zip' => $this->string(255),
            'phone' => $this->string(255),
            'address_type' => $this->string(255),
        ], $tableOptions);

        $this->addForeignKey('fk_customer_address_customer_id', '{{%customer_address}}', 'customer_id', '{{%customer}}', 'id', 'cascade');




    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%customer_address}}');
        $this->dropTable('{{%customer}}');
    }
}
