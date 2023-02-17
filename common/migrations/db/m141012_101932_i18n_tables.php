<?php

use yii\db\Migration;

class m141012_101932_i18n_tables extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%i18n_source_message}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'category' => $this->string(32),
            'message' => $this->text()
        ], $tableOptions);

        $this->createTable('{{%i18n_message}}', [
            'id' => $this->bigInteger()->unsigned(),
            'language' => $this->string(16),
            'translation' => $this->text()
        ], $tableOptions);

        $this->addPrimaryKey('i18n_message_pk', '{{%i18n_message}}', ['id', 'language']);
        $this->addForeignKey('fk_i18n_message_source_message', '{{%i18n_message}}', 'id', '{{%i18n_source_message}}', 'id', 'cascade', 'restrict');
    }

    public function down()
    {
        $this->dropForeignKey('fk_i18n_message_source_message', '{{%i18n_message}}');
        $this->dropTable('{{%i18n_message}}');
        $this->dropTable('{{%i18n_source_message}}');
    }
}
