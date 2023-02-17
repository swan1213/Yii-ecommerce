<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property string $id
 * @property string $uniqueId
 * @property string $user_id
 * @property string $name
 * @property string $type
 * @property string $sku
 * @property string $url
 * @property string $upc
 * @property string $ean
 * @property string $jan
 * @property string $isbn
 * @property string $mpn
 * @property string $description
 * @property string $adult
 * @property string $age_group
 * @property string $brand
 * @property string $condition
 * @property string $gender
 * @property string $weight
 * @property string $package_length
 * @property string $package_height
 * @property string $package_width
 * @property string $package_box
 * @property int $stock_quantity
 * @property double $allocate_inventory
 * @property string $currency
 * @property string $country_code
 * @property string $stock_level
 * @property string $stock_status
 * @property int $low_stock_notification
 * @property double $price
 * @property double $sales_price
 * @property string $schedule_sales_date
 * @property string $status
 * @property string $permanent_hidden
 * @property string $stock_manage
 * @property string $warranty_type
 * @property string $warranty_period
 * @property string $translate_status
 * @property string $created_at
 * @property string $updated_at
 * @property string $published
 * @property string $hts
 *
 * @property OrderProduct[] $orderProducts
 * @property User $user
 * @property ProductAttribution[] $productAttributions
 * @property ProductCategory[] $productCategories
 * @property ProductConnection[] $productConnections
 * @property ProductImage[] $productImages
 * @property ProductVariation[] $productVariations
 */
class Product extends \yii\db\ActiveRecord
{
    const ADULT_YES = "Yes";
    const ADULT_NO = "No";

    const AGE_GROUP_Newborn = "Newborn";
    const AGE_GROUP_Infant = "Infant";
    const AGE_GROUP_Toddler = "Toddler";
    const AGE_GROUP_Kids = "Kids";
    const AGE_GROUP_Adult = "Adult";

    const STOCK_LEVEL_IN_STOCK = "In Stock";
    const STOCK_LEVEL_OUT_STOCK = "Out of Stock";

    const PRODUCT_CONDITION_NEW = "New";
    const PRODUCT_CONDITION_USED = "Used";
    const PRODUCT_CONDITION_REFURBISHED = "Refurbished";

    const STOCK_STATUS_VISIBLE = "Visible";
    const STOCK_STATUS_HIDDEN = "Hidden";

    const STATUS_ACTIVE = "active";
    const STATUS_INACTIVE = "in_active";

    const STATUS_YES = "Yes";
    const STATUS_NO = "No";

    const LOW_STOCK_NOTIFICATION = 5;

    const STOCK_MANAGE_YES = "Yes";
    const STOCK_MANAGE_NO = "No";

    const PRODUCT_PUBLISHED_YES = "Yes";
    const PRODUCT_PUBLISHED_NO = "No";

    const PRODUCT_PERMANENT_YES = "Yes";
    const PRODUCT_PERMANENT_NO = "No";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'stock_quantity', 'low_stock_notification'], 'integer'],
            [['description', 'adult', 'age_group', 'condition', 'package_box', 'stock_level', 'stock_status', 'status', 'stock_manage', 'translate_status'], 'string'],
            [['allocate_inventory', 'price', 'sales_price'], 'number'],
            [['schedule_sales_date', 'created_at', 'updated_at'], 'safe'],
            [['name', 'type', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'brand', 'warranty_type', 'warranty_period', 'hts'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 512],
            [['gender'], 'string', 'max' => 32],
            [['weight', 'currency', 'country_code'], 'string', 'max' => 64],
            [['package_length', 'package_height', 'package_width'], 'string', 'max' => 128],
            [['permanent_hidden', 'published'], 'string', 'max' => 16],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'name' => Yii::t('common', 'Name'),
            'type' => Yii::t('common', 'Type'),
            'sku' => Yii::t('common', 'Sku'),
            'url' => Yii::t('common', 'Url'),
            'upc' => Yii::t('common', 'Upc'),
            'ean' => Yii::t('common', 'Ean'),
            'jan' => Yii::t('common', 'Jan'),
            'isbn' => Yii::t('common', 'Isbn'),
            'mpn' => Yii::t('common', 'Mpn'),
            'description' => Yii::t('common', 'Description'),
            'adult' => Yii::t('common', 'Adult'),
            'age_group' => Yii::t('common', 'Age Group'),
            'brand' => Yii::t('common', 'Brand'),
            'condition' => Yii::t('common', 'Condition'),
            'gender' => Yii::t('common', 'Gender'),
            'weight' => Yii::t('common', 'Weight'),
            'package_length' => Yii::t('common', 'Package Length'),
            'package_height' => Yii::t('common', 'Package Height'),
            'package_width' => Yii::t('common', 'Package Width'),
            'package_box' => Yii::t('common', 'Package Box'),
            'stock_quantity' => Yii::t('common', 'Stock Quantity'),
            'allocate_inventory' => Yii::t('common', 'Allocate Inventory'),
            'currency' => Yii::t('common', 'Currency'),
            'country_code' => Yii::t('common', 'Country Code'),
            'stock_level' => Yii::t('common', 'Stock Level'),
            'stock_status' => Yii::t('common', 'Stock Status'),
            'low_stock_notification' => Yii::t('common', 'Low Stock Notification'),
            'price' => Yii::t('common', 'Price'),
            'sales_price' => Yii::t('common', 'Sales Price'),
            'schedule_sales_date' => Yii::t('common', 'Schedule Sales Date'),
            'status' => Yii::t('common', 'Status'),
            'permanent_hidden' => Yii::t('common', 'Permanent Hidden'),
            'stock_manage' => Yii::t('common', 'Stock Manage'),
            'warranty_type' => Yii::t('common', 'Warranty Type'),
            'warranty_period' => Yii::t('common', 'Warranty Period'),
            'translate_status' => Yii::t('common', 'Translate Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'published' => Yii::t('common', 'Published'),
            'hts' => Yii::t('common', 'Hts'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributions()
    {
        return $this->hasMany(ProductAttribution::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductConnections()
    {
        return $this->hasMany(ProductConnection::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariations()
    {
        return $this->hasMany(ProductVariation::className(), ['product_id' => 'id']);
    }

    public function getTranslationDatas()
    {
        $data = [];
        $product_translation = ProductTranslation::findAll(['product_id'=>$this->id]);
        foreach ($product_translation as $signle_translation){
            $single_data['id'] = $signle_translation->id;
            $single_data['smartling_id'] = $signle_translation->smartling_id;
            $single_data['name'] = $signle_translation->name;
            $single_data['description'] = $signle_translation->description;
            $single_data['brand'] = $signle_translation->brand;
            $single_data['override'] = $signle_translation->override;
            $smartling = Smartling::findOne(['id'=>$signle_translation->smartling_id]);
            if(!empty($smartling)){
                $user_connection = UserConnection::findOne(['id'=>$smartling->user_connection_id]);
                if(!empty($user_connection)){
                    $single_data['channel_name'] = $user_connection->getPublicName();
                    $data[] = $single_data;
                }
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getUniqueId()
    {
        return str_pad($this->primaryKey, 8, '0', STR_PAD_LEFT);
    }


    /**
     * @inheritdoc
     * @return \common\models\query\ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductQuery(get_called_class());
    }

    /**
     * Common function for Product Importing for all channels/stores
     */
    public static function productImportingCommon($product) {

        $conversion_rate = $product['conversion_rate'];

        $product_categories_ids = $product['categories'];

        $product_json_data = isset($product['json_data'])?$product['json_data']:null;
        $product_extra_fields = isset($product['extra_fields'])?$product['extra_fields']:null;

        $checkProductModel = NULL;

        if ( !isset($product['connection_product_id'])
            || empty($product['connection_product_id'])
            || $product['connection_product_id'] == '0' ){

            $product['connection_product_id'] = '0';
            $checkProductModel = Product::find()
                ->where([
                    'sku' => $product['sku'],
                    'user_id' => $product['user_id']
                ])
                ->andWhere(['<>', 'sku', ''])
                ->one();

        } else {

            $checkProductConnection = ProductConnection::findOne([
                'connection_product_id' => $product['connection_product_id'],
                'user_connection_id' => $product['user_connection_id']
            ]);

            if ( !empty($checkProductConnection) ){
                $checkConnectionPid = $checkProductConnection->product_id;

                $checkProductModel = Product::findOne([
                    'id' => $checkConnectionPid
                ]);

            }

        }

        if ( empty($checkProductModel) ){
            $checkProductModel = new Product();
        }

        if (isset($product['currency']) && !empty($product['currency'])){
            $product['price'] = number_format((float)$conversion_rate * $product['price'], 2, '.', '');
            $product['sales_price'] = number_format((float)$conversion_rate * $product['sales_price'], 2, '.', '');
        }

        $productData = [
            'Product' => $product
        ];

        if ( $checkProductModel->load($productData) && $checkProductModel->save(false) ){

            $product_id = $checkProductModel->id;


            $pConnectionModel = ProductConnection::findOne([
                'user_id' => $product['user_id'],
                'user_connection_id' => $product['user_connection_id'],
                'product_id' => $product_id,
            ]);
            if ( empty($pConnectionModel) ){
                $pConnectionModel = new ProductConnection();
            }

            $pConnectionModel->user_id = $product['user_id'];
            $pConnectionModel->user_connection_id = $product['user_connection_id'];
            $pConnectionModel->connection_product_id = $product['connection_product_id'];
            $pConnectionModel->product_id = $product_id;
            if(isset($product['extra_fields']) and isset($product['json_data'])) {
                $pConnectionModel->extra_fields = json_encode($product['extra_fields']);
                $pConnectionModel->json_data = json_encode($product['json_data']);
            }


            if( isset($product_json_data) && !empty($product_json_data) ) {
                $pConnectionModel->json_data = @json_encode($product_json_data, JSON_UNESCAPED_UNICODE);
            }

            if( isset($product_extra_fields) && !empty($product_extra_fields) ) {
                $pConnectionModel->extra_fields = @json_encode($product_extra_fields, JSON_UNESCAPED_UNICODE);
            }



            $pConnectionModel->save(false);

            $product_image = $product['images'];

            foreach ($product_image as $_image) {
                $connection_image_id = $_image['connection_image_id'];
                $product_image_url = $_image['image_url'];
                $label = $_image['label'];
                $image_position = $_image['position'];
                $base_image = $_image['base_img'];

                if ( ($connection_image_id == 0) || !isset($connection_image_id) || empty($connection_image_id) ) {
                    $connection_image_id = 0;
                    $productImageModel = ProductImage::findOne([
                        'product_id' => $product_id,
                        'link' => $product_image_url
                    ]);

                } else {
                    $productImageModel = ProductImage::findOne([
                        'product_id' => $product_id,
                        'connection_image_id' => $connection_image_id
                    ]);


                }

                if ( empty($productImageModel) ) {
                    $productImageModel = new ProductImage();

                    $productImageModel->created_at = date('Y-m-d h:i:s', strtotime($product['created_at']));
                }

                $productImageModel->user_id = $product['user_id'];
                $productImageModel->product_id = $product_id;
                $productImageModel->label = $label;
                $productImageModel->link = $product_image_url;
                $productImageModel->priority = $image_position;
                $productImageModel->connection_image_id = $connection_image_id;
                $productImageModel->status = 1;
                if ($base_image == $product_image_url) {
                    $productImageModel->default_image = ProductImage::DEFAULT_IMAGE_YES;
                }

                $productImageModel->updated_at = date('Y-m-d h:i:s', strtotime($product['updated_at']));
                $productImageModel->save(false);
            }

            foreach ($product_categories_ids as $catId) {

                $category = Category::findOne([
                    'user_connection_id' => $product['user_connection_id'],
                    'connection_category_id' => $catId
                ]);
                if ( !empty($category) ) {
                    $productCategory = ProductCategory::findOne([
                        'product_id' => $product_id,
                        'category_id' => $category->id
                    ]);

                    if (empty($productCategory)) {
                        $productCategory = new ProductCategory();

                        $productCategory->user_id = $product['user_id'];
                        $productCategory->category_id = $category->id;
                        $productCategory->product_id = $product_id;
                        $productCategory->created_at = date('Y-m-d h:i:s', strtotime($product['created_at']));
                        $productCategory->save(false);
                    }
                }
            }
            $productVariationSetId = 0;
            $optionsCount = 0;
            $product_v_SetNames = [];
            $product_v_SetValues = [];

            if ( isset($product['options_set']) && !empty($product['options_set']) ) {
                $productOptions = $product['options_set'];

                $variationSetName = "";
                $variationSetValues = "";

                foreach ($productOptions as $eachProductOption){
                    $variationName = strtoupper($eachProductOption['name']);

                    $variationDescritpion = $variationName;
                    if ( isset($product['type']) && !empty($product['type']) ){
                        $variationDescritpion .= "-" . $product['type'];
                    }
                    $variationItemModel = VariationItem::findOne([
                        'name' => $variationName,
                        'description' => $variationDescritpion,
                        'user_id' => $product['user_id']
                    ]);
                    if (empty($variationItemModel)) {
                        $variationItemModel = new VariationItem();

                        $variationItemModel->name = $variationName;
                        $variationItemModel->description = $variationDescritpion;
                        $variationItemModel->user_id = $product['user_id'];
                        $variationItemModel->save(false);
                    }

                    $productOptionItems = $eachProductOption['values'];
                    foreach ($productOptionItems as $each_item) {

                        $item_label = $each_item['label'];
                        $item_value = $each_item['value'];

                        $variationValueModel = VariationValue::findOne([
                            'variation_item_id' => $variationItemModel->id,
                            'label' => $item_label,
                            'value' => $item_value,
                            'user_id' => $product['user_id'],
                        ]);

                        if (empty($variationValueModel)) {
                            $variationValueModel = new VariationValue();

                            $variationValueModel->variation_item_id = $variationItemModel->id;
                            $variationValueModel->label = $item_label;
                            $variationValueModel->value = $item_value;
                            $variationValueModel->user_id = $product['user_id'];
                            $variationValueModel->save(false);
                        }

                        if ($variationSetValues == "") {
                            $variationSetValues = $variationValueModel->id;
                        } else {
                            $variationSetValues .= "-" . $variationValueModel->id;
                        }
                    }

                    if ($variationSetName == "") {
                        $variationSetName = $variationName;
                    } else {
                        $variationSetName .= " / " . $variationName;
                    }
                    $optionsCount ++;

                }

                $variationSet = VariationSet::findOne([
                    'name' => $variationSetName,
                    'items' => $variationSetValues
                ]);
                if (empty($variationSet)) {
                    $variationSet = new VariationSet();
                    $variationSet->name = $variationSetName;
                    $variationSet->description = $variationSetName;
                    $variationSet->items = $variationSetValues;
                    $variationSet->user_id = $product['user_id'];
                    $variationSet->item_count = $optionsCount;
                    $variationSet->save(false);
                }

                $productVariationSetId = $variationSet->id;
            }

            //Begin : Import product variations based on options value
//            ProductVariation::deleteAll([
//                'product_id' => $product_id,
//                'user_id' => $product['user_id'],
//                'user_connection_id' => $product['user_connection_id']
//            ]);

            $variationsData = $product['variations'];
            foreach ($variationsData as $each_variation) {

                //$connection_productId = $product['connection_product_id'];
                $productVariation = ProductVariation::findOne([
                    'product_id' => $product_id,
                    'user_id' => $product['user_id'],
                    'user_connection_id' => $product['user_connection_id'],
                    'connection_variation_id' => $each_variation['connection_variation_id']
                ]);

                if ( empty($productVariation) ){

                    $productVariation = ProductVariation::findOne([
                        'product_id' => $product_id,
                        'user_id' => $product['user_id'],
                        'user_connection_id' => $product['user_connection_id'],
                        'sku_value' => $each_variation['sku_value']
                    ]);
                }

                if ( empty($productVariation) ){
                    $productVariation = new ProductVariation();
                }

                $productVariation->user_id = $product['user_id'];
                $productVariation->product_id = $product_id;
                $productVariation->connection_variation_id = $each_variation['connection_variation_id'];
                $productVariation->variation_set_id = $productVariationSetId;
                $productVariation->user_connection_id = $product['user_connection_id'];

                $productVariation->sku_key = $each_variation['sku_key'];
                $productVariation->sku_value = $each_variation['sku_value'];
                $productVariation->inventory_key = $each_variation['inventory_key'];
                $productVariation->inventory_value = $each_variation['inventory_value'];
                $productVariation->allocate_inventory = $each_variation['inventory_value'];

                $productVariation->price_key = $each_variation['price_key'];

                $each_variation_price = 0;
                if (isset($each_variation['price_value']) && !empty($each_variation['price_value'])){
                    $each_variation_price = number_format((float)$conversion_rate * $each_variation['price_value'], 2, '.', '');
                }

                $productVariation->price_value = $each_variation_price;
                $productVariation->weight_key = $each_variation['weight_key'];
                $productVariation->weight_value = $each_variation['weight_value'];

                $variationOptions = $each_variation['options'];

                $productVariationName = "";
                $productVariationValues = "";
                $productVariationDesc = "";

                $optionsCount= 0;

                if ( !empty($variationOptions) ) {
                    foreach ($variationOptions as $each_v_option) {

                        $optionName = strtoupper($each_v_option['name']);
                        $optionValue = $each_v_option['value'];

                        $optionDescription = $optionName;
                        if ( isset($product['type']) && !empty($product['type']) ){
                            $optionDescription .= "-" . $product['type'];
                        }


                        $productVariationOptionItem = VariationItem::findOne([
                            'name' => $optionName,
                            'description' => $optionDescription
                        ]);

                        if (empty($productVariationOptionItem)) {

                            $productVariationOptionItem = new VariationItem();

                            $productVariationOptionItem->name = $optionName;
                            $productVariationOptionItem->description = $optionDescription;
                            $productVariationOptionItem->user_id = $product['user_id'];

                            $productVariationOptionItem->save(false);

                        }

                        $productVariationOptionValue = VariationValue::findOne([
                            'user_id' => $product['user_id'],
                            'label' => isset($optionValue['label'])?$optionValue['label']:'',
                            'value' => $optionValue['value']
                        ]);

                        if ( empty($productVariationOptionValue) ){
                            $productVariationOptionValue = new VariationValue();

                            $productVariationOptionValue->variation_item_id = $productVariationOptionItem->id;
                            $productVariationOptionValue->label = $optionValue['label'];
                            $productVariationOptionValue->value = $optionValue['value'];
                            $productVariationOptionValue->user_id = $product['user_id'];

                            $productVariationOptionValue->save(false);

                        }

                        if ($productVariationName == "") {
                            $productVariationName = $optionName . ' | ' . $optionValue['value'];
                            $productVariationDesc = $optionName . ' | ' . isset($optionValue['label'])?$optionValue['label']:$optionValue['value'];
                        } else {
                            $productVariationName .= ' ' . $optionName . ' | ' . $optionValue['value'];
                            $productVariationDesc .= '<br>' . $optionName . ' | ' . isset($optionValue['label'])?$optionValue['label']:$optionValue['value'];
                        }

                        if ($productVariationValues == "") {
                            $productVariationValues = $productVariationOptionValue->id;
                        } else {
                            $productVariationValues .= "-" . $productVariationOptionValue->id;
                        }

                        if (!in_array($productVariationOptionItem->name, $product_v_SetNames)) {
                            array_push($product_v_SetNames, $productVariationOptionItem->name);
                        }
                        if (!in_array($productVariationOptionValue->id, $product_v_SetValues)) {
                            array_push($product_v_SetValues, $productVariationOptionValue->id);
                        }

                        $optionsCount ++;
                    }
                }

                $variationModel = Variation::findOne(['items' => $productVariationValues]);
                if (empty($variationModel)) {

                    $variationModel = new Variation();
                    $variationModel->name = $productVariationName;
                    $variationModel->items = $productVariationValues;
                    $variationModel->description = $productVariationDesc;
                    $variationModel->user_id = $product['user_id'];
                    $variationModel->save(false);
                }

                $productVariation->variation_id = $variationModel->id;

                $productVariation->variation_set_id = $productVariationSetId;

                $productVariation->save(false);
            }

            if ($productVariationSetId == 0) {

                $productVarSetName = implode(' / ', $product_v_SetNames);
                $productVarSetValueStr = implode('-', $product_v_SetValues);

                $p_VariationSet = VariationSet::findOne([
                    'name' => $productVarSetName,
                    'items' => $productVarSetValueStr
                ]);
                if (empty($p_VariationSet)) {
                    $p_VariationSet = new VariationSet();
                    $p_VariationSet->name = $productVarSetName;
                    $p_VariationSet->items = $productVarSetValueStr;
                    $p_VariationSet->description = $productVarSetName;
                    $p_VariationSet->user_id = $product['user_id'];
                    $p_VariationSet->item_count = count($product_v_SetNames);
                    $p_VariationSet->save(false);
                }


                ProductVariation::updateAll(
                    [
                        'variation_set_id' => $p_VariationSet->id
                    ],
                    [
                        'variation_set_id' => 0,
                        'product_id' => $product_id,
                        'user_id' => $product['user_id'],
                        'user_connection_id' => $product['user_connection_id'],
                    ]
                );
            }
            //End : Import product variations based on options value


            return $product_id;

        }


        return null;

    }

    public static function genders()
    {
        return [
            UserProfile::GENDER_UNISEX,
            UserProfile::GENDER_MALE,
            UserProfile::GENDER_FEMALE
        ];
    }

    public static function adults()
    {
        return [
            self::ADULT_YES,
            self::ADULT_NO
        ];
    }

    public static function conditions()
    {
        return [
            self::PRODUCT_CONDITION_NEW,
            self::PRODUCT_CONDITION_USED,
            self::PRODUCT_CONDITION_REFURBISHED,
        ];
    }

    public static function agegroups()
    {
        return [
            self::AGE_GROUP_Newborn,
            self::AGE_GROUP_Infant,
            self::AGE_GROUP_Toddler,
            self::AGE_GROUP_Kids,
            self::AGE_GROUP_Adult,
        ];
    }

}
