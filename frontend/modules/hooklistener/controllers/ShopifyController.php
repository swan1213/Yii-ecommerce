<?php
namespace frontend\modules\hooklistener\controllers;

use common\models\CurrencyConversion;
use common\models\Product;
use common\models\ProductConnection;
use common\models\UserConnection;
use Yii;
use yii\base\Controller;
use frontend\components\ShopifyComponent;

class ShopifyController extends Controller
{

    private $storeName = ShopifyComponent::storeName;

    public function actionIndex(){
        echo "Here is Shopify hooklistener";
    }

    public function actionShopUpdate(){


        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }
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




        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) ) {

            $shopData = json_decode($webhookContent, true);

            ShopifyComponent::importShop($user_connection_id, $shopData);

            return true;
        }

        return false;
    }

    /*
     * Shopify Porduct Create Hooks
     */

    public function actionProductCreate() {

        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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


        $createdProductData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($createdProductData) ) {

            $shopifyClientInfo = $userConnection->connection_info;

            $importUser = $userConnection->user;
            $store_connection_details = $userConnection->userConnectionDetails;
            $user_id = $importUser->id;

            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            $shopify_shop = $shopifyClientInfo['url'];

            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }


            $createdProductData['shopify_shop'] = $shopify_shop;
            $createdProductData['user_id'] = $user_id;
            $createdProductData['store_currency_code'] = $store_currency_code;
            $createdProductData['store_country_code'] = $store_country_code;
            $createdProductData['user_connection_id'] = $user_connection_id;
            $createdProductData['conversion_rate'] = $conversion_rate;

            ShopifyComponent::shopifyUpsertProduct($createdProductData);

            ShopifyComponent::shopifyAssignCollectionByProduct($user_connection_id, $createdProductData['id']);

            return true;
        }

        return false;
    }

    public function actionProductUpdate() {

        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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

        $updatedProductData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($updatedProductData) ) {

            $shopifyClientInfo = $userConnection->connection_info;

            $importUser = $userConnection->user;
            $store_connection_details = $userConnection->userConnectionDetails;
            $user_id = $importUser->id;

            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            $shopify_shop = $shopifyClientInfo['url'];

            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }


            $updatedProductData['shopify_shop'] = $shopify_shop;
            $updatedProductData['user_id'] = $user_id;
            $updatedProductData['store_currency_code'] = $store_currency_code;
            $updatedProductData['store_country_code'] = $store_country_code;
            $updatedProductData['user_connection_id'] = $user_connection_id;
            $updatedProductData['conversion_rate'] = $conversion_rate;

            ShopifyComponent::shopifyUpsertProduct($updatedProductData);

            ShopifyComponent::shopifyAssignCollectionByProduct($user_connection_id, $updatedProductData['id']);


            return true;
        }

        return false;
    }


    public function actionProductDelete() {
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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


        $deletedProductData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($deletedProductData) ) {

            $shopify_product_id = $deletedProductData['id'];

            $user_product = ProductConnection::findOne([
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $shopify_product_id]);

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
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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


        $createdOrderData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($createdOrderData) ) {

            $importUser = $userConnection->user;
            $store_connection_details = $userConnection->userConnectionDetails;
            $user_id = $importUser->id;

            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }


            $createdOrderData['user_id'] = $user_id;
            $createdOrderData['user_connection_id'] = $user_connection_id;
            $createdOrderData['store_currency_code'] = $store_currency_code;
            $createdOrderData['store_country_code'] = $store_country_code;
            $createdOrderData['conversion_rate'] = $conversion_rate;

            ShopifyComponent::shopifyUpsertOrder($createdOrderData);


            return true;
        }

        return false;

    }

    public function actionOrderUpdate() {

        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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

        $updatedOrderData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($updatedOrderData) ) {

            $importUser = $userConnection->user;
            $store_connection_details = $userConnection->userConnectionDetails;
            $user_id = $importUser->id;

            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }


            $updatedOrderData['user_id'] = $user_id;
            $updatedOrderData['user_connection_id'] = $user_connection_id;
            $updatedOrderData['store_currency_code'] = $store_currency_code;
            $updatedOrderData['store_country_code'] = $store_country_code;
            $updatedOrderData['conversion_rate'] = $conversion_rate;

            ShopifyComponent::shopifyUpsertOrder($updatedOrderData);


            return true;
        }

        return false;


    }


    public function actionCustomerCreate() {

        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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


        $createdCustomerData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($createdCustomerData) ) {

            $user_id = $userConnection->user_id;

            $createdCustomerData['user_id'] = $user_id;
            $createdCustomerData['user_connection_id'] = $user_connection_id;

            ShopifyComponent::shopifyUpsertCustomer($createdCustomerData);

            return true;
        }

        return false;


    }

    public function actionCustomerUpdate() {

        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }

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


        $updatedCustomerData = json_decode($webhookContent, true);

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($userConnection) && !empty($updatedCustomerData) ) {

            $user_id = $userConnection->user_id;

            $updatedCustomerData['user_id'] = $user_id;
            $updatedCustomerData['user_connection_id'] = $user_connection_id;

            ShopifyComponent::shopifyUpsertCustomer($updatedCustomerData);


            return true;
        }

        return false;


    }


}