<?php

use yii\db\Migration;

/**
 * Class m180217_174954_seed_shopify_connection_attribute_table
 */
class m180217_174954_seed_shopify_connection_attribute_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        //shopify
        $this->delete('{{%connection_attribution}}', ['connection_id' => 3]);

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [
            ["body_html", "Product description", "Description of the product. Supports HTML formatting.", 3],
            ["created_at", "Product Created Date", "Date and time when the product was created. The API returns this value in ISO 8601 format.", 3],
            ["handle", "Product Handle", "Human-friendly unique string for the product. Automatically generated from the product's title. Used by the Liquid templating language to refer to objects.", 3],
            ["id", "Product ID", "The unique numerical ID of the product. Increments sequentially.", 3],
            ["product_type", "Product type", "Categorization that a product can be tagged with, commonly used for filtering and searching.", 3],
            ["published_at", "Product Publish Date", "Date and time when the product was published to the Online Store channel. The API returns this value in ISO 8601 format. A value of null indicates that the product is not published to Online Store.", 3],
            ["title", "Product name", "Name of the product. In a shop's catalog, clicking on a product's title takes you to that product's page. On a product's page, the product's title typically appears in a large font.'", 3],
            ["updated_at", "Product Updated Date", "Date and time when the product was last modified. The API returns this value in ISO 8601 format.", 3],
            //["variants", "Product Variants", "List of variant objects, each one representing a slightly different version of the product.", 3],
            //["vendor", "Product Vendor", "Name of the vendor of the product.", 3],

        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180217_174954_seed_shopify_connection_attribute_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180217_174954_seed_shopify_connection_attribute_table cannot be reverted.\n";

        return false;
    }
    */
}
