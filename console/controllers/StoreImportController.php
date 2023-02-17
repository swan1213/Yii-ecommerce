<?php
namespace console\controllers;

use common\models\Country;
use common\models\CurrencyConversion;
use common\models\Notification;
use common\models\UserConnection;
use frontend\components\BigcommerceComponent;
use frontend\components\ReactionComponent;
use frontend\components\MagentoComponent;
use frontend\components\Magento2Component;
use frontend\components\WoocommerceComponent;
use Yii;
use yii\console\Controller;
use common\commands\SendEmailCommand;
use frontend\components\ShopifyComponent;
use common\components\shopify\ShopifyThreadSpeedComponent;
use frontend\components\VtexComponent;
use SoapClient;
use SoapFault;

class StoreImportController extends Controller
{

    public function actionShopify($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_shopify = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_shopify)) {

            $importUser = $store_shopify->user;
            $store_connection_details = $store_shopify->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $shopDetails = $store_connection_details->others;
            $shopCurrency = $shopDetails['shop_currency'];

            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            //$store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($shopCurrency, $userCurrency);
                //$conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            ShopifyComponent::addShopifyHooks($user_connection_id);

            ShopifyComponent::shopifyProductImport($user_connection_id, $conversion_rate);

            ShopifyComponent::shopifyAssignProductToCollection($user_connection_id);

            ShopifyComponent::shopifyCustomerImport($user_connection_id);

            ShopifyComponent::shopifyOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */
            $email_message = 'Success, Your Shopify Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Shopify Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Shopify Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Shopify ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_shopify->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_shopify->save(true, ['import_status']);

            return true;
        }

        return false;
    }


    public function actionSpeedShopify($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_shopify = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_shopify)) {

            $importUser = $store_shopify->user;
            $store_connection_details = $store_shopify->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            //$store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            ShopifyComponent::addShopifyHooks($user_connection_id);

            ShopifyComponent::shopifySpeedProductImport($user_connection_id, $conversion_rate);

            ShopifyComponent::shopifySpeedAssignProductToCollection($user_connection_id);

            ShopifyComponent::shopifySpeedCustomerImport($user_connection_id);

            ShopifyComponent::shopifySpeedOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */
            $email_message = 'Success, Your Shopify Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Shopify Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Shopify Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Shopify ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_shopify->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_shopify->save(true, ['import_status']);

            return true;
        }

        return false;
    }

    public function actionSpeedxShopify($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_shopify = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_shopify)) {

            $importUser = $store_shopify->user;
            $store_connection_details = $store_shopify->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            //$store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            ShopifyThreadSpeedComponent::addShopifyHooks($user_connection_id);

            ShopifyThreadSpeedComponent::shopifySpeedProductImport($user_connection_id, $conversion_rate);

            ShopifyThreadSpeedComponent::shopifySpeedAssignProductToCollection($user_connection_id);

            ShopifyThreadSpeedComponent::shopifySpeedCustomerImport($user_connection_id);

            ShopifyThreadSpeedComponent::shopifySpeedOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */
            $email_message = 'Success, Your Shopify Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Shopify Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Shopify Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Shopify ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_shopify->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_shopify->save(true, ['import_status']);

            return true;
        }

        return false;
    }


    public function actionBigcommerce($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_bigcommerce)) {

            $importUser = $store_bigcommerce->user;
            $store_connection_details = $store_bigcommerce->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            //$store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            BigcommerceComponent::addBigcommerceHooks($user_connection_id);

            BigcommerceComponent::bgcCategoryImport($user_connection_id);

            BigcommerceComponent::bgcProductImport($user_connection_id, $conversion_rate);

            BigcommerceComponent::bgcCustomerImport($user_connection_id);

            BigcommerceComponent::bgcOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */


            $email_message = 'Success, Your Bigcommerce Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Bigcommerce Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Bigcommerce Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Bigcommerce ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_bigcommerce->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_bigcommerce->save(true, ['import_status']);

            return true;
        }

        return false;
    }

    public function actionWoocommerce($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_woocommerce)) {

            $importUser = $store_woocommerce->user;
            $store_connection_details = $store_woocommerce->userConnectionDetails;
            $shopUrl = parse_url($store_connection_details->store_url);
            $shopName = $shopUrl['host'];
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            WoocommerceComponent::addWoocommerceHooks($user_connection_id);

            WoocommerceComponent::wcCategoryImport($user_connection_id);

            WoocommerceComponent::wcProductImport($user_connection_id, $conversion_rate);

            WoocommerceComponent::wcCustomerImport($user_connection_id);

            WoocommerceComponent::wcOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */
            $email_message = 'Success, Your Woocommerce Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Woocommerce Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Woocommerce Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Woocommerce ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_woocommerce->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_woocommerce->save(true, ['import_status']);

            return true;
        }

        return false;
    }

    public function actionVtex($user_connection_id, $user_notify=true){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_vtex)) {

            $importUser = $store_vtex->user;
            $store_connection_details = $store_vtex->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }

            //VtexComponent::addVtexHooks($user_connection_id);
            VtexComponent::vtexCategoryImporting($user_connection_id);

            VtexComponent::vtexProductImporting($user_connection_id, $conversion_rate);

            VtexComponent::vtexCustomerImport($user_connection_id);

            VtexComponent::vtexOrderImport($user_connection_id, $conversion_rate);


            if ( $user_notify ){

                /* Send Email */
                $email_message = 'Success, Your Vtex Store '.$shopName.' importing has done.';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Vtex Import'),
                    'view' => '@common/mail/template',
                    'to' => $email,
                    'params' => [
                        'title' => 'Vtex Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                //Drop-Down Notification for User
                $notif_type = "Vtex ".$shopName;

                $notification_model = new Notification();
                $notification_model->user_id = $user_id;
                $notification_model->title = $notif_type;
                $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
                $notification_model->save(false);


                $store_vtex->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $store_vtex->save(true, ['import_status']);

            }

            return true;
        }

        return false;

    }

    public function actionReaction($user_connection_id) {


        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_reaction = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_reaction)) {

            $importUser = $store_reaction->user;
            $store_connection_details = $store_reaction->userConnectionDetails;

            $shopName = $store_connection_details->store_name;
            $shopDetails = $store_connection_details->others;
            $shopCurrency = $shopDetails['shop_currency'];

            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            //$store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($shopCurrency, $userCurrency);
            }

            //ReactionComponent::addReactionHooks($user_connection_id);

            ReactionComponent::reactionProductImport($user_connection_id, $conversion_rate);

            ReactionComponent::reactionCustomerImport($user_connection_id);

            ReactionComponent::reactionOrderImport($user_connection_id, $conversion_rate);


            /* Send Email */
            $email_message = 'Success, Your Reaction Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Reaction Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Reaction Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Reaction ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_reaction->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_reaction->save(true, ['import_status']);

            return true;
        }

        return false;
    }


    public function actionMagento($user_connection_id) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        ini_set('default_socket_timeout', 7200);

        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento)) {
            $importUser = $store_magento->user;
            $store_connection_details = $store_magento->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }
            $magentoClientInfo = $store_magento->connection_info;

            $magento_soap_url = $magentoClientInfo['magento_soap_url'];
            $magento_soap_user = $magentoClientInfo['magento_soap_user'];
            $magento_soap_api = $magentoClientInfo['magento_soap_api'];
            try {
                $cli = new SoapClient($magento_soap_url);
                $session_id = $cli->login($magento_soap_user, $magento_soap_api);

                //All Stores of Magento
                // $stores_list = $cli->call($session_id, 'store.list');
//            MagentoComponent::addShopifyHooks($user_connection_id, $session_id);
//
                MagentoComponent::magentoCategoryImport($store_magento, $session_id, $cli);
                MagentoComponent::magentoProductImport($store_magento, $session_id, $cli, $conversion_rate);
                MagentoComponent::magentoCustomerImport($store_magento, $session_id, $cli);
                MagentoComponent::magentoOrderImport($store_magento, $session_id, $cli, $conversion_rate);

                /* Send Email */
                $email_message = 'Success, Your Magento Store '.$shopName.' importing has done.';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Magento Import'),
                    'view' => '@common/mail/template',
                    'to' => $email,
                    'params' => [
                        'title' => 'Magento Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                //Drop-Down Notification for User
                $notif_type = "Magento ".$shopName;

                $notification_model = new Notification();
                $notification_model->user_id = $user_id;
                $notification_model->title = $notif_type;
                $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
                $notification_model->save(false);


                $store_magento->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $store_magento->save(true, ['import_status']);

                return true;
            } catch (SoapFault $e) {
                $e->faultstring;
                var_dump($e->getMessage());
            }
        }
        return false;
    }


    public function actionMagento2($user_connection_id) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento)) {
            $importUser = $store_magento->user;
            $store_connection_details = $store_magento->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }
            $magentoClientInfo = $store_magento->connection_info;

            $magento_shop = $magentoClientInfo['magento_shop'];
            $admin_url = $magentoClientInfo['admin_url'];
            $magento_2_access_token = $magentoClientInfo['magento_2_access_token'];
            $magento_country = $magentoClientInfo['magento_country'];


            $access_token = array("Authorization: Bearer $magento_2_access_token");

//            $requestUrlStores = $magento_shop.'index.php/rest/V1/store/websites?searchCriteria=';
//            $requestUrlCategories = $magento_shop.'index.php/rest/V1/categories';
//            $requestUrlProducts = $magento_shop.'index.php/rest/V1/products?searchCriteria=';
//            $requestUrlCustomers = $magento_shop.'index.php/rest/V1/customers/search?searchCriteria=';
//            $requestUrlOrders = $magento_shop.'index.php/rest/V1/orders?searchCriteria=';
            Magento2Component::magentoCategoryImport($store_magento, $magento_shop, $access_token);
            Magento2Component::magentoProductImport($store_magento, $magento_shop, $access_token, $conversion_rate);
            Magento2Component::magentoCustomerImport($store_magento, $magento_shop, $access_token);
            Magento2Component::magentoOrderImport($store_magento, $magento_shop, $access_token, $conversion_rate);

            /* Send Email */
            $email_message = 'Success, Your Magento2 Store '.$shopName.' importing has done.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Magento2 Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Magento2 Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Magento2 ".$shopName;

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your ' . $notif_type . ' store data has been successfully imported.';
            $notification_model->save(false);


            $store_magento->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $store_magento->save(true, ['import_status']);

            return true;
        }
        return false;
    }

}