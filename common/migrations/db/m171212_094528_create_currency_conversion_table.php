<?php

use yii\db\Migration;

/**
 * Handles the creation of table `currency_conversion`.
 */
class m171212_094528_create_currency_conversion_table extends Migration
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

        $this->createTable('{{%currency_conversion}}', [
            'from_currency' => $this->string(255),
            'rate' => $this->double()->defaultValue(1),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%currency_conversion}}');
    }
}
