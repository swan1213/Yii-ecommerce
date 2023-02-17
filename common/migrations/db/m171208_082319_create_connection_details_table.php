<?php

use yii\db\Migration;

use common\models\Smartling;
use common\models\UserConnection;
use common\models\Connection;
/**
 * Handles the creation of table `connection_details`.
 */
class m171208_082319_create_connection_details_table extends Migration
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

        $this->createTable('{{%connection}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'type_id' => $this->bigInteger()->unsigned()->defaultValue(Connection::CONNECTION_TYPE_STORE),
            'name' => $this->string(255)->notNull(),
            'url' => $this->string(512)->null(),
            'amount' => $this->double()->defaultValue(0),
            'image_url' => $this->string(512),
            'enabled' => "ENUM('Yes', 'No') DEFAULT 'No'",
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);
        $this->createIndex('type_id', '{{%connection}}', 'type_id');

        $this->createTable('{{%user_connection}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_id' => $this->bigInteger()->unsigned()->notNull(),
            'connection_info' => $this->string(1024)->notNull(),
            'import_status' => $this->integer(1)->defaultValue(UserConnection::IMPORT_STATUS_PROCESSING),
            'connected' => $this->integer(1)->defaultValue(UserConnection::CONNECTED_NO),
            'smartling_status' => $this->integer(1)->defaultValue(Smartling::SMARTLING_STATUS_IN_ACTIVE),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->createIndex('connection_id', '{{%user_connection}}', 'connection_id');
        $this->createIndex('user_id', '{{%user_connection}}', 'user_id');
        $this->addForeignKey('fk_user_connection_user_id', '{{%user_connection}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_user_connection_connection_id', '{{%user_connection}}', 'connection_id', '{{%connection}}', 'id', 'cascade');


        $this->createTable('{{%connection_details}}', [
            'user_connection_id' => $this->bigPrimaryKey()->unsigned(),
            'store_name' => $this->string(255),
            'store_url' => $this->string(512),
            'country' => $this->string(255),
            'country_code' => $this->string(255),
            'currency' => $this->string(64),
            'currency_symbol' => $this->string(64),
            'others' => $this->text(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);

        $this->addForeignKey('fk_connection_details_user_connection_id', '{{%connection_details}}', 'user_connection_id', '{{%user_connection}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('connection_details');
    }
}
