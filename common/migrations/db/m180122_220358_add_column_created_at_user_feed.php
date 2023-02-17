<?php

use yii\db\Migration;

/**
 * Class m180122_220358_add_column_created_at_user_feed
 */
class m180122_220358_add_column_created_at_user_feed extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->addColumn('{{%user_feed}}', 'created_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addColumn('{{%user_feed}}', 'updated_at', 'datetime on update current_timestamp');

        $this->delete('{{%feed}}');

        $this->batchInsert('{{%feed}}', ['id', 'name', 'link'], [
            [1, 'google', 'feed/google'],
            [2, 'facebook', 'feed/facebook'],
            [3, 'twitter', 'feed/tweet'],
            [4, 'pinterest', 'feed/pin'],
        ]);

        $this->delete('{{%user_feed}}');

        $this->insert('{{%user_feed}}', [
            'name' => 'test2',
            'user_id' => '3',
            'feed_id' => '2',
            'link' => 'https://elliot.global/feed/facebook?u_id=NA==',
            'code' => 'NA==',
            'categories' => '[\"65\",\"84\"]',
            'country_codes' => '[\"CN\",\"MY\"]'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180122_220358_add_column_created_at_user_feed cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180122_220358_add_column_created_at_user_feed cannot be reverted.\n";

        return false;
    }
    */
}
