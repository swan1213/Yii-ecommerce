<?php

use yii\db\Migration;

/**
 * Class m171219_151753_rename_table_drop_columns_variation
 */
class m171219_151753_rename_table_drop_columns_variation extends Migration
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


        $this->dropTable('{{%variation_item}}');
        $this->dropTable('{{%variation_type}}');
        $this->dropTable('{{%product_type}}');


        $this->createTable('{{%variation_item}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'description' => $this->string(512),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_item_user_id', '{{%variation_item}}', 'user_id', '{{%user}}', 'id', 'cascade');


        $this->createTable('{{%variation_value}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'variation_item_id' => $this->bigInteger()->unsigned()->notNull(),
            'label' => $this->string(255),
            'value' => $this->string(255),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_variation_value_item_id', '{{%variation_value}}', 'variation_item_id', '{{%variation_item}}', 'id');
        $this->addForeignKey('fk_variation_value_user_id', '{{%variation_value}}', 'user_id', '{{%user}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171219_151753_rename_table_drop_columns_variation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171219_151753_rename_table_drop_columns_variation cannot be reverted.\n";

        return false;
    }
    */
}
