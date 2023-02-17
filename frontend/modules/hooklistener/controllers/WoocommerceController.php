<?php

namespace frontend\modules\hooklistener\controllers;


use common\models\CurrencyConversion;
use common\models\ProductConnection;
use common\models\UserConnection;
use common\models\Product;
use Yii;
use yii\base\Controller;
use frontend\components\WoocommerceComponent;


class WoocommerceController extends Controller
{

    private $storeName = WoocommerceComponent::storeName;


    public function actionIndex(){
        echo $this->storeName;
    }

    //new products from Woocommerce to Elliot
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

        $product = json_decode($webhookContent, true);
        //$hook_action_id = $product['id'];

        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($product) ){

            $wcOwner = $wcConnection->user;
            $user_id = $wcConnection->user_id;
            $currency = $wcConnection->userConnectionDetails->currency;
            $country_code = $wcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($wcOwner->currency)?$wcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }



            $product['user_id'] = $user_id;
            $product['user_connection_id'] = $user_connection_id;
            $product['conversion_rate'] = $conversion_rate;
            $product['store_country_code'] = $country_code;
            $product['store_currency_code'] = $currency;


            WoocommerceComponent::wcUpsertProduct($product);


            return true;
        }

        return false;

    }

    //update products from Woocommerce to Elliot
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

        $product = json_decode($webhookContent, true);

        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($product) ){

            $wcOwner = $wcConnection->user;
            $user_id = $wcConnection->user_id;
            $currency = $wcConnection->userConnectionDetails->currency;
            $country_code = $wcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($wcOwner->currency)?$wcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }

            $product['user_id'] = $user_id;
            $product['user_connection_id'] = $user_connection_id;
            $product['conversion_rate'] = $conversion_rate;
            $product['store_country_code'] = $country_code;
            $product['store_currency_code'] = $currency;


            WoocommerceComponent::wcUpsertProduct($product);

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

        $data = json_decode($webhookContent, true);


        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($data) ){
            $hook_action_id = $data['id'];
            $wc_product_id = $hook_action_id;

            $user_product = ProductConnection::findOne([
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $wc_product_id]);

            if (!empty($user_product)){

                $productModel = Product::findOne([
                    'id' => $user_product->product_id
                ]);

                if ( !empty($productModel) ) {
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

        $order_data = json_decode($webhookContent, true);


        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($order_data) ) {

            $wcOwner = $wcConnection->user;
            $user_id = $wcConnection->user_id;
            $currency = $wcConnection->userConnectionDetails->currency;
            $country_code = $wcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($wcOwner->currency)?$wcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }

            $order_data['user_connection_id'] = $user_connection_id;
            $order_data['currency'] = $currency;
            $order_data['user_id'] = $user_id;
            $order_data['conversion_rate'] = $conversion_rate;
            $order_data['country_code'] = $country_code;


            WoocommerceComponent::wcUpsertOrder($order_data);

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

        $order_data = json_decode($webhookContent, true);

        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($order_data) ) {

            $wcOwner = $wcConnection->user;
            $user_id = $wcConnection->user_id;
            $currency = $wcConnection->userConnectionDetails->currency;
            $country_code = $wcConnection->userConnectionDetails->country_code;

            $userCurrency = isset($wcOwner->currency)?$wcOwner->currency:'USD';

            if ($currency != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency, $userCurrency);
            }

            $order_data['user_connection_id'] = $user_connection_id;
            $order_data['currency'] = $currency;
            $order_data['user_id'] = $user_id;
            $order_data['conversion_rate'] = $conversion_rate;
            $order_data['country_code'] = $country_code;


            WoocommerceComponent::wcUpsertOrder($order_data);

            return true;
        }

        return false;
    }


//Create Customer Hook
    public function actionCustomerCreate(){

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

        $customer_data = json_decode($webhookContent, true);


        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($customer_data) ) {

            $user_id = $wcConnection->user_id;

            $customer_data['user_id'] = $user_id;
            $customer_data['user_connection_id'] = $user_connection_id;

            WoocommerceComponent::wcUpsertCustomer($customer_data);

            return true;
        }

        return false;

    }


    public function actionCustomerUpdate(){

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

        $customer_data = json_decode($webhookContent, true);


        $wcConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($wcConnection) && !empty($customer_data) ) {

            $user_id = $wcConnection->user_id;

            $customer_data['user_id'] = $user_id;
            $customer_data['user_connection_id'] = $user_connection_id;

            WoocommerceComponent::wcUpsertCustomer($customer_data);

            return true;
        }

        return false;

    }


}