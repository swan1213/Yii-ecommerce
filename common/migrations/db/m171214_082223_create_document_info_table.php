<?php

use yii\db\Migration;

/**
 * Handles the creation of table `document_info`.
 */
class m171214_082223_create_document_info_table extends Migration
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

        $this->createTable('{{%document_info}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'bank_account_no' => $this->string(255),
            'bank_roting_no' => $this->string(255),
            'bank_code' => $this->string(255),
            'bank_name' => $this->string(255),
            'bank_address' => $this->text(),
            'bank_swift' => $this->string(255),
            'bank_account_type' => $this->string(255),
            'business_tax_id' => $this->string(255),
            'alipay_payment_account_no' => $this->string(255),
            'alipay_payment_account_id' => $this->string(255),
            'alipay_payment_account_email' => $this->string(255),
            'alipay_payment_address_1' => $this->string(255),
            'alipay_payment_address_2' => $this->string(255),
            'alipay_payment_account_city' => $this->string(255),
            'alipay_payment_account_state' => $this->string(255),
            'alipay_payment_account_country' => $this->string(255),
            'alipay_payment_account_zip_code' => $this->string(255),
            'dinpay_payment_account_no' => $this->string(255),
            'dinpay_payment_account_id' => $this->string(255),
            'dinpay_payment_account_email' => $this->string(255),
            'dinpay_payment_address_1' => $this->string(255),
            'dinpay_payment_address_2' => $this->string(255),
            'dinpay_payment_account_city' => $this->string(255),
            'dinpay_payment_account_state' => $this->string(255),
            'dinpay_payment_account_country' => $this->string(255),
            'dinpay_payment_account_zip_code' => $this->string(255),
            'payoneer_payment_account_no' => $this->string(255),
            'payoneer_payment_account_id' => $this->string(255),
            'payoneer_payment_account_email' => $this->string(255),
            'payoneer_payment_address_1' => $this->string(255),
            'payoneer_payment_address_2' => $this->string(255),
            'payoneer_payment_account_city' => $this->string(255),
            'payoneer_payment_account_state' => $this->string(255),
            'payoneer_payment_account_country' => $this->string(255),
            'payoneer_payment_account_zip_code' => $this->string(255),
            'worldfirst_payment_account_no' => $this->string(255),
            'worldfirst_payment_account_id' => $this->string(255),
            'worldfirst_payment_account_email' => $this->string(255),
            'worldfirst_payment_address_1' => $this->string(255),
            'worldfirst_payment_address_2' => $this->string(255),
            'worldfirst_payment_account_city' => $this->string(255),
            'worldfirst_payment_account_state' => $this->string(255),
            'worldfirst_payment_account_country' => $this->string(255),
            'worldfirst_payment_account_zip_code' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_document_info_user_id', '{{%document_info}}', 'user_id', '{{%user}}', 'id', 'cascade');

        $this->createTable('{{%document_file}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'type' => "enum('banking','business','directors') NOT NULL",
            'file_base' => $this->string(512),
            'file_path' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_document_file_user_id', '{{%document_file}}', 'user_id', '{{%user}}', 'id', 'cascade');


        $this->createTable('{{%document_director}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'document_file_id' => $this->bigInteger()->unsigned(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'dob' => $this->datetime(),
            'address' => $this->string(),
            'last_4_social' => $this->string(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_document_director_user_id', '{{%document_director}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_document_director_document_file_id', '{{%document_director}}', 'document_file_id', '{{%document_file}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%document_director}}');
        $this->dropTable('{{%document_file}}');
        $this->dropTable('{{%document_info}}');
    }
}
