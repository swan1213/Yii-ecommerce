<?php

use yii\db\Migration;

/**
 * Handles the creation of table `integration`.
 */
class m180121_151714_create_integration_table extends Migration
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

        $this->dropForeignKey('fk_integrations_user_id', '{{%integration}}');
        $this->dropTable('{{%integration}}');


        $this->createTable('{{%integration}}', [
            'id' => $this->primaryKey()->unsigned(),
            'type_id' => $this->smallInteger(3)->defaultValue(1),
            'name' => $this->string(255)->notNull(),
            'url' => $this->string(512),
            'image_url' => $this->string(512),
            'enabled' => "ENUM('Yes', 'No') DEFAULT 'No'",
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('integration');
    }
}
