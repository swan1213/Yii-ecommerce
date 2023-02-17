<?php

use yii\db\Migration;

/**
 * Class m171218_110801_insert_permission_menu
 */
class m171218_110801_insert_permission_menu extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('permission_menu', ['name' => 'People',]);
        $this->insert('permission_menu', ['name' => 'Products',]);
        $this->insert('permission_menu', ['name' => 'Orders',]);
        $this->insert('permission_menu', ['name' => 'Content',]);

        $this->insert('permission_submenu', ['name' => 'View All', 'parent_id' => 1,]);
        $this->insert('permission_submenu', ['name' => 'Add New', 'parent_id' => 1,]);

        $this->insert('permission_submenu', ['name' => 'View All', 'parent_id' => 2,]);
        $this->insert('permission_submenu', ['name' => 'Add New', 'parent_id' => 2,]);
        $this->insert('permission_submenu', ['name' => 'Attributes', 'parent_id' => 2,]);
        $this->insert('permission_submenu', ['name' => 'Categories', 'parent_id' => 2,]);
        $this->insert('permission_submenu', ['name' => 'Types', 'parent_id' => 2,]);
        $this->insert('permission_submenu', ['name' => 'Variations', 'parent_id' => 2,]);

        $this->insert('permission_submenu', ['name' => 'View All', 'parent_id' => 3,]);
        $this->insert('permission_submenu', ['name' => 'View All', 'parent_id' => 4,]);
        $this->insert('permission_submenu', ['name' => 'Add New', 'parent_id' => 4,]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_110801_insert_permission_menu cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_110801_insert_permission_menu cannot be reverted.\n";

        return false;
    }
    */
}
