<?php

namespace frontend\modules\hooklistener\controllers;


use common\models\Category;
use common\models\CurrencyConversion;
use common\models\ProductConnection;
use common\models\UserConnection;
use common\models\Product;
use Yii;
use yii\base\Controller;
use frontend\components\BigcommerceComponent;
use frontend\components\ElliBigCommerce as Bigcommerce;


class BigcommerceController extends Controller
{

    private $storeName = BigcommerceComponent::storeName;


    public function actionIndex(){
        echo $this->storeName;
    }

    //new products from Bigcommerce to Elliot
    public function actionProductCreate() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_action_id = $data->data->id;

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_action_id) ){

            $bigCommerceSetting = $bgcConnection->connection_info;
            $bgcdomain = $bgcConnection->userConnectionDetails->store_url;
            $user_company = $bgcConnection->user->company;
            $bgcOwner = $bgcConnection->user;
            $user_id = $bgcConnection->user_id;
            $currency = $bgcConnection->userConnectionDetails->currency;
            $country_code = $bgcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($bgcOwner->currency)?$bgcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }


            Bigcommerce::configure($bigCommerceSetting);

            $product = Bigcommerce::getProduct($hook_action_id);

            if (!empty($product)) {

                $extraData = [
                    'conversion_rate' => $conversion_rate,
                    'user_connection_id' => $user_connection_id,
                    'user_company' => $user_company,
                    'bgcdomain' => $bgcdomain,
                    'user_id' => $user_id,
                    'country_code' => $country_code,
                    'currency' => $currency,
                    'bigCommerceSetting' => $bigCommerceSetting,
                ];
                BigcommerceComponent::bgcUpsertProduct($product, $extraData);

            }

            return true;
        }

        return false;

    }

    //update products from Bigcommerce to Elliot
    public function actionProductUpdate() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_action_id = $data->data->id;

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_action_id) ){

            $bigCommerceSetting = $bgcConnection->connection_info;
            $bgcdomain = $bgcConnection->userConnectionDetails->store_url;
            $user_company = $bgcConnection->user->company;
            $bgcOwner = $bgcConnection->user;
            $user_id = $bgcConnection->user_id;
            $currency = $bgcConnection->userConnectionDetails->currency;
            $country_code = $bgcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($bgcOwner->currency)?$bgcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }


            Bigcommerce::configure($bigCommerceSetting);

            $product = Bigcommerce::getProduct($hook_action_id);

            if (!empty($product)) {

                $extraData = [
                    'conversion_rate' => $conversion_rate,
                    'user_connection_id' => $user_connection_id,
                    'user_company' => $user_company,
                    'bgcdomain' => $bgcdomain,
                    'user_id' => $user_id,
                    'country_code' => $country_code,
                    'currency' => $currency,
                    'bigCommerceSetting' => $bigCommerceSetting,
                ];
                BigcommerceComponent::bgcUpsertProduct($product, $extraData);

            }

            return true;
        }

        return false;

    }

    //delete products
    public function actionProductDelete() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_action_id = $data->data->id;

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_action_id) ){

            $bgc_product_id = $hook_action_id;

            $user_product = ProductConnection::findOne([
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $bgc_product_id]);

            if (!empty($user_product)){

                $productModel = Product::findOne([
                    'id' => $user_product->product_id
                ]);

                if ( !empty($productModel) ){
                    $productModel->permanent_hidden = Product::STATUS_YES;
                    $productModel->status = Product::STATUS_INACTIVE;
                    $productModel->published = Product::PRODUCT_PUBLISHED_NO;
                    $productModel->save(true, ['permanent_hidden', 'status', 'published']);
                }

                $user_product->status = ProductConnection::STATUS_NO;
                $user_product->save(true, ['status']);
            }

            return true;
        }

        return false;

    }


    public function actionOrderCreate() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_order_id = $data->data->id;

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_order_id) ) {

            $bigCommerceSetting = $bgcConnection->connection_info;
            $bgcOwner = $bgcConnection->user;
            $user_id = $bgcConnection->user_id;
            $currency = $bgcConnection->userConnectionDetails->currency;
            $country_code = $bgcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($bgcOwner->currency)?$bgcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }


            Bigcommerce::configure($bigCommerceSetting);

            $order_data = Bigcommerce::getOrder($hook_order_id);

            if (!empty($order_data)) {

                $extraData = [
                    'conversion_rate' => $conversion_rate,
                    'user_connection_id' => $user_connection_id,
                    'user_id' => $user_id,
                    'currency_code' => $currency,
                    'country_code' => $country_code,
                ];

                BigcommerceComponent::bgcUpsertOrder($order_data, $extraData);

            }


            return true;
        }

        return false;

    }


    public function actionOrderUpdate() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_order_id = $data->data->id;

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_order_id) ) {

            $bigCommerceSetting = $bgcConnection->connection_info;
            $bgcOwner = $bgcConnection->user;
            $user_id = $bgcConnection->user_id;
            $currency = $bgcConnection->userConnectionDetails->currency;
            $country_code = $bgcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($bgcOwner->currency)?$bgcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }


            Bigcommerce::configure($bigCommerceSetting);

            $order_data = Bigcommerce::getOrder($hook_order_id);

            if (!empty($order_data)) {

                $extraData = [
                    'conversion_rate' => $conversion_rate,
                    'user_connection_id' => $user_connection_id,
                    'user_id' => $user_id,
                    'currency_code' => $currency,
                    'country_code' => $country_code,
                ];

                BigcommerceComponent::bgcUpsertOrder($order_data, $extraData);

            }


            return true;
        }

        return false;

    }



    public function actionCategory() {

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_category_id = $data->data->id;

        if ($data->data->type == "category" && $data->scope == "store/category/deleted"){
            return true;
        }

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_category_id) ) {

            $bigCommerceSetting = $bgcConnection->connection_info;
            $user_id = $bgcConnection->user_id;


            Bigcommerce::configure($bigCommerceSetting);

            $category = Bigcommerce::getCategory($hook_category_id);

            if (!empty($category)) {

                $cat_id = $category->id;
                $cat_name = $category->name;
                $cat_description = $category->description;
                $cat_parent_id = $category->parent_id;

                $category_data = [
                    'name' => $cat_name, // Give category name
                    'description' => $cat_description, // Give category body html
                    'parent_id' => 0,
                    'user_id' => $user_id, // Give Elliot user id,
                    'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                    'connection_category_id' => $cat_id, // Give category id of Store/channels
                    'connection_parent_id' => $cat_parent_id, // Give Category parent id of Elliot if null then give 0
                ];

                Category::categoryImportingCommon($category_data);

            }


            return true;
        }

        return false;

    }


//Create Customer Hook
    public function actionCustomer(){

        $request = Yii::$app->request->get();

        if ( !isset($request['id']) ) {
            return false;
        }
        $user_connection_id = $request['id'];

        if ( !isset($request['action']) ) {
            return false;
        }
        $actionStoreName = $request['action'];
        if ( $actionStoreName !== $this->storeName ){
            return false;
        }

        /* For Web Hook Content */
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

        $data = json_decode($webhookContent);
        $hook_customer_id = $data->data->id;

        if ($data->data->type == "customer" && $data->scope == "store/customer/deleted"){
            return true;
        }

        $bgcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($bgcConnection) && !empty($hook_customer_id) ) {

            $bigCommerceSetting = $bgcConnection->connection_info;
            $user_id = $bgcConnection->user_id;


            Bigcommerce::configure($bigCommerceSetting);

            $customers_data = Bigcommerce::getCustomer($hook_customer_id);

            if (!empty($customers_data)) {

                $extra_data = [
                    'user_connection_id' => $user_connection_id,
                    'user_id' => $user_id
                ];

                BigcommerceComponent::bgcUpsertCustomer($customers_data, $extra_data);

            }


            return true;
        }

        return false;

    }


}