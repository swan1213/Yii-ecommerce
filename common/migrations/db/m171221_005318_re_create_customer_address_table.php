<?php

use yii\db\Migration;

/**
 * Class m171221_005318_re_create_customer_address_table
 */
class m171221_005318_re_create_customer_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->dropTable('{{%customer_address}}');

        $this->createTable('{{%customer_address}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'customer_id' => $this->bigInteger()->unsigned(),
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
    public function safeDown()
    {
        echo "m171221_005318_re_create_customer_address_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171221_005318_re_create_customer_address_table cannot be reverted.\n";

        return false;
    }
    */
}
