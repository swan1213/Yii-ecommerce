<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_level_permission`.
 */
class m171211_122625_create_user_permission_table extends Migration
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

        $this->createTable('{{%user_permission}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'title' => $this->string(255),
            'menu_permission' => $this->text(),
            'channel_permission' => $this->text(),
            'other_permission' => $this->text(),
            'user_id' => $this->bigInteger()->unsigned(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%user_permission}}');
    }
}
