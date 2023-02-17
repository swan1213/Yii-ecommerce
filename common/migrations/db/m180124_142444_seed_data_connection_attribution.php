<?php

use yii\db\Migration;

/**
 * Class m180124_142444_seed_data_connection_attribution
 */
class m180124_142444_seed_data_connection_attribution extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->batchInsert('{{%connection_attribution}}', ['name', 'label', 'description', 'connection_id'], [

            ['scalable','able to be scaled','<p><span style=\"color: rgb(34, 34, 34], font-family: arial, sans-serif;\">able to be scaled ...</span><br></p>',NULL],
            ['Name',NULL,NULL,'0'],
            ['SKU',NULL,NULL,'0'],
            ['Brand',NULL,NULL,'0'],
            ['Weight',NULL,NULL,'0'],
            ['Availability',NULL,NULL,'0'],
            ['Description',NULL,NULL,'0'],
            ['Package Length',NULL,NULL,'0'],
            ['Package Height',NULL,NULL,'0'],
            ['Package Width',NULL,NULL,'0'],
            ['name',NULL,NULL,'1'],
            ['sku',NULL,NULL,'1'],
            ['description',NULL,NULL,'1'],
            ['availability_description',NULL,NULL,'1'],
            ['price',NULL,NULL,'1'],
            ['cost_price',NULL,NULL,'1'],
            ['Price',NULL,NULL,'0'],
            ['Sales_price',NULL,NULL,'0'],
            ['retail_price',NULL,NULL,'1'],
            ['sale_price',NULL,NULL,'1'],
            ['calculated_price',NULL,NULL,'1'],
            ['warranty',NULL,NULL,'1'],
            ['width',NULL,NULL,'1'],
            ['height',NULL,NULL,'1'],
            ['depth',NULL,NULL,'1'],
            ['brand',NULL,NULL,'1'],
            ['upc',NULL,NULL,'1'],
            ['primary_image',NULL,NULL,'1'],
            ['availability',NULL,NULL,'1'],
            ['title',NULL,NULL,'2'],
            ['variants/sku',NULL,NULL,'2'],
            ['vendor',NULL,NULL,'2'],
            ['tags',NULL,NULL,'2'],
            ['variants/inventory_quantity',NULL,NULL,'2'],
            ['variants/fulfillment_service',NULL,NULL,'2'],
            ['title',NULL,NULL,'3'],
            ['vendor',NULL,NULL,'3'],
            ['tags',NULL,NULL,'3'],
            ['variants/sku',NULL,NULL,'3'],
            ['variants/inventory_quantity',NULL,NULL,'3'],
            ['variants/fulfillment_service',NULL,NULL,'3'],
            ['name',NULL,NULL,'4'],
            ['short_description',NULL,NULL,'4'],
            ['sku',NULL,NULL,'4'],
            ['price',NULL,NULL,'4'],
            ['regular_price',NULL,NULL,'4'],
            ['sale_price',NULL,NULL,'4'],
            ['stock_quantity',NULL,NULL,'4'],
            ['weight',NULL,NULL,'4'],
            ['sku',NULL,NULL,'5'],
            ['weight',NULL,NULL,'5'],
            ['price',NULL,NULL,'5'],
            ['special_price',NULL,NULL,'5'],
            ['name',NULL,NULL,'5'],
            ['description',NULL,NULL,'5'],
            ['sku',NULL,NULL,'6'],
            ['name',NULL,NULL,'6'],
            ['extension_attributes/category_id',NULL,NULL,'6'],
            ['stock_item/qty',NULL,NULL,'6'],
            ['Name',NULL,NULL,'7'],
            ['BrandId',NULL,NULL,'7'],
            ['Description',NULL,NULL,'7'],
            ['Title',NULL,NULL,'7'],
            ['name',NULL,NULL,'11'],
            ['title',NULL,NULL,'11'],

        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180124_142444_seed_data_connection_attribution cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_142444_seed_data_connection_attribution cannot be reverted.\n";

        return false;
    }
    */
}
