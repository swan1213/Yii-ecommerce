<?php

use yii\db\Migration;

/**
 * Class m171218_110607_add_permission_menu
 */
class m171218_110607_add_permission_menu extends Migration
{


    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%permission_menu}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'description' => $this->string(512),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);


        $this->createTable('{{%permission_submenu}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255),
            'description' => $this->string(512),
            'parent_id' => $this->bigInteger()->unsigned(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->addForeignKey('fk_permission_parent_id', '{{%permission_submenu}}', 'parent_id', '{{%permission_menu}}', 'id', 'cascade');

    }

    public function down()
    {
        $this->dropTable('{{%permission_menu}}');
        $this->dropTable('{{%permission_submenu}}');
    }

}
