<?php

use yii\db\Migration;

/**
 * Class m171218_111631_add_permission_other
 */
class m171218_111631_add_permission_other extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%permission_other}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'label' => $this->string(255),
            'description' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%permission_other}}');
    }

}
