<?php

use yii\db\Migration;

/**
 * Class m180130_121148_alter_column_feed_connection_id_user_feed_table
 */
class m180130_121148_alter_column_feed_connection_id_user_feed_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%user_feed}}', 'feed_connection_id', $this->string(64)->defaultValue('0'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180130_121148_alter_column_feed_connection_id_user_feed_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180130_121148_alter_column_feed_connection_id_user_feed_table cannot be reverted.\n";

        return false;
    }
    */
}
