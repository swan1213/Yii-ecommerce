<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporate_document`.
 */
class m171214_090424_create_corporate_document_table extends Migration
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

        $this->createTable('{{%corporate_document}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'business_license' => $this->string(512),
            'business_paper' => $this->string(512),
            'tax_id' => $this->string(255),
            'connection' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_corporate_document_user_id', '{{%corporate_document}}', 'user_id', '{{%user}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%corporate_document}}');
    }
}
