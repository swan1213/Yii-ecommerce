<?php

namespace frontend\models;

use common\models\Category;
use common\models\Product;
use Yii;
use yii\base\Model;

class RakutenConnectionForm extends Model
{
    public $sevice_secret;
    public $license_key;

    public function __construct() {
        parent::__construct();
    }

    public function rules()
    {
        return [
            [['sevice_secret', 'license_key'], 'required']
        ];
    }

    public function checkAuth(){
        $auth = "ESA " . base64_encode($this->sevice_secret . ":" . $this->license_key);
        $ch = curl_init('https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categories/get');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/hal+json; charset=utf-8",
                "Accept : application/hal+json",
                "Authorization : $auth",
                "Accept-Encoding : en-US,en;q=0.8")
        );
        $response  = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return false;
        }
        else{
            $xml = simplexml_load_string($response);
            $json = json_encode($xml);
            $data = json_decode($json,TRUE);
            if(isset($data['status']['systemStatus'])){
                if($data['status']['systemStatus']=="OK"){
                    return true;
                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        }
    }

    public function getCategories($userConnection, $user_id){
        $auth = "ESA " . base64_encode($this->sevice_secret . ":" . $this->license_key);
        $ch = curl_init('https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categories/get');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/hal+json; charset=utf-8",
                "Accept : application/hal+json",
                "Authorization : $auth",
                "Accept-Encoding : en-US,en;q=0.8")
        );
        $response  = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return false;
        }
        else{
            $xml = simplexml_load_string($response);
            $json = json_encode($xml);
            $data = json_decode($json,TRUE);
            if(isset($data['status']['systemStatus'])){
                if($data['status']['systemStatus']=="OK"){
                    if(isset($data['categoriesGetResult']['categoryList']['category'])) {
                        $categoryList = $data['categoriesGetResult']['categoryList']['category'];
                        foreach ($categoryList as $singleData){
                            self::insertCagegory($userConnection, $user_id, $singleData);
                        }
                        return true;
                    }
                    else{
                        return false;
                    }
                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        }
    }

    public static function insertCagegory($userConnectionModel, $user_id, $cate_data, $connection_parent_id=0) {
        $category_data = [
            'name' => isset($cate_data['name']) ? $cate_data['name'] : "", // Give category name
            'description' => "", // Give category body html
            'parent_id' => $connection_parent_id,
            'user_id' => $user_id, // Give Elliot user id,
            'user_connection_id' => $userConnectionModel->id, // Give Channel/Store prefix id
            'connection_category_id' => isset($cate_data['categoryId']) ? $cate_data['categoryId'] : "", // Give category id of Store/channels
            'connection_parent_id' => 0, //Give parent id of Store/channels
            'created_at' => date("Y-m-d H:i:s"), // Give Created at date if null then give current date format date('Y-m-d H:i:s')
            'updated_at' => date("Y-m-d H:i:s"), // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
        ];
        $cat_child = isset($cate_data['childCategories']) ? $cate_data['childCategories'] : [];
        $categroy_id = Category::categoryImportingCommon($category_data);
        if (count($cat_child) > 0) {
            foreach ($cat_child as $value) {
                self::insertCagegory($userConnectionModel, $user_id, $value, $categroy_id);
            }
        }
    }

    public function getProducts($userConnection, $user_id, $conversion_rate){
        $offset = 0;
        $current_date = date('Y-m-d', time());
        $current_time = date('h:i:s', time());
        $current_date = $current_date . "T" . $current_time .".000%2B00:00";
        $auth = "ESA " . base64_encode($this->sevice_secret . ":" . $this->license_key);
        while (1){
            var_dump("https://api.rms.rakuten.co.jp/es/2.0/product/search?releaseDateTo=$current_date&offset=$offset");
            $ch = curl_init("https://api.rms.rakuten.co.jp/es/2.0/product/search?releaseDateTo=$current_date&offset=$offset");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/hal+json; charset=utf-8",
                    "Accept : application/hal+json",
                    "Authorization : $auth",
                    "Accept-Encoding : en-US,en;q=0.8")
            );
            $response  = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                return false;
            }
            else{
                $xml = simplexml_load_string($response);
                $json = json_encode($xml);
                $data = json_decode($json,TRUE);
                if(isset($data['status']['systemStatus'])){
                    if($data['status']['systemStatus']=="OK"){
                        if(isset($data['productSearchResult']['products']['product'])) {
                            $productList = $data['productSearchResult']['products']['product'];
                            foreach ($productList as $singleData){
                                self::insertProduct($userConnection, $user_id, $singleData, $conversion_rate);
                            }
                        }
                        else{
                            break;
                        }
                    }
                    else{
                        break;
                    }
                }
                else{
                    break;
                }
            }
            $offset++;
            sleep ( 0.5 );
            if($offset>19)
                break;
        }
    }

    public static function insertProduct($userConnection, $user_id, $singleData, $conversion_rate){
        $userConnectionDetail = $userConnection->userConnectionDetails;

        $store_currency_code = $userConnectionDetail->currency;
        $store_country_code = $userConnectionDetail->country_code;
        $user_connection_id = $userConnection->id;

        $product_sku = isset($singleData['productId'])?$singleData['productId'] : "";
        $product_id = isset($singleData['productId'])?$singleData['productId'] : "";
        $title = isset($singleData['productName']) ? $singleData['productName'] : "";

        $product_description = '';
        $product_url = isset($singleData['reviewUrlPC'])?$singleData['reviewUrlPC'] : "";

        $product_categories_ids = array();
//        if(isset($singleData['genreId']))
//        {
//            $product_categories_ids = $singleData['genreId'];
//        }
        $product_type = "";isset($singleData['genreName'])?$singleData['genreName'] : "";
        $product_weight = "";

        $websites = array();
        $created_date = date("Y-m-d H:i:s");
        $updated_date = date("Y-m-d H:i:s");
        $product_status = "1";
        $product_price = isset($singleData['isOpenPrice'])?$singleData['isOpenPrice']:0;

        $product_quantity = "";
        $stock_status = 0;

        $product_image_data = array();

        $p_upc = '';
        $p_ean = '';
        $p_jan = '';
        $p_isbn = '';
        $p_mpn = '';
        $variants_data = [];
        $options_set_data = [];

        $product_data = [
            'user_id' => $user_id, // Elliot user id,
            'name' => $title, // Product name,
            'sku' => $product_sku, // Product SKU,
            'url' => $product_url, // Product url if null give blank value,
            'upc' => $p_upc, // Product upc if any,
            'ean' => $p_ean,
            'jan' => $p_jan, // Product jan if any,
            'isbn' => $p_isbn, // Product isban if any,
            'mpn' => $p_mpn, // Product mpn if any,
            'description' => $product_description, // Product Description,
            'adult' => null,
            'age_group' => null,
            'brand' => null,
            'condition' => null,
            'gender' => null,
            'weight' => $product_weight, // Product weight if null give blank value,
            'package_length' => null,
            'package_height' => null,
            'package_width' => null,
            'package_box' => null,
            'stock_quantity' => $product_quantity, //Product quantity,
            'allocate_inventory' => null,
            'currency' => $store_currency_code,
            'country_code' => $store_country_code,
            'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
            'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
            'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
            'price' => $product_price, // Porduct price,
            'sales_price' => $product_price, // Product sale price if null give Product price value,
            'schedule_sales_date' => null,
            'status' => ($product_status == 1)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
            'published' => ($product_status == 1)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
            'permanent_hidden' => Product::STATUS_NO,
            'user_connection_id' => $user_connection_id,
            'connection_product_id' => $product_id, // Stores/Channel product ID,
            'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
            'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
            'type' => $product_type, // Product Type
            'images' => $product_image_data, // Product images data
            'variations' => $variants_data,
            'options_set' => $options_set_data,
            'websites' => $websites, //This is for only magento give only and blank array
            'conversion_rate' => $conversion_rate,
            'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
        ];
//        var_dump($product_data);
//        exit;
        if ( !empty($product_data) ) {
            Product::productImportingCommon($product_data);
        }
    }
}