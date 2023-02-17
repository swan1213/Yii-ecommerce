<?php

use yii\db\Migration;
use common\models\ProductImage;
/**
 * Class m171217_154733_alter_product_image_table
 */
class m171217_154733_alter_product_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->dropForeignKey('fk_product_image_product_id','{{%product_image}}');
        $this->dropTable('{{%product_image}}');

        $this->createTable('{{%product_image}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'product_id' => $this->bigInteger()->unsigned(),
            'label' => $this->string(255),
            'link' => $this->string(512),
            'alternative_image' => $this->string(512),
            'html_video_link' => $this->string(512),
            'degree_360_video_link' => $this->string(512),
            'tag_status' => $this->string(255),
            'tag' => $this->string(255),
            'priority' => $this->string(255),
            'default_image' => $this->string(32)->defaultValue(ProductImage::DEFAULT_IMAGE_NO),
            'status' => $this->integer(1)->defaultValue(1),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->addForeignKey('fk_product_image_user_id', '{{%product_image}}', 'user_id', '{{%user}}', 'id', 'cascade');
        $this->addForeignKey('fk_product_image_product_id', '{{%product_image}}', 'product_id', '{{%product}}', 'id', 'cascade');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171217_154733_alter_product_image_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171217_154733_alter_product_image_table cannot be reverted.\n";

        return false;
    }
    */
}
