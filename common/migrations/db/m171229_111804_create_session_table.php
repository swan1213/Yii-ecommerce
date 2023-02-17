<?php

use yii\db\Migration;

/**
 * Handles the creation of table `session`.
 */
class m171229_111804_create_session_table extends Migration
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


        $this->createTable('app_session', [
            'id' => $this->string(64)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->text()
        ], $tableOptions);

        $this->addPrimaryKey('pk_session_id', 'app_session', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('app_session');
    }
}
