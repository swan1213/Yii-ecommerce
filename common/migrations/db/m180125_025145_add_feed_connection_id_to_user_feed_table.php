<?php

use yii\db\Migration;

/**
 * Class m180125_025145_add_feed_connection_id_to_user_feed_table
 */
class m180125_025145_add_feed_connection_id_to_user_feed_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_feed}}', 'feed_connection_id', $this->string(25)->notNull()->after('feed_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180125_025145_add_feed_connection_id_to_user_feed_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180125_025145_add_feed_connection_id_to_user_feed_table cannot be reverted.\n";

        return false;
    }
    */
}
