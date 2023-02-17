<?php

use yii\db\Migration;

/**
 * Class m180214_093356_re_seed_connection_attribute_table
 */
class m180214_093356_re_seed_connection_attribute_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->delete('{{%connection_attribution}}');

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [

            ['scalable','able to be scaled','<p><span style=\"color: rgb(34, 34, 34], font-family: arial, sans-serif;\">able to be scaled ...</span><br></p>',NULL],
            ['Name',NULL,NULL,'1'],
            ['SKU',NULL,NULL,'1'],
            ['Brand',NULL,NULL,'1'],
            ['Weight',NULL,NULL,'1'],
            ['Availability',NULL,NULL,'1'],
            ['Description',NULL,NULL,'1'],
            ['Package Length',NULL,NULL,'1'],
            ['Package Height',NULL,NULL,'1'],
            ['Package Width',NULL,NULL,'1'],
            ['name',NULL,NULL,'2'],
            ['sku',NULL,NULL,'2'],
            ['description',NULL,NULL,'2'],
            ['availability_description',NULL,NULL,'2'],
            ['price',NULL,NULL,'2'],
            ['cost_price',NULL,NULL,'2'],
            ['Price',NULL,NULL,'1'],
            ['Sales_price',NULL,NULL,'1'],
            ['retail_price',NULL,NULL,'2'],
            ['sale_price',NULL,NULL,'2'],
            ['calculated_price',NULL,NULL,'2'],
            ['warranty',NULL,NULL,'2'],
            ['width',NULL,NULL,'2'],
            ['height',NULL,NULL,'2'],
            ['depth',NULL,NULL,'2'],
            ['brand',NULL,NULL,'2'],
            ['upc',NULL,NULL,'2'],
            ['primary_image',NULL,NULL,'2'],
            ['availability',NULL,NULL,'2'],
            ['title',NULL,NULL,'3'],
            ['variants/sku',NULL,NULL,'3'],
            ['vendor',NULL,NULL,'3'],
            ['tags',NULL,NULL,'3'],
            ['variants/inventory_quantity',NULL,NULL,'3'],
            ['variants/fulfillment_service',NULL,NULL,'3'],
            ['title',NULL,NULL,'4'],
            ['vendor',NULL,NULL,'4'],
            ['tags',NULL,NULL,'4'],
            ['variants/sku',NULL,NULL,'4'],
            ['variants/inventory_quantity',NULL,NULL,'4'],
            ['variants/fulfillment_service',NULL,NULL,'4'],
            ['name',NULL,NULL,'5'],
            ['short_description',NULL,NULL,'5'],
            ['sku',NULL,NULL,'5'],
            ['price',NULL,NULL,'5'],
            ['regular_price',NULL,NULL,'5'],
            ['sale_price',NULL,NULL,'5'],
            ['stock_quantity',NULL,NULL,'5'],
            ['weight',NULL,NULL,'5'],
            ['sku',NULL,NULL,'6'],
            ['weight',NULL,NULL,'6'],
            ['price',NULL,NULL,'6'],
            ['special_price',NULL,NULL,'6'],
            ['name',NULL,NULL,'6'],
            ['description',NULL,NULL,'6'],
            ['sku',NULL,NULL,'7'],
            ['name',NULL,NULL,'7'],
            ['extension_attributes/category_id',NULL,NULL,'7'],
            ['stock_item/qty',NULL,NULL,'7'],
            ['Name',NULL,NULL,'8'],
            ['BrandId',NULL,NULL,'8'],
            ['Description',NULL,NULL,'8'],
            ['Title',NULL,NULL,'8'],
            ['name',NULL,NULL,'12'],
            ['title',NULL,NULL,'12'],

        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180214_093356_re_seed_connection_attribute_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_093356_re_seed_connection_attribute_table cannot be reverted.\n";

        return false;
    }
    */
}
