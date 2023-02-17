<?php

use yii\db\Migration;

/**
 * Handles the creation of table `feed`.
 */
class m180116_150716_create_feed_table extends Migration
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


        $this->createTable('{{%feed}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255),
            'link' => $this->string(512)
        ], $tableOptions);


        $this->batchInsert('{{%feed}}', ['name', 'link'], [
            ['google', 'google_feed'],
            ['facebook', 'facebook_feed'],
            ['twitter', 'tweet_feed'],
            ['pinterest', 'pin_feed'],
        ]);


        $this->createTable('{{%user_feed}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
	        'name' => $this->string(512),
	        'user_id' => $this->bigInteger()->unsigned()->notNull(),
	        'feed_id' => $this->integer()->unsigned()->notNull(),
            'link' => $this->string(512),
            'code' => $this->string(1024),
            'categories' => $this->text(),
	        'country_codes' => $this->text(),
        ], $tableOptions);

        //$this->addForeignKey('fk_user_feed_user_id', '{{%user_feed}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_user_feed_feed_id', '{{%user_feed}}', 'feed_id', '{{%feed}}', 'id', 'cascade');

        $this->insert('{{%user_feed}}', [
            'name' => 'FB feed1',
            'user_id' => '3',
            'feed_id' => '2',
            'link' => 'https://elliot.global/facebook_feed?u_id=XNd1sd1',
            'code' => 'XNd1sd1',
            'categories' => '{1,3,4,5,9}',
            'country_codes' => '{\"US\", \"IN\"}'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_user_feed_feed_id', '{{%user_feed}}');

        $this->dropTable('{{%user_feed}}');
        $this->dropTable('{{%feed}}');
    }
}
