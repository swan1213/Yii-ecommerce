<?php

use yii\db\Migration;

/**
 * Class m180214_133452_re_seed_magento_connection_attribute_table
 */
class m180214_133452_re_seed_magento_connection_attribute_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->delete('{{%connection_attribution}}', ['connection_id' => 6]);
        $this->delete('{{%connection_attribution}}', ['connection_id' => 7]);

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [
            ["product_id", "Product ID", "Product ID", 6],
            ["sku", "Product SKU", "Product SKU", 6],
            ["set", "Product set", "Product set", 6],
            ["type", "Product type", "Product type", 6],
            ["categories", "Array of categories", "Array of categories", 6],
            ["websites", "Array of websites", "Array of websites", 6],
            ["created_at", "Created At", "Date when the product was created", 6],
            ["updated_at", "Updated At", "Date when the product was last updated", 6],
            ["type_id", "Type ID", "Type ID", 6],
            ["name", "Product name", "Product name", 6],
            ["description", "Product description", "Product description", 6],
            ["short_description", "Short description", "Short description for a product", 6],
            ["weight", "Product weight", "Product weight", 6],
            ["status", "Status", "Status of a product", 6],
            ["url_key", "Relative URL key", "Relative URL path that can be entered in place of a target path", 6],
            ["url_path", "URL path", "URL path", 6],
            ["visibility", "Visibility", "Product visibility on the frontend", 6],
            ["category_ids", "Category IDs", "Array of category IDs", 6],
            ["website_ids", "Website IDs", "Array of website IDs", 6],
            ["has_options", "Options", "Defines whether the product has options", 6],
            ["gift_message_available", "Gift message", "Defines whether the gift message is available for the product", 6],
            ["price", "Product price", "Product price", 6],
            ["special_price", "Product special price", "Product special price", 6],
            ["special_from_date", "Special From Date", "Date starting from which the special price is applied to the product", 6],
            ["special_to_date", "Special To Date",  "Date till which the special price is applied to the product", 6],
            ["tax_class_id", "Tax class ID", "Tax class ID", 6],
            ["tier_price", "Tier PriceEntity", "Array of catalogProductTierPriceEntity", 6],
            ["meta_title", "Mate title", "Mate title", 6],
            ["meta_keyword", "Meta keyword", "Meta keyword", 6],
            ["meta_description", "Meta description", "Meta description", 6],
            ["custom_design", "Custom design", "Custom design", 6],
            ["custom_layout_update", "Custom layout update", "Custom layout update", 6],
            ["options_container", "Options container", "Options container", 6],
            ["additional_attributes", "Additional attributes", "Array of additional attributes", 6],
            ["enable_googlecheckout", "Google Checkout", "Defines whether Google Checkout is applied to the product", 6]
        ]);

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [
            ["product_id", "Product ID", "Product ID", 7],
            ["sku", "Product SKU", "Product SKU", 7],
            ["set", "Product set", "Product set", 7],
            ["type", "Product type", "Product type", 7],
            ["categories", "Array of categories", "Array of categories", 7],
            ["websites", "Array of websites", "Array of websites", 7],
            ["created_at", "Created At", "Date when the product was created", 7],
            ["updated_at", "Updated At", "Date when the product was last updated", 7],
            ["type_id", "Type ID", "Type ID", 7],
            ["name", "Product name", "Product name", 7],
            ["description", "Product description", "Product description", 7],
            ["short_description", "Short description", "Short description for a product", 7],
            ["weight", "Product weight", "Product weight", 7],
            ["status", "Status", "Status of a product", 7],
            ["url_key", "Relative URL key", "Relative URL path that can be entered in place of a target path", 7],
            ["url_path", "URL path", "URL path", 7],
            ["visibility", "Visibility", "Product visibility on the frontend", 7],
            ["category_ids", "Category IDs", "Array of category IDs", 7],
            ["website_ids", "Website IDs", "Array of website IDs", 7],
            ["has_options", "Options", "Defines whether the product has options", 7],
            ["gift_message_available", "Gift message", "Defines whether the gift message is available for the product", 7],
            ["price", "Product price", "Product price", 7],
            ["special_price", "Product special price", "Product special price", 7],
            ["special_from_date", "Special From Date", "Date starting from which the special price is applied to the product", 7],
            ["special_to_date", "Special To Date",  "Date till which the special price is applied to the product", 7],
            ["tax_class_id", "Tax class ID", "Tax class ID", 7],
            ["tier_price", "Tier PriceEntity", "Array of catalogProductTierPriceEntity", 7],
            ["meta_title", "Mate title", "Mate title", 7],
            ["meta_keyword", "Meta keyword", "Meta keyword", 7],
            ["meta_description", "Meta description", "Meta description", 7],
            ["custom_design", "Custom design", "Custom design", 7],
            ["custom_layout_update", "Custom layout update", "Custom layout update", 7],
            ["options_container", "Options container", "Options container", 7],
            ["additional_attributes", "Additional attributes", "Array of additional attributes", 7],
            ["enable_googlecheckout", "Google Checkout", "Defines whether Google Checkout is applied to the product", 7]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180214_133452_re_seed_magento_connection_attribute_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_133452_re_seed_magento_connection_attribute_table cannot be reverted.\n";

        return false;
    }
    */
}
