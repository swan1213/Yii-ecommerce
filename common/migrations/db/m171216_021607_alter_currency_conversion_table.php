<?php

use yii\db\Migration;

/**
 * Class m171216_021607_alter_currency_conversion_table
 */
class m171216_021607_alter_currency_conversion_table extends Migration
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
        $this->dropTable('{{%currency_conversion}}');

        $this->createTable('{{%currency_conversion}}', [
            'id' => $this->primaryKey()->unsigned(),
            'from_currency' => $this->string(255),
            'rate' => $this->double()->defaultValue(1),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171216_021607_alter_currency_conversion_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171216_021607_alter_currency_conversion_table cannot be reverted.\n";

        return false;
    }
    */
}
