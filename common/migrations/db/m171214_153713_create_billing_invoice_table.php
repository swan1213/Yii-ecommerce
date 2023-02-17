<?php

use yii\db\Migration;

/**
 * Handles the creation of table `billing_invoice`.
 */
class m171214_153713_create_billing_invoice_table extends Migration
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

        $this->createTable('{{%billing_invoice}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'stripe_id' => $this->string(),
            'total_word_count' => $this->string(),
            'amount' => $this->string(),
            'refund_amount' => $this->string(),
            'customer_email' => $this->string(),
            'invoice_name' => $this->string(),
            'status' => $this->string(),
            'user_connection_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_billing_invoice_user_id', '{{%billing_invoice}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_billing_invoice_user_connection_id', '{{%billing_invoice}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%billing_invoice}}');
    }
}
