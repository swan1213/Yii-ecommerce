<?php

use yii\db\Migration;

use common\models\Notification;
/**
 * Handles the creation of table `notification`.
 */
class m171209_030842_create_notification_table extends Migration
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

        $this->createTable('{{%notification}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'type' => $this->string(255),
            'message' => $this->string(512),
            'status' => $this->string(255)->defaultValue(Notification::NOTIFICATION_STATUS_UNREAD),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);

        $this->addForeignKey('fk_notification_user_id', '{{%notification}}', 'user_id', '{{%user}}', 'id', 'cascade');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('notification');

    }
}
