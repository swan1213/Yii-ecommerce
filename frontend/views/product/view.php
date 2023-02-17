<?php
use common\models\ConnectionCategoryList;
use common\models\CurrencyConversion;
use common\models\CurrencySymbol;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductConnection;
use common\models\ProductTranslation;
use common\models\UserConnectionDetails;
use frontend\components\CustomFunction;
use frontend\components\Helpers;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\models\ProductImage;
use common\models\UserConnection;
use common\models\ProductCategory;
use common\models\Category;
use common\models\ProductVariation;
use common\models\ProductAttribution;
use common\models\Variation;
use common\models\VariationItem;
use common\models\VariationSet;
use common\models\Attribution;
use common\models\AttributionType;
use common\models\User;
use common\models\Fulfillment;

//$smartling_files_dir = Yii::getAlias('@smartling_files');

$this->title = 'Product Manager';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

/* Product Fields Values */
$user_id = Yii::$app->user->identity->id;
if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
    $user_id = Yii::$app->user->identity->parent_id;

$product_id = isset($product_model->id) ? $product_model->id : "";
$product_des = isset($product_model->description) ? $product_model->description : "";
//General Tab
$product_name = isset($product_model->name) ? ucfirst($product_model->name) : "";
$SKU = isset($product_model->sku) ? $product_model->sku : "";
$HTS = isset($product_model->hts) ? $product_model->hts : "";
$UPC = isset($product_model->upc) ? $product_model->upc : "";
$EAN = isset($product_model->ean) ? $product_model->ean : "";
$JAN = isset($product_model->jan) ? $product_model->jan : "";
$ISBN = isset($product_model->isbn) ? $product_model->isbn : "";
$MPN = isset($product_model->mpn) ? $product_model->mpn : "";
$adult = isset($product_model->adult) ? $product_model->adult : "no";
$age_group = isset($product_model->age_group) ? $product_model->age_group : "";
$availability = isset($product_model->stock_level) ? $product_model->stock_level : "Out of Stock";
$brand = isset($product_model->brand) ? $product_model->brand : "";
$condition = isset($product_model->condition) ? $product_model->condition : "New";
$gender = isset($product_model->gender) ? $product_model->gender : "Unisex";
//echo var_dump($product_model->weight); die;
$weight = isset($product_model->weight) && $product_model->weight ? number_format($product_model->weight, 2) : 0;
$package_length = isset($product_model->package_length) ? $product_model->package_length : "";
$package_height = isset($product_model->package_height) ? $product_model->package_height : "";
$package_width = isset($product_model->package_width) ? $product_model->package_width : "";
$package_box = isset($product_model->package_box) ? $product_model->package_box : "";

//Variations Tab
$var_set_id = isset($product_model->id) ? $product_model->id : "";
$op_set_object = VariationSet::find()->where(['id' => $var_set_id])->one();
$op_set = '';
$op_ids_arr = array();
if (!empty($op_set_object)):
    $op_set = $op_set_object->name;
    $op_ids_arr = explode("-", $op_set_object->items);
endif;

$stk_qty = isset($product_model->stock_quantity) ? $product_model->stock_quantity : 0;
$stk_lvl = isset($product_model->stock_level) ? $product_model->stock_level : 'Out of Stock';
$stk_status = isset($product_model->stock_status) ? $product_model->stock_status : 'Visible';
$low_stk_ntf = isset($product_model->low_stock_notification) ? $product_model->low_stock_notification : '';


$user = Yii::$app->user->identity;

$selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
if (isset($selected_currency) and ! empty($selected_currency)) {
    $currency_symbol = $selected_currency['symbol'];
}
$price = isset($product_model->price) ? number_format((float) $product_model->price, 2) : 0;
$sale_price = isset($product_model->sales_price) && $product_model->sales_price ? number_format((float) $product_model->sales_price, 2) : 0;
$schedule_date1 = date('Y-m-d', time());
$schedule_date2 = isset($product_model->schedule_sales_date) ? $product_model->schedule_sales_date : '';
//Translation Tab
//product default image
$Product_images = ProductImage::find()->Where(['product_id' => $product_id, 'user_id' => $user_id])->orderBy(['priority' => SORT_ASC])->all();
$Product_images_count = count($Product_images);

$store_connection = UserConnection::find()->where(['user_id' => $user_id])->available()->all();
$connections = array();

foreach ($store_connection as $con) {
    $connections[] = $con->getPublicName();
}

//User For Categoreis
$product_cat = ProductCategory::find()->Where(['product_id' => $product_id])->all();

if (!empty($product_cat)) :
    $val = '';
    $name = '';

    foreach ($product_cat as $pcat_data) :
        $category_data = Category::find()->Where(['id' => $pcat_data->category_id])->one();

        $cat_name = !empty($category_data) ? $category_data->name : '';
        $cat_id = !empty($category_data) ? $category_data->id : '';
        //$arr_data = [];
        $arr_data[] = array('value' => $cat_id, 'name' => $cat_name);
        $val .= $cat_id . ",";
        $name .= $cat_name . ",";

    endforeach;
    $cat_data_source = rtrim($val, ',');
    $cat_name_source1 = trim($name, ",");
    $cat_name_source = str_replace(",", "<br>", $cat_name_source1);
endif;

/* For Display Coonected Channel in channel manager */
$productChannel = Product::find()->where(['id' => $product_model->id])->one();
$product_connections = $productChannel->productConnections;
$names = array();
$name_with_country = array();
$check_id_list_value = array();
$store_id = $channel_ids = '';
$sm_channel_country = $translation_status='';

//$value_to_display = isset($names) && $names ? implode(",", $names) : '';
foreach ($product_connections as $product_connection) {
    if ($product_connection->status == ProductConnection::STATUS_YES) {
        $name_with_country[] = $product_connection->userConnection->getPublicName();
        $check_id_list_value[] = $product_connection->user_connection_id;
    }
}
$translated_data = $productChannel->getTranslationDatas();
$value_to_display = isset($name_with_country) && $names ? implode(",", $name_with_country) : '';

$value_check_list_display = isset($check_id_list_value) ? implode(",", $check_id_list_value) : '';

/* For Stock Status */
$stock_status_arr = Product::find()->where(['id' => $product_id])->all();
$visible_data_arr = [];
$not_visible_data_arr = [];
if (!empty($stock_status_arr)) {
    foreach ($stock_status_arr as $status_arr) {
        if ($status_arr->status == "yes") {
            $visible_data_arr[] = 'yes';
        } else {
            $not_visible_data_arr[] = 'no';
        }
    }
}
$stock_status_test = "Featured in " . count($visible_data_arr) . " Channels, Hidden in " . count($not_visible_data_arr) . " Channels";


/* For Stock Level */
$stock_levael_arr = Product::find()->where(['id' => $product_id])->all();
$in_data_arr = [];
$out_data_arr = [];
if (!empty($stock_levael_arr)) {
    foreach ($stock_levael_arr as $stock_level_arr) {
        if ($stock_level_arr->stock_level == "In Stock") {
            $in_data_arr[] = 'In';
        } else {
            $out_data_arr[] = 'out';
        }
    }
}
$stock_level_test = "In Stock in " . count($in_data_arr) . " Channels, Out of Stock in " . count($out_data_arr) . " Channels";

function isValidPermission() {

    if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
        return CustomFunction::checkPermissionOther(1);
    }
    return true;
}
?>
<style type="text/css">
    #lazada_category_modal .category-search {
        max-height: 45rem;
        overflow: auto;
        border: 1px solid #ccc;
    }

    #lazada_category_modal .category-search .list-group.list-group-root {
        padding: 0;
        overflow: hidden;
    }

    .list-group.list-group-root .list-group {
        margin-bottom: 0;
    }

    .list-group.list-group-root > .list-group > .list-group-item {
        padding-left: 30px;
    }

    .list-group.list-group-root > .list-group > .list-group > .list-group-item {
        padding-left: 45px;
    }

    .list-group.list-group-root > .list-group > .list-group > .list-group > .list-group-item {
        padding-left: 60px;
    }

    .list-group.list-group-root > .list-group > .list-group > .list-group > .list-group > .list-group-item {
        padding-left: 75px;
    }

    #lazada_category_modal .category-search .list-group.list-group-root .list-group-item {
        border-radius: 0;
        border-width: 1px 0 0 0;
    }

    #lazada_category_modal .category-search .list-group.list-group-root > .list-group-item:first-child {
        border-top-width: 0;
    }

    #lazada_category_modal .category-search .list-group-item .glyphicon {
        margin-right: 5px;
    }

    #lazada_category_modal .modal-header {
        display: flex;
        flex-flow: row;
        justify-content: space-between;
    }

    #lazada_category_modal .modal-header .modal-title  {
        flex: 1;
    }

    .lazada-category #lazada_category {
        padding: 1em;
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        min-height: 50px;
        height: 100%;
    }

    .lazada-category #lazada_category .list-group .list-group-item.cur {
        background: #f4f4f4;
    }

    .lazada-category .list-group-item i.glyphicon {
        margin-right: 0.5em;
    }

    .lazada-category #lazada_category .list-group .list-group-item.cur.checked:before {
        font-family: FontAwesome;
        content: "✓";
        font-weight: bold;
        margin-right: 0.5em;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <input type="hidden" id="pID_created" value="<?= $product_id; ?>"/>
        <div class="single_product_main_img">
            <?php
            if (!empty($Product_images)) {
                $default_product_image = ProductImage::find()->Where(['product_id' => $product_id, 'default_image' => 'Yes'])->one();
                if (empty($default_product_image)):
                    $default_product_image = ProductImage::find()->Where(['product_id' => $product_id, 'priority' => 1])->one();
                endif;
                $product_image_link = '';
                if (isset($default_product_image->link) && !empty($default_product_image->link))
                    $product_image_link = $default_product_image->link;
                ?>
                <div class="bs-grid-block product_image">
                    <div class="content">
                        <!--Starts Change cover image !-->
                        <div class="user-display-bg product-texthover p_default_img" style="background-image:url('<?= $product_image_link ?>');">
                            <!--<img src="<?= $product_image_link ?>"  alt="Profile Background" id="product_image_dropzone1"  class="custom_drozone_css dropzone">-->
                            <div class="product-overlay custom_drozone_css dropzone span-product-image-css" id="product_image_dropzone"><br />
                                <span class="panel-heading profile-panel-heading" ></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="bs-grid-block product_image product-texthover-default">
                    <!--Starts Change cover image !-->
                    <div class="user-display-bg">
                        <div class="content dropzone" id="product_image_dropzone-default1"><span class="size span-add-product-text">Add Product Image</span></div>
                        <div class="product-default-overlay custom_drozone_css dropzone span-product-image-css" id="product_image_dropzone-default"><br />
                            <span class="panel-heading profile-panel-heading" ></span>
                        </div>
                    </div>
                </div>

            <?php } ?>
        </div>
        <div class="pdetail_head">
            <div class="page-head product_detail">
                <h2 class="page-head-title"><?= Html::encode($product_name) ?></h2>
                <ol class="breadcrumb page-head-nav">
                    <?php echo Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],]); ?>
                </ol>
            </div>


            <div class="pdes">
                <div><?php //echo preg_replace("/<img[^>]+\>/i", "", $product_des);      ?></div>
            </div>
        </div>
        <div class="up_btn_div">
            <?php if (isValidPermission()) { ?>
                <button class="btn btn-space btn-primary" onclick="updateProduct()">Publish</button>
            <?php } ?>
        </div>
        <input type="hidden" id="product_id" value="<?php echo $product_model->id; ?>"/>
    </div>
</div>

<div class="row">
    <!--Default Tabs-->
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="tab-container">
                <ul class="nav nav-tabs">
                    <li class="active customCC"><a href="#general" data-toggle="tab">General Information</a></li>
                    <li class="customCC"><a href="#variations" data-toggle="tab">Variations</a></li>
                    <li class="customCC"><a href="#attribution" data-toggle="tab">Attribution</a></li>
                    <li class="customCC"><a href="#categories" data-toggle="tab">Categories</a></li>
                    <li class="customCC"><a href="#ch_mngr" data-toggle="tab">Channel Manager</a></li>
                    <li class="customCC"><a href="#invt_mngr" data-toggle="tab">Inventory Management</a></li>
                    <li class="customCC"><a href="#media" data-toggle="tab">Media</a></li>
                    <li class="getLiGraph"><a href="#performance" data-toggle="tab">Performance</a></li>
                    <li class="customCC"><a href="#pricing" data-toggle="tab">Pricing</a></li>
                    <?php if(count($translated_data)>0){ ?>
                        <li><a href="#translation" data-toggle="tab">Translation</a></li>
                    <?php } ?>
                    <?php if(!empty($Connect_lazada)){ ?>
                        <li class="customCC"><a href="#Warranties" data-toggle="tab">Warranties</a></li>
                    <?php } ?>

                </ul>
                <div class="tab-content product_details">
                    <div id="general" class="tab-pane active cont">
                        <div class="table-responsive">
                            <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <td width="35%">Name</td>
                                    <td width="65%"><a id="product_name" href="#" data-type="text" data-title="Please Enter value"><?php echo $product_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">SKU</td>
                                    <td width="65%"><a id="SKU" href="#" data-type="text" data-title="Please Enter value (must be unique)" ><?php echo $SKU; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">HTS</td>
                                    <td width="65%"><a id="HTS" href="#" data-type="text" data-title="Please Enter value (must be unique)" ><?php echo $HTS; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">UPC</td>
                                    <td width="65%"><a id="UPC" href="#" data-type="text" data-title="Please Enter value (must be unique)" ><?php echo $UPC; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">EAN</td>
                                    <td width="65%"><a id="EAN" href="#" data-type="text" data-title="Please Enter value (must be unique)"><?php echo $EAN; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">JAN</td>
                                    <td width="65%"><a id="JAN" href="#" data-type="text" data-title="Please Enter value (must be unique)"><?php echo $JAN; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">ISBN</td>
                                    <td width="65%"><a id="ISBN" href="#" data-type="text" data-title="Please Enter value (must be unique)"><?php echo $ISBN; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">MPN</td>
                                    <td width="65%"><a id="MPN" href="#" data-type="text" data-title="Please Enter value (must be unique)"><?php echo $MPN; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Adult</td>
                                    <td><a id="adult" data-title="Please Select" data-value="<?php echo $adult; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <tr>
                                    <td>Age Group</td>
                                    <td><a id="age_group" data-title="Please Select" data-value="<?php echo $age_group; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <tr>
                                    <td>Availability</td>
                                    <td><a id="availability" data-title="Please Select" data-value="<?php echo $availability; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Brand</td>
                                    <td width="65%"><a  id="brand" href="javascript:"><?php echo Yii::$app->user->identity->company; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Condition</td>
                                    <td><a id="condition" data-title="Please Select" data-value="<?php echo $condition; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td><a id="gender" data-title="Please Select" data-value="<?php echo $gender; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Weight (lbs)</td>
                                    <td width="65%"><a id="weight" href="#" data-type="text" data-title="Please Enter value"><?php echo $weight; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Package Length (cm)</td>
                                    <td width="65%"><a id="package_length" href="#" data-type="text" data-title="Please Enter value"><?php echo $package_length; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Package Height (cm)</td>
                                    <td width="65%"><a id="package_height" href="#" data-type="text" data-title="Please Enter value"><?php echo $package_height; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Package Width (cm)</td>
                                    <td width="65%"><a id="package_width" href="#" data-type="text" data-title="Please Enter value"><?php echo $package_width; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">What's in the box</td>
                                    <td width="65%"><a id="package_box" href="#" data-type="text" data-title="Please Enter value"><?php echo $package_box; ?></a></td>
                                </tr>

                                <tr>
                                    <td width="35%">Description</td>
                                    <td width="65%">
                                        <div id="product-update-description"><?= $product_des ?></div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="variations" class="tab-pane cont">
                        <div class="table-responsive">
                            <div id="variations" class="tab-pane cont">
                                <div class="table-responsive">
                                    <div class="col-sm-12 skus be-loading">
                                        <table id="skus_tbl" width="100%" class="table table-condensed table-hover table-bordered table-striped">
                                            <?php
                                            //                                                $connection = \Yii::$app->db;
                                            //                                                $query = "SELECT *, SUM(inventory_value) FROM product_variation WHERE product_id={$product_model->id} AND user_id={$user_id} AND user_connection_id > 0 GROUP BY sku_value";
                                            //                                                $query_run = $connection->createCommand($query);
                                            //                                                $product_variants_rows = $query_run->queryAll();
                                            $product_variants_rows = ProductVariation::find()->where(['product_id' => $product_id, 'user_id' => $user_id])
                                                ->groupBy('sku_value')->distinct()->all();
                                            if(count($product_variants_rows) == 0){
                                            ?>
                                            <thead>
                                            <tr>
                                                <th colspan="4" class="pskus_th">
                                                    Options & SKUs
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>SKU</th>
                                                <th>Inventory</th>
                                                <th>Price</th>
                                                <th>Weight</th>
                                                <!--th>Actions</th-->
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <tr class="odd"><td valign="top" colspan="4" style="text-align:center;" class="dataTables_empty">No data available in table</td></tr>
                                            <!--<a href="/variations/create"><button class="btn btn-space btn-primary" >Add Variation</button></a>-->
                                            <?php } else {
                                            $skuRowIndex = 0;
                                            $tplHeaderColumnCount = 0;
                                            $tplHeaderColumnStr = "";

                                            foreach ($product_variants_rows as $variation_row){
                                            $variation_price = 0;
                                            if (!empty($variation_row->price_value) && ($variation_row->price_value != "Empty"))
                                                $variation_price = $variation_row->price_value;
                                            else if (!empty($product_model->price))
                                                $variation_price = $product_model->price;
                                            $variation_weight = 0;
                                            if (!empty($variation_row->weight_value) && ($variation_row->weight_value != "Empty"))
                                                $variation_weight = $variation_row->weight_value;
                                            else if (!empty($product_model->weight))
                                                $variation_weight = $product_model->weight;

                                            if ( $skuRowIndex == 0 ) {
                                            $tplHeadContent = ProductVariation::getVariationSetTypeNames($variation_row->variation_set_id);
                                            foreach ($tplHeadContent as $tplHeadColumns){
                                                $tplHeaderColumnStr .= "<th width='18%'>".$tplHeadColumns."</th>";
                                                $tplHeaderColumnCount ++;
                                            }
                                            ?>
                                            <thead>
                                            <tr>
                                                <th colspan="<?=$tplHeaderColumnCount+4?>" class="pskus_th">
                                                    Options & SKUs
                                                </th>
                                            </tr>
                                            <tr>
                                                <th width="28%">SKU</th>
                                                <?=$tplHeaderColumnStr?>
                                                <th width="18%">Inventory</th>
                                                <th width="18%">Price</th>
                                                <th width="18%">Weight</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            }
                                            ?>
                                            <tr id="<?= $variation_row['id']; ?>">
                                                <!--SKU-->
                                                <td>
                                                    <?php if (empty($variation_row->sku_value) || $variation_row->sku_value == "Empty") { ?>
                                                        <a class="pvarSKU" id="variation_sku_<?=$variation_row->id?>" data-key="<?=$variation_row->id?>" data-field="sku"  href="#" data-type="text" data-title="Variation SKU" ><?php echo !empty($variation_row->sku_value)? $variation_row->sku_value: '-';  ?></a>
                                                    <?php } else { ?>
                                                        <a class="pvarSKUNoEdit" href="#" data-type="text" data-title="Variation SKU" ><?php echo !empty($variation_row->sku_value)? $variation_row->sku_value: '-';  ?></a>
                                                    <?php } ?>
                                                </td>
                                                <!--Type-->
                                                <?php
                                                for($varIndex = 0 ; $varIndex < $tplHeaderColumnCount ; $varIndex ++) {
                                                    ?>
                                                    <td>
                                                        <a class="variation-item" href="#" data-type="text" data-title="Variation Type" ><?php
                                                            $product_variant_row = ProductVariation::find()->where(['id' => $variation_row->id])->one();
                                                            echo $product_variant_row->getVariationItems($varIndex)?></a>
                                                    </td>
                                                    <?php
                                                }
                                                ?>
                                                <!--Inventory-->
                                                <td>
                                                    <a data-toggle="modal" id="variation_stock_<?=$variation_row->id?>" data-target="#form-stock-qty" href="#" data-type="text" data-title="Variation Inventory" class="custom-dyn-modal" data-modal-type="product-variation" data-key="<?=$variation_row->id?>" data-value="<?=$variation_row->inventory_value?>"><?= !empty($variation_row)? $variation_row->inventory_value: '-'; ?></a>
                                                    <!--                                                            <a class="pvarInventory" href="#" data-type="text" data-title="Variation Inventory" ></a>-->
                                                </td>
                                                <!--Price-->
                                                <td>
                                                    <a class="pvarPrice" id="variation_price_<?=$variation_row->id?>" data-key="<?=$variation_row->id?>" data-field="price" href="#" data-type="text" data-title="Variation Price" ><?= number_format(($variation_price), 2); ?></a>
                                                </td>
                                                <!--Weight-->
                                                <td>
                                                    <a class="pvarWeight" id="variation_weight_<?=$variation_row->id?>" data-key="<?=$variation_row->id?>" data-field="weight" href="#" data-type="text" data-title="Variation Weight" ><?=  number_format($variation_weight, 2); ?></a>
                                                </td>
                                                <!--td><a class="pvarActions_update icon" href="#" title="Remove Variant"><i class="mdi mdi-delete"></i></a></td-->
                                            </tr>
                                            <?php
                                            $skuRowIndex ++;
                                            }
                                            }
                                            ?>
                                            </tbody>
                                        </table>

                                        <div class="be-spinner">
                                            <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                                <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <?php //} else { ?>
                                    <!--<a href="/variations/create"><button class="btn btn-space btn-primary" >Add Variation</button></a>-->
                                    <?php //} ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="attribution" class="tab-pane cont">
                        <div class="table-responsive">
                            <input type="hidden" id="gcatname" value="" />
                            <table id="attr_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <th width="35%">Google Shopping</th>
                                    <th width="65%"></th>
                                </tr>
                                <tr>
                                    <td>Google Shopping 1</td>
                                    <td><a id="cat1" data-title="Please Enter Value" data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click editable-unsaved"><?= '' ?></a></td>
                                </tr>
                                <tr>
                                    <td>Google Shopping 2</td>
                                    <td><a id="cat2" data-title="Please Enter Value" data-placement="right" data-pk="1" data-type="select" href="#" data-original-title="" title="" class="editable editable-click editable-unsaved"><?= '' ?></a></td>
                                </tr>
                                <!-- <tr>
                                     <td>Google Shopping 3</td>
                                     <td><a id="cat3" data-title="Please Enter Value" data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"></a></td>
                                 </tr>-->
                                <tr>
                                    <th width="35%">Contextual Data</th>
                                    <th width="65%">
                                        <?php if (empty($attribute_types)) {?>
                                            <a class="btn btn-space btn-primary" href="/attribute-type/create"><i class="icon icon-left mdi mdi-plus"></i>Add an Attribute Type</a>
                                        <?php } ?>
                                    </th>
                                </tr>
                                <?php if (!empty($attribute_types)) { ?>
                                    <?php
                                    foreach ($attribute_types as $attribute_type) {
                                        $attrs = Attribution::find()->where(['attribution_type' => $attribute_type->id, 'user_id' => $user_id])->all();
                                        ?>
                                        <tr>
                                            <td><?= ucfirst($attribute_type->name) ?></td>
                                            <?php if (empty($attrs)) { ?>
                                                <td>
                                                    <a class="btn btn-space btn-primary" href="/attributes/create"><i class="icon icon-left mdi mdi-plus"></i>Add an Attribute</a>
                                                </td>
                                            <?php } else { ?>
                                                <td><a class="up_context_data" data-title="Please Select" data-value="" data-type="checklist" href="#" class="editable editable-click" data-source="get-attributes?attr_type=<?= $attribute_type->id ?>"></a></td>
                                            <?php } ?>
                                        </tr>
                                    <?php }
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--For Categories Section !-->
                    <div id="categories" class="tab-pane be-loading">
                        <div class="table-responsive">
                            <!--                                <a href="/categories/create" target="_blank" class="btn btn-space btn-primary">-->
                            <!--                                    <i class="icon icon-left mdi mdi-plus"></i>-->
                            <!--                                </a>-->
                            <table id="cats_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <td width="35%">Categories</td>
                                    <td width="65%"><a id="cats_list" data-title="Please Enter Value" data-value="<?php echo isset($cat_data_source) ? $cat_data_source : ''; ?>" data-type="checklist" href="#" class="editable editable-click" data-source="get-cats"><?php echo isset($cat_name_source) ? $cat_name_source : ''; ?></a></td>    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="channel-categories">
                            <div class="channel-group lazada-category">
                                <a href="#lazada_category" class="list-group-item" data-toggle="collapse">
                                    <i class="glyphicon glyphicon-chevron-right"></i>Lazada
                                </a>
                                <div class="collapse container-fluid be-loading" id="lazada_category">
                                    <div class="be-spinner">
                                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                        </svg>
                                    </div>
                                    <div class="row category-area">
                                        <div class="col-sm-4">
                                            Select one category
                                        </div>

                                        <div class="col-sm-8">
                                            <div class="list-group list-group-root">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End For Categories Section !-->
                    <div id="ch_mngr" class="tab-pane">
                        <div class="table-responsive">
                            <a href="/channels/create" target="_blank" class="btn btn-space btn-primary">
                                <i class="icon icon-left mdi mdi-plus"></i>
                                Add New Channel
                            </a>
                            <table id="channel_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <td width="35%">Select Channel for Sale</td>
                                    <td width="65%"><a id="channelsonproductview" data-inputclass="channel-list" data-title="Please Enter Value" data-value="<?php echo isset($value_check_list_display) ? $value_check_list_display : ''; ?>" data-type="checklist" href="#" class="editable editable-click" data-source="getchannel"><?php echo isset($value_to_display) ? $value_to_display : ''; ?></a></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="invt_mngr" class="tab-pane">
                        <div class="table-responsive">
                            <table id="invt_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <td width="35%">Stock Qty</td>
                                    <!--<td width="65%"><a id="stk_qty" href="#" data-type="text" data-title="Please Enter value"><?php //echo $stk_qty;        ?></a></td>-->
                                    <td width="65%">
                                        <a data-toggle="modal" data-target="#form-stock-qty" class="custom-org-modal" href="#" data-type="text" data-title="Please Enter value">Click to Modify Allocation by Channel</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Stock Level</td>
                                    <!--<td><a id="stk_lvl" data-title="Please Select" data-value="<?php //echo $stk_lvl;        ?>" data-pk="1" data-type="select" href="#"></a></td>-->
                                    <td width="65%"><a data-toggle="modal" data-target="#form-stock-level" href="#"><?= $stock_level_test; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Stock Status</td>
                                    <!--<td><a id="stk_status" data-title="Please Select" data-value="<?php //echo $stk_status;        ?>" data-pk="1" data-type="select" href="#"></a></td>-->
                                    <td width="65%"><a data-toggle="modal" data-target="#form-stock-status" href="#"><?= $stock_status_test; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Low Stock Notification</td>
                                    <td width="65%"><a id="low_stk_ntf" href="#" data-type="text" data-title="Please Enter value"><?php echo $low_stk_ntf; ?></a></td>
                                    <!--<td width="65%"><a data-toggle="modal" data-target="#form-stock-notification" href="#">#</a></td>-->
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="media" class="tab-pane">
                        <div class="row pmedia pmedia-update">
                            <input type="hidden" value="<?= $Product_images_count; ?>" id="product_imgDivCount"/>
                            <?php
                            if ($Product_images_count == 0):
                                $more_divs_count = 4;
                            elseif ($Product_images_count == 1):
                                $more_divs_count = 3;
                            elseif ($Product_images_count == 2):
                                $more_divs_count = 2;
                            elseif ($Product_images_count == 3):
                                $more_divs_count = 1;
                            else:
                                $more_divs_count = 0;
                            endif;
                            $i = 0;
                            foreach ($Product_images as $p_image):
                                $i++;
                                $p_image_id = $p_image->id;
                                $p_image_link = $p_image->link;
                                $p_image_label = $p_image->label;
                                $p_image_order = $p_image->priority;
                                $p_image_default = $p_image->default_image;
                                $radio_check = '';
                                if ($p_image_default == 'Yes'):
                                    $radio_check = 'checked';
                                elseif ($p_image_order == 1):
                                    $radio_check = 'checked';
                                endif;
                                ?>
                                <div class="col-sm-3 pimg-div<?= $i ?> be-loading draggable-element" id="pimgDiv_<?= $i ?>">

                                    <div class="bs-grid-block product_update_image product-texthover-default">
                                        <div class="user-display-bg product-texthover">
                                            <img src="<?= $p_image_link ?>"  alt="Profile Background" id="product_image_dropzone1"  height="300" class="custom_drozone_css dropzone">
                                            <div class="product-overlay custom_drozone_css dropzone span-product-image-css pimgUpdate_dropzone" id="pImageDrop_<?php echo $i; ?>"><br />
                                                <span class="panel-heading profile-panel-heading" ></span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="upld_img<?php echo $i; ?>" class="upld_img" value="<?= $p_image_id; ?>">
                                    <div class="setimg">
                                        <i class="icon icon-left mdi mdi-image"></i>
                                        Set as Default Image
                                        <div class="be-radio">
                                            <input <?= $radio_check ?> name="pimg_radio" id="pimgRad_<?php echo $i; ?>" type="radio">
                                            <label for="pimgRad_<?php echo $i; ?>"></label>
                                        </div>
                                    </div>
                                    <table id="pimg_tbl<?php echo $i; ?>" style="clear: both" class="table table-striped table-borderless">
                                        <tbody>
                                        <tr>
                                            <td width="45%">Image Label</td>
                                            <td width="55%"><a id="pimg_lbl<?php echo $i; ?>" class="pimg_lbl" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                        </tr>
                                        <tr>
                                            <td width="45%">Alt Tag</td>
                                            <td width="55%"><a id="pimg_alt_tag<?php echo $i; ?>" class="pimg_alt_tag" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                        </tr>
                                        <tr>
                                            <td width="45%">HTML Video Link</td>
                                            <td width="55%"><a id="pimg_html_video<?php echo $i; ?>" class="pimg_html_video" href="#" data-type="text" data-title="Please Enter value(Only support Youtube and Vimeo)"></a></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="vfile">
                                        <input type="file" name="pimg_360_video" id="pimg_360_video<?php echo $i; ?>" data-multiple-caption="{count} files selected" multiple class="inputfile" accept="video/*" >
                                        <label for="pimg_360_video<?php echo $i; ?>" class="btn-default"> <i class="mdi mdi-upload"></i>
                                            <span>Select 360-degree video</span>
                                        </label>
                                    </div>
                                    <div class="progress" id="pimg_360_video_progress_wrapper<?php echo $i; ?>">
                                        <div id="pimg_360_video_progress<?php echo $i; ?>" class="progress-bar progress-bar-primary progress-bar-striped"></div>
                                    </div>
                                    <div class="vupld">
                                        <button id="vupldbtn_<?php echo $i; ?>" class="btn btn-rounded btn-space btn-default vupld_btn">Upload</button>
                                    </div>
                                    <div class="pimg_save_btns">
                                        <button id="pimgSaveBtn_<?php echo $i; ?>" class="btn btn-space btn-primary btn-sm pimg_save_btn">Save</button>
                                    </div>
                                    <div class="be-spinner">
                                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                        </svg>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php
                            for ($a = 1; $a <= $more_divs_count; $a++):
                                $i++;
                                ?>
                                <div class="col-sm-3 pimg-div<?= $i ?> be-loading draggable-element" id="pimgDiv_<?= $i ?>">
                                    <div class="bs-grid-block product-texthover-default product_create_image">
                                        <div class="user-display-bg">
                                            <div class="content dropzone pImg_Create_Drop1" id="pImageDrop_<?php echo $i; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="upld_img<?php echo $i; ?>" class="upld_img" value="">
                                    <div class="setimg">
                                        <i class="icon icon-left mdi mdi-image"></i>
                                        Set as Default Image
                                        <div class="be-radio">
                                            <input <?= isset($radio_check) ? $radio_check : ''; ?> name="pimg_radio" id="pimgRad_<?php echo $i; ?>" type="radio">
                                            <label for="pimgRad_<?php echo $i; ?>"></label>
                                        </div>
                                    </div>
                                    <table id="pimg_tbl<?php echo $i; ?>" style="clear: both" class="table table-striped table-borderless">
                                        <tbody>
                                        <tr>
                                            <td width="45%">Image Label</td>
                                            <td width="55%"><a id="pimg_lbl<?php echo $i; ?>" class="pimg_lbl" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                        </tr>
                                        <tr>
                                            <td width="45%">Alt Tag</td>
                                            <td width="55%"><a id="pimg_alt_tag<?php echo $i; ?>" class="pimg_alt_tag" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                        </tr>
                                        <tr>
                                            <td width="45%">HTML Video Link</td>
                                            <td width="55%"><a id="pimg_html_video<?php echo $i; ?>" class="pimg_html_video" href="#" data-type="text" data-title="Please Enter value(Only support Youtube and Vimeo)"></a></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="vfile">
                                        <input type="file" name="pimg_360_video" id="pimg_360_video<?php echo $i; ?>" data-multiple-caption="{count} files selected" multiple class="inputfile" accept="video/*" >
                                        <label for="pimg_360_video<?php echo $i; ?>" class="btn-default"> <i class="mdi mdi-upload"></i>
                                            <span>Select 360-degree video</span>
                                        </label>
                                    </div>
                                    <div class="progress" id="pimg_360_video_progress_wrapper<?php echo $i; ?>">
                                        <div id="pimg_360_video_progress<?php echo $i; ?>" class="progress-bar progress-bar-primary progress-bar-striped"></div>
                                    </div>
                                    <div class="vupld">
                                        <button id="vupldbtn_<?php echo $i; ?>" class="btn btn-rounded btn-space btn-default vupld_btn">Upload</button>
                                    </div>
                                    <div class="pimg_save_btns">
                                        <button id="pimgSaveBtn_<?php echo $i; ?>" class="btn btn-space btn-primary btn-sm pimg_save_btn">Save</button>
                                    </div>
                                    <div class="be-spinner">
                                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                        </svg>
                                    </div>
                                </div>
                            <?php endfor; ?>
                            <input type="hidden" value="<?= $i; ?>" id="imgDivCount"/>

                            <div class="addimg">
                                <button class="btn btn-space btn-primary addimg-btn">
                                    <i class="icon icon-left mdi mdi-plus"></i>
                                    Add More
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="performance" class="tab-pane">
                        <!--For Grpah plots!-->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="widget widget-fullwidth be-loading PerformanceTab">
                                    <div class="widget-head">
                                        <div class="tools">
                                            <div class="dropdown"><span data-toggle="dropdown" class="icon mdi mdi-more-vert visible-xs-inline-block dropdown-toggle"></span>
                                                <ul role="menu" class="dropdown-menu">
                                                    <li class="singleProductGraph" data-id="day"><a href="javscript:" >Daily</a></li>
                                                    <li  class="singleProductGraph" data-id="week"><a href="javscript:">Weekly</a></li>
                                                    <li class="singleProductGraph" data-id="month"><a href="javscript:" >Monthly</a></li>
                                                    <li class="singleProductGraph" data-id="quarter"><a href="javscript:" >Quarterly</a></li>
                                                    <li class="divider"></li>
                                                    <li class="singleProductGraph" data-id="year"><a href="javscript:" >Annually</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="button-toolbar hidden-xs">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default   singleProductGraph" data-id="day">Daily</button>
                                                <button type="button" class="btn btn-default   singleProductGraph" data-id="week">Weekly</button>
                                                <button type="button" class="btn btn-default  active singleProductGraph"  data-id="month">Monthly</button>
                                                <button type="button" class="btn btn-default  singleProductGraph"  data-id="quarter">Quarterly</button>
                                                <button type="button" class="btn btn-default  singleProductGraph" data-id="year">Yearly</button>

                                                <input type="hidden" name="" value="<?= $product_model->id ?>" id="hidden_product_id">
                                            </div>
                                        </div>
                                        <?php
                                        $connected_data = UserConnection::find()->Where(['user_id' => $user_id])->available()->asArray()->all();
                                        ?>
                                        <?php if (empty($connected_data)): ?>
                                            <span class="title">Connect Your Store</span>
                                        <?php else: ?>
                                            <span class="title">Global Performance</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="widget-chart-container">
                                        <div class="widget-chart-info">
                                            <ul id="chartInfoProduct" class="chart-legend-horizontal">
                                            </ul>
                                        </div>
                                        <!--STARTS daily Main GRAPH!-->
                                        <div id="single_product_chart" style="height: 260px;" class="single_product_chart"></div>
                                    </div>
                                    <div class="be-spinner" style="width: 100%;text-align:center;right:0;">
                                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                                        </svg>
                                        <span style="display:block; margin-top:30px;">Your data is loading.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--for recent orders-->

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default panel-table">
                                    <div class="panel-heading perf">
                                        <div class="title">Recent Orders</div>
                                    </div>
                                    <?php if (empty($connected_data)) : ?>
                                        <center><button class="btn btn-space btn-primary" id="see">Connect Your Store to see Data</button></center>
                                    <?php else : ?>
                                        <div class="panel-body table-responsive ">
                                            <table id="recent_orders_dashboard" class="table-borderless table table-striped table-hover table-fw-widget dataTable">
                                                <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody class="no-border-x">
                                                <?php
                                                $orders_data_recent = OrderProduct::find()->select('order_id')
                                                    ->joinWith(['order'])
                                                    ->andWhere(['order_product.user_id' => $user_id, 'product_id' => $product_id])
                                                    ->orderBy(['order.order_date' => SORT_DESC,])
                                                    ->groupBy('order_id')->limit(5)->all();

                                                if (empty($orders_data_recent)):
                                                    ?>
                                                    <tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">No data available in table.</td> </tr>
                                                <?php
                                                else :
                                                    foreach ($orders_data_recent as $orders_data_value) :
                                                        $channel_abb_id = isset($orders_data_value->order->connection_order_id) ? $orders_data_value->order->connection_order_id : "";
                                                        $firstname = $orders_data_value->order->customer->first_name;
                                                        $lname = $orders_data_value->order->customer->last_name;
                                                        $order_amount = isset($orders_data_value->order->total_amount) ? $orders_data_value->order->total_amount : 0;
                                                        $order_value = number_format((float) $order_amount, 2, '.', '');
                                                        $date_order = date('M-d-Y', strtotime($orders_data_value->order->order_date));
                                                        $order_status = $orders_data_value->order->status;
                                                        $label = '';
                                                        if ($order_status == 'Completed') :
                                                            $label = 'label-success';
                                                        endif;

                                                        if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') :
                                                            $label = 'label-danger';
                                                        endif;

                                                        if ($order_status == 'In Transit' || $order_status == 'On Hold'):
                                                            $label = 'label-primary';
                                                        endif;

                                                        if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending'):
                                                            $label = 'label-warning';
                                                        endif;
                                                        if ($order_status == 'Shipped' || $order_status == 'Partially Shipped'):
                                                            $label = 'label-primary';
                                                        endif;

                                                        $order_value = $order_value;
                                                        $order_value = number_format((float) $order_value, 2, '.', '');
                                                        $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
                                                        if (isset($selected_currency) and ! empty($selected_currency)) {
                                                            $currency_symbol = $selected_currency['symbol'];
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td><a href="/order/view?id=<?php echo $orders_data_value->order->id; ?>"><?= $channel_abb_id; ?></a></td>
                                                            <td class="captialize"><?= $firstname . ' ' . $lname; ?></td>
                                                            <td class="" style="text-align:left;"><?php echo $currency_symbol ?><?= number_format($order_value, 2); ?></td>
                                                            <td><?= $date_order; ?></td>
                                                            <td><span class="label  <?= $label; ?>"><?= $order_status; ?></span></td>
                                                        </tr>
                                                    <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php
                                    endif;
                                    ?>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="pricing" class="tab-pane">
                        <div class="table-responsive">
                            <table id="pricing_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <!--Strats For Elliot Price and sale price !-->
                                <tr>
                                    <td width="35%">Price</td>
                                    <td width="65%"><span class="currency_symbol" style="color:#4285F4"><?php echo $currency_symbol ?></span><a class="pPrice" id="price"  href="#" data-type="text" data-title="Please Enter value"><?php echo $price; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Sale Price</td>
                                    <td width="65%"><span class="currency_symbol" style="color:#4285F4"><?php echo $currency_symbol ?></span><a class="pSalePrice" id="sale_price"  href="#" data-type="text" data-title="Please Enter value"><?php echo $sale_price; ?></a></td>
                                </tr>

                                <tr>
                                    <td>Schedule Sale Date</td>
                                    <td  width="65%"><a id="schedule_date2" href="#" data-title="Please Select" data-pk="1" data-template="D / MMM / YYYY" data-viewformat="DD/MM/YYYY" data-format="YYYY-MM-DD" data-value="<?php echo $schedule_date1; ?>" data-type="combodate" class="editable editable-click"></a></td>
                                </tr>
                                </tbody>
                            </table>

                            <?php
                            foreach ($check_id_list_value as $connection_id) {

                                if ($connection_id == User::getDefaultConnection($user_id)) {
                                    continue;
                                }
                                $user_connection = UserConnection::find()->where(['id' => $connection_id])->one();
                                if (empty($user_connection)) {
                                    continue;
                                }
                                $store_name = $user_connection->getConnectionName();
                                $store_full_name = $user_connection->getPublicName();
                                $product_Connect_data = $store_full_name;
                                $href_link = str_replace(' ', '', $product_Connect_data);

                                $store_currency = $user_connection->userConnectionDetails->currency;

                                $conversion_rate = 1;
                                $userCurrency = isset($user->currency)?$user->currency:'USD';

                                if ($store_currency != '') {
                                    $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $store_currency);
                                }
                                $connect_price = number_format(($product_model->price * $conversion_rate), 2);
                                $connect_sale_price = number_format($product_model->sales_price * $conversion_rate, 2);
                                $connect_schedule_date = $schedule_date1;

                                $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($store_currency)])->select(['id', 'symbol'])->asArray()->one();
                                if (isset($selected_currency) and ! empty($selected_currency)) {
                                    $currency_symbol_channel = $selected_currency['symbol'];
                                }
                                if (empty($currency_symbol_channel))
                                    $currency_symbol_channel = '$';
                                ?>

                                <div id="accordionChannelS" class="panel-group accordion">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelS" href="#<?= $href_link ?>"><i class="icon mdi mdi-chevron-down"></i><?= $store_full_name; ?></a></h4>
                                        </div>
                                        <div id="<?= $href_link ?>" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <table class="table table-striped table-borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td width="35%"><?= $store_name; ?> Price</td>
                                                        <td width="65%"><span class="currency_symbol" style="color:#4285F4"><?php echo $currency_symbol_channel;?></span><a class="price_connect" id="price_con_<?=$connection_id?>" data-key="<?=$connection_id?>" data-rate="<?=$conversion_rate?>"  href="#" data-type="text" data-title="Please Enter value"><?php echo $connect_price; ?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"><?= $store_name; ?> Sale Price</td>
                                                        <td width="65%"><span class="currency_symbol" style="color:#4285F4"><?php echo $currency_symbol_channel; ?></span><a class="sale_price_connect" id="sale_price_con_<?=$connection_id?>" data-key="<?=$connection_id?>" data-rate="<?=$conversion_rate?>"  href="#" data-type="text" data-title="Please Enter value"><?php echo $connect_sale_price; ?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= $store_name; ?> Schedule Sale Date</td>
                                                        <td  width="65%">
                                                            <span class="currency_symbol" style="color:#4285F4"><?php echo $connect_schedule_date; ?></span>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <!--End For  Price and sale price for connected channel and stores !-->
                        </div>
                    </div>

                    <!--For Translation Tab According ton smartling!-->
                    <div id="translation" class="tab-pane">
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="accordionChannelS1" class="panel-group accordion">
                                    <?php if(!empty($translated_data)) {
                                        foreach ($translated_data as $sm_store){
                                            ?>
                                            <div class="panel panel-default">

                                                <div class="panel-heading">
                                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionChannelS1" href="#<?="smartling_" . $sm_store['id'];?>"><i class="icon mdi mdi-chevron-down"></i><?= $sm_store['channel_name']?></a></h4>
                                                </div>
                                                <div id="<?="smartling_" . $sm_store['id']?>" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <div class=" col-sm-4 control-label panel-heading" style="display: none;">Override Translation</div>
                                                        <div class="switch-button switch-button-lg" style="display: none;">
                                                            <input
                                                                    type="checkbox"
                                                                <?=$sm_store['override'] == ProductTranslation::TRANSLATE_OVERRIDE_YES ? "checked" : "" ?>
                                                                    name="<?="smartling_" . $sm_store['id'];?>"
                                                                    id="<?="smartling_" . $sm_store['id']?>"
                                                                    class="swt99"
                                                                    data-productid="<?=$product_id;?>"
                                                                    data-connectionid="<?=$sm_store['id']?>"
                                                                    data-channel="<?=$sm_store['channel_name'];?>"
                                                            >
                                                            <span>
								                                    <label for="<?="smartling_" . $sm_store['id']?>"></label>
                                                                </span>
                                                        </div>
                                                        <div class="panel-body div_translate_title<?=$sm_store['id'];?>">
                                                            <div class="table-responsive">
                                                                <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td width="35%">Product Title</td>
                                                                        <td width="65%"><a id="product_translate_title<?=$sm_store['id']?>" class="trans_title" href="#" data-type="text" data-title="Please Enter value"><?=$sm_store['name'];?></a></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td width="35%">Brand</td>
                                                                        <td width="65%"><a id="product_translate_brand<?=$sm_store['id']?>" class="trans_brand" href="#" data-type="text" data-title="Please Enter value"><?=$sm_store['brand'];?></a></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td width="35%">Description</td>
                                                                        <td width="65%">
                                                                            <div id="smartling_editor<?=$sm_store['id']?>" class="smartling_editor"><?=$sm_store['description']?></div>
                                                                        </td>
                                                                    </tr>

                                                                    </tbody>
                                                                </table>

                                                                <div style="text-align: right">
                                                                    <button class="btn btn-space btn-primary" onclick="updatedTranslation(<?=$sm_store['id']?>)">Save</button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php } } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--For Warranties Tab According !-->
                    <div id="Warranties" class="tab-pane">
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="accordionChannelS1" class="panel-group accordion">

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End For Warranties Tab According !-->
                </div>
                <div class="tab_up_btn_div custOMPerf">
                    <?php if (isValidPermission()) { ?>
                        <button class="btn btn-space btn-primary" onclick="updateProduct()">Publish</button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$sum_stock_quantity = Product::find()->Where(['id' => $product_model->id])->sum('stock_quantity');
/* Get count Of Stock Quantity */
$count_stock_quantity = Product::find()->Where(['id' => $product_model->id])->count('stock_quantity');
$productAbbr = Product::findOne(['id' => $product_model->id]);
$productSTKManage = $productAbbr->stock_manage;
?>
<!--Starst Product Stock Quantity modal -->
<div id="form-stock-qty" tabindex="-1" role="dialog" class="modal fade colored-header colored-header-primary">
    <div class="modal-dialog custom-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close md-close"><span class="mdi mdi-close"></span></button>
                <h3 class="modal-title">Stock Quantity</h3>
            </div>
            <input type="hidden" id="sum_product_abb" value="<?= $sum_stock_quantity; ?>"/>
            <input type="hidden" id="count_product_abb" value="<?= $count_stock_quantity; ?>"/>
            <input type="hidden" id="custom_item_key"/>
            <input type="hidden" id="count_item_value"/>
            <input type="hidden" id="stockManageFlag" value="<?= $productSTKManage?>"/>
            <div class="modal-body ModalCustomClass be-loading">
                <div class="stock-manage-box">
                    <div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                    <!--Stock Manage Enable/Disable-->
                    <div class="row stock-mange-header">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-6">InventoryIQ</div>
                                <div class="col-sm-3">
                                    <div class="switch-button switch-button-lg">
                                        <input type="checkbox" name="qnt" id="qnt" <?php echo ($productSTKManage=='Yes')? 'checked':''?>>
                                        <span><label for="qnt" class="chnl_dsbl_rv"></label></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="stock-view-percent<?=($productSTKManage=='No')? ' stock-view-percent-hide':''?>">Priority</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 stock-view-title">Total Stock Qty</div>
                                <div class="col-sm-3 stock-view-value" id="sum_stk_qty">
                                    <a class="channel-stk-qty-value" id="channel_stk_qty_<?= $product_model->id; ?>" data-key="<?=$product_model->id?>" href="#" data-type="text" data-title="Please Enter value">
                                        <?= trim($sum_stock_quantity); ?>
                                    </a>
                                </div>
                                <div class="col-sm-3 stock-view-percent<?=($productSTKManage=='No')? ' stock-view-percent-hide':''?>" id="sum_stk_percent">
                                    <?php
                                    if ( $sum_stock_quantity > 0 ) {
                                        echo "100%";
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Stock Manage Enable/Disable-->
                    <!--Accordions-->
                    <div class="row">
                        <div class="col-sm-12">
                            <div id="channel_store_accordion" class="panel-group accordion">
                                <!--Strats For  Stock Qty for connected channel and stores !-->
                                <?php
                                //                                    $connection = \Yii::$app->db;
                                //                                    $query = "SELECT *, SUM(inventory_value) FROM product_variation WHERE product_id={$product_model->id} AND user_id={$user_id} AND user_connection_id > 0 GROUP BY sku_value";
                                //                                    $query_run = $connection->createCommand($query);
                                //                                    $product_store_variants_rows = $query_run->queryAll();
                                $product_store_variants_rows = ProductVariation::find()
                                    ->where(['product_id' => $product_model->id, 'user_id' => $user_id])
                                    ->distinct()
                                    ->groupBy('sku_value')
                                    ->all();

                                if (!empty($product_store_variants_rows)) {
                                    $i = 1; $j = 1;
                                    foreach ( $product_store_variants_rows as $variant_sku_row ) {
                                        ?>
                                        <div class="panel panel-default">
                                            <div class="panel-body">

                                                <div class="row">
                                                    <div class="col-sm-6 stock-view-title">
                                                        <a data-toggle="collapse" data-parent="#channel_store_accordion" href="#collapseStoreChn<?=$i?>">
                                                            <h2 class="panel-title">
                                                            <i class="icon mdi mdi-chevron-down"></i>
                                                            <strong>SKU : <strong></strong></strong><?= isset($variant_sku_row->sku_value)?$variant_sku_row->sku_value:'-'; ?>
                                                            </h2>
                                                        </a>
                                                    </div>
                                                    <div class="col-sm-3 stock-view-value" id="store_sum_stk_qty_<?=$i?>">
                                                        <a class="variation-store-stk-sum-value"
                                                           id="store_stk_sum_<?= $variant_sku_row->variation_id; ?>"
                                                           data-serialkey="<?= $variant_sku_row->sku_value; ?>"
                                                           data-field="inventory"
                                                           data-key="<?= $variant_sku_row->id; ?>" href="#"
                                                           data-parent-key="<?= $i; ?>" href="#"
                                                           data-type="text" data-title="Please Enter value">
                                                            <?= isset($variant_sku_row->inventory_value) ? $variant_sku_row->inventory_value : '0'; ?>
                                                        </a>

                                                    </div>
                                                    <div class="col-sm-3 stock-view-percent<?=($productSTKManage=='No')? ' stock-view-percent-hide':''?>" id="store_sum_stk_percent_<?=$i?>" >
                                                        <?php

                                                        if ( is_numeric($variant_sku_row->inventory_value) && $variant_sku_row->inventory_value > 0 && is_numeric($sum_stock_quantity) && $sum_stock_quantity > 0  ) {
                                                            //echo number_format((float) ($variant_sku_row['SUM(inventory_value)']*100/$sum_stock_quantity), 2, '.', '')."%";
                                                            echo "100%";
                                                        } else {
                                                            echo "0%";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="collapseStoreChn<?=$i?>" class="panel-collapse collapse in">
                                                <div class="panel-body">

                                                    <?php
                                                    $channel_count = sizeof($check_id_list_value);
                                                    foreach ($check_id_list_value as $connection_id) {

                                                        if ($connection_id == User::getDefaultConnection($user_id)) {
                                                            continue;
                                                        }
                                                        $channelStoreRow = ProductVariation::find()
                                                            ->where(['product_id' => $product_model->id, 'user_id' => $user_id, 'variation_id'=> $variant_sku_row->variation_id, 'user_connection_id'=>$connection_id ])
                                                            ->one();
                                                        $userConnection = UserConnection::find()->where(['id' => $connection_id])->one();
                                                        $storeChannelName = $userConnection->getPublicName();
                                                        ?>
                                                        <div class="row store-variations-row"
                                                             id="store_varaiation_<?= $connection_id; ?>">
                                                            <div class="col-sm-6 stock-view-title">
                                                                <strong> <?= $storeChannelName ?> </strong>
                                                            </div>
                                                            <div class="col-sm-3 stock-view-value">
                                                                <a class="variation-store-stk-qty-value"
                                                                   id="store_stk_qty_<?= $j; ?>"
                                                                   data-serialkey="<?= $connection_id; ?>"
                                                                   data-field="inventory"
                                                                   data-parent-key="<?= $i; ?>"
                                                                   data-children-key="<?= $j; ?>"
                                                                   data-variation-id="<?= $variant_sku_row->variation_id; ?>"
                                                                   data-key="<?= isset($channelStoreRow->id) ? $channelStoreRow->id : -1  ?>" href="#"
                                                                   data-type="text" data-title="Please Enter value">
                                                                    <?= isset($channelStoreRow->allocate_inventory) && !empty($channelStoreRow->allocate_inventory) ? $channelStoreRow->allocate_inventory : '0'; ?>
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-3 stock-view-percent<?= ($productSTKManage == 'No') ? ' stock-view-percent-hide' : '' ?>"
                                                                 id="store_stk_percent_<?= $connection_id; ?>">
                                                                <?php
                                                                if (isset($variant_sku_row->inventory_value) && ($variant_sku_row->inventory_value > 0)) {
                                                                    ?><a class="variation-store-stk-percent"
                                                                         id="store_stk_percent_<?= $j; ?>"
                                                                         data-serialkey="<?= $connection_id; ?>"
                                                                         data-field="inventory"
                                                                         data-parent-key="<?= $i; ?>"
                                                                         data-children-key="<?= $j; ?>"
                                                                         data-variation-id="<?= $variant_sku_row->variation_id; ?>"
                                                                         data-key="<?= isset($channelStoreRow->id) ? $channelStoreRow->id : -1  ?>" href="#"
                                                                         data-type="text" data-title="Please Enter value">
                                                                    <?= !empty($channelStoreRow->allocate_percent) ? $channelStoreRow->allocate_percent : 0; ?>
                                                                    </a>
                                                                    %
                                                                    <?php
                                                                } else {
                                                                    echo "0%";
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        $j++;
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $i++;
                                    }
                                }
                                ?>
                                <!--End For  Stock Qty for connected channel and stores !-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default md-close stock-cancel">Cancel</button>
                <button type="button" data-dismiss="modal" class="btn btn-primary md-close stock-save">Save</button>
            </div>
        </div>
    </div>
</div>

<!--End Product Stock Quantity modal -->

<!--Starst Product Stock level modal -->
<div id="form-stock-level" tabindex="-1" role="dialog" class="modal fade colored-header colored-header-primary">
    <div class="modal-dialog custom-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close md-close"><span class="mdi mdi-close"></span></button>
                <h3 class="modal-title">Stock Level</h3>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="pricing_tbl" style="clear: both" class="table table-striped table-borderless">
                        <tbody>
                        <!--Strats For Elliot Stock Qty!-->
                        <tr>
                            <td width="35%">Stock Level</td>
                            <td><a id="stk_lvl" data-title="Please Select" data-value="<?php echo $stk_lvl; ?>" data-pk="1" data-type="select" href="#"></a></td>
                        </tr>
                        <!--END  For Elliot Stock Qty!-->

                        <!--Strats For  Stock Qty for connected channel and stores !-->
                        <?php
                        if (!empty($names)) {
                            foreach ($names as $product_Connect_data) {
                                $store_country = '';
                                $store_connection_id = preg_replace("/[^0-9,.]/", "", $product_Connect_data);
                                $product_Connect_data_final = preg_replace('/[0-9]+/', '', $product_Connect_data);

                                $magento_2 = substr($product_Connect_data, 0, 8);
                                if ($magento_2 == 'Magento2') {
                                    $product_Connect_data_final = $magento_2;
                                    $store_connection_id_2 = substr($product_Connect_data, 8);
                                    $store_connection_id = $store_connection_id_2;
                                }

                                $check_valid_store = Stores::find()->where(['store_name' => $product_Connect_data_final])->one();
                                if (!empty($check_valid_store)) {

                                    $prdouct_abb_stock = ProductAbbrivation::find()->Where(['product_id' => $product_model->id, 'channel_accquired' => trim($product_Connect_data_final), 'mul_store_id' => $store_connection_id])->one();
                                    $store_connection_details_data = StoresConnection::find()->Where(['stores_connection_id' => $store_connection_id])->with('storesDetails')->one();
                                    if (!empty($store_connection_details_data)) {
                                        $store_country = @$store_connection_details_data->storesDetails->country;
                                    }
                                } else {
                                    $prdouct_abb_stock = ProductAbbrivation::find()->Where(['product_id' => $product_model->id, 'channel_accquired' => trim($product_Connect_data)])->one();
                                    $store_country = '';
                                }
                                ?>
                                <tr>
                                    <td width="35%"><?= $product_Connect_data_final . ' ' . $store_country; ?> Stock Level</td>
                                    <td><a id="connect_stk_lvl" class="stock_level_connect" data-stock-level-connect="<?= $product_Connect_data; ?>" data-title="Please Select" data-value="<?= $prdouct_abb_stock->stock_level; ?>" data-pk="1" data-type="select" href="#"></a></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!--End For  Stock Qty for connected channel and stores !-->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default md-close">Cancel</button>
                <button type="button" data-dismiss="modal" class="btn btn-primary md-close">Save</button>
            </div>
        </div>
    </div>
</div>
<!--End Product Stock Level modal -->

<!--Starst Product Stock Status modal -->
<div id="form-stock-status" tabindex="-1" role="dialog" class="modal fade colored-header colored-header-primary">
    <div class="modal-dialog custom-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close md-close"><span class="mdi mdi-close"></span></button>
                <h3 class="modal-title">Stock Status</h3>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="pricing_tbl" style="clear: both" class="table table-striped table-borderless">
                        <tbody>
                        <!--Strats For Elliot Stock Qty!-->
                        <tr>
                            <td width="35%">Stock Status</td>
                            <td><a id="stk_status" data-title="Please Select" data-value="<?php echo $stk_status; ?>" data-pk="1" data-type="select" href="#"></a></td>
                        </tr>
                        <!--END  For Elliot Stock Qty!-->

                        <!--Strats For  Stock Qty for connected channel and stores !-->
                        <?php
                        if (!empty($names)) {
                            foreach ($names as $product_Connect_data) {
                                $store_country = '';
                                $store_connection_id = preg_replace("/[^0-9,.]/", "", $product_Connect_data);
                                $product_Connect_data_final = preg_replace('/[0-9]+/', '', $product_Connect_data);

                                $magento_2 = substr($product_Connect_data, 0, 8);
                                if ($magento_2 == 'Magento2') {
                                    $product_Connect_data_final = $magento_2;
                                    $store_connection_id_2 = substr($product_Connect_data, 8);
                                    $store_connection_id = $store_connection_id_2;
                                }

                                $check_valid_store = Stores::find()->where(['store_name' => $product_Connect_data_final])->one();
                                if (!empty($check_valid_store)) {
                                    $prdouct_abb_stock = ProductAbbrivation::find()->Where(['product_id' => $product_model->id, 'channel_accquired' => trim($product_Connect_data_final), 'mul_store_id' => $store_connection_id])->one();
                                    $prdouct_abb_stk_status = $prdouct_abb_stock->stock_status;
                                    $store_connection_details_data = StoresConnection::find()->Where(['stores_connection_id' => $store_connection_id])->with('storesDetails')->one();
                                    if (!empty($store_connection_details_data)) {
                                        $store_country = @$store_connection_details_data->storesDetails->country;
                                    }
                                } else {
                                    $prdouct_abb_stock = ProductAbbrivation::find()->Where(['product_id' => $product_model->id, 'channel_accquired' => trim($product_Connect_data)])->one();
                                    $prdouct_abb_stk_status = $prdouct_abb_stock->stock_status;
                                    $store_country = '';
                                }
                                ?>
                                <tr>
                                    <td width="35%"><?= $product_Connect_data_final . ' ' . $store_country; ?> Stock Status</td>
                                    <td>
                                        <a id="connect_stk_status" data-title="Please Select" class="stock_status_connect" data-stock-status-connect="<?= $product_Connect_data; ?>" data-value="<?= $prdouct_abb_stk_status; ?>" data-pk="1" data-type="select" href="#">
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        <!--End For  Stock Qty for connected channel and stores !-->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default md-close">Cancel</button>
                <button type="button" data-dismiss="modal" class="btn btn-primary md-close">Save</button>
            </div>
        </div>
    </div>
</div>
<!--End Product Stock Status modal -->

<!-- Product error modal -->
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in product_update_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close product_update_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='product_update_header_error_msg'></h3>
                    <p id="product_update_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default product_update_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="lazada_category_modal" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center">Lazada Categories</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close lazada_category_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="category-search">
                    <div class="list-group list-group-root">

                    </div>
                </div>
                <div class="xs-mt-50">
                    <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                    <button type="button" class="btn btn-space btn-primary select-category">Select</button>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="lazada_category_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close lazada_category_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="lazada_category_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default lazada_category_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="lazada_category_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='lazada_category_ajax_header_error_msg'></h3>
                    <p id="lazada_category_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default lazada_category_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>