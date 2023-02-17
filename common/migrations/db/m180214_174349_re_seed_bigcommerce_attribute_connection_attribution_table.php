<?php

use yii\db\Migration;

/**
 * Class m180214_174349_re_seed_bigcommerce_attribute_connection_attribution_table
 */
class m180214_174349_re_seed_bigcommerce_attribute_connection_attribution_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        //bigcommerce
        $this->delete('{{%connection_attribution}}', ['connection_id' => 2]);

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [
            ["id", "Product ID", "The unique numerical ID of the product. Increments sequentially.", 2],
            ["name", "Product name", "The product name.", 2],
            ["type", "Product type", "The product type. One of: physical – a physical stock unit. digital – a digital download.", 2],
            ["sku", "Product SKU", "User-defined product code/stock keeping unit (SKU).", 2],
            ["description", "Product description", "Product description, which can include HTML formatting.", 2],
            ["price", "Product Price", "The product’s price. Should include, or exclude, tax based on the store settings.", 2],
            ["sale_price", "Product Sales Price", "Sale price. If entered, this will be used instead of value in the price field when calculating the product’s cost.", 2],
            ["is_visible", "Product Visible", "Flag to determine whether or not the product should be displayed to customers browsing. If true, the product will be displayed. If false, the product will be hidden from view.", 2],
            ["inventory_level", "Product Inventory", "Current inventory level of the product. Simple inventory tracking must be enabled (see the inventory_tracking field) for this to take effect.", 2],
            ["inventory_warning_level", "Product Low Inventory Notification", "Inventory Warning level for the product. When the product’s inventory level drops below this warning level, the store owner will be sent a notification. Simple inventory tracking must be enabled (see the inventory_tracking field) for this to take effect.", 2],
            ["weight", "Product weight", "Weight of the product, which can be used when calculating shipping costs.", 2],
            ["width", "Product width", "Width of the product, which can be used when calculating shipping costs.", 2],
            ["height", "Product height", "Height of the product, which can be used when calculating shipping costs.", 2],
            ["date_created", "Product Created Date", "The date of which the product was created.", 2],
            ["date_modified", "Product Updated Date", "The date that the product was last modified.", 2],
            ["condition", "Product condition", "The product’s condition. Will be shown on the product page if the value of the is_condition_shown field is true. Possible values: New, Used, Refurbished.", 2],
            ["upc", "Product upc", "The product UPC code, which is used in feeds for shopping comparison sites.", 2],
            ["custom_url", "Product url", "Custom URL (if set) overriding the structure dictated in the store’s settings. If no custom URL is set, this will contain the default URL.", 2],
            ["availability", "Product availability", "Availability of the product. Possible values:
									available – the product can be purchased on the storefront.
									disabled - the product is listed on the storefront, but cannot be purchased.
									preorder – the product is listed for pre-orders.", 2],
            ["brand", "Product brand", "The product’s brand", 2],
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180214_174349_re_seed_bigcommerce_attribute_connection_attribution_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_174349_re_seed_bigcommerce_attribute_connection_attribution_table cannot be reverted.\n";

        return false;
    }
    */
}
