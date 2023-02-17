<?php
namespace console\controllers;

use common\models\Country;
use common\models\Crontask;
use common\models\CurrencyConversion;
use common\models\Notification;
use common\models\UserConnection;
use frontend\components\BigcommerceComponent;
use frontend\components\ChannelFlipkartComponent;
use frontend\components\ChannelJetComponent;
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
use Automattic\WooCommerce\Client as Woocommerce;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use SoapClient;
use SoapFault;

class TestController extends Controller
{

    public function actionMailTo($toMailAddress){

        /* Send Email */
        $email_message = 'Email Test Message';
        Yii::$app->commandBus->handle(new SendEmailCommand([
            'subject' => Yii::t('common', 'Mail Test'),
            'view' => '@common/mail/template',
            'to' => $toMailAddress,
            'params' => [
                'title' => 'Mail test',
                'content' => $email_message,
                'server' => env('SEVER_URL')
            ]
        ]));

    }

    public function actionInsertJob(){

        $task1 = new Crontask();
        $task1->name = "testwithoutparam";
        $task1->action = "test/cron";
        $task1->completed = Crontask::COMPLETED_YES;
        $task1->save(false);


        $task2 = new Crontask();
        $task2->name = "testwithparam";
        $task2->action = "test/cron-param-one";
        $param = ['testparam'];
        $task2->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
        $task2->completed = Crontask::COMPLETED_YES;
        $task2->save(false);


        $task3 = new Crontask();
        $task3->name = "testwithparamtwo";
        $task3->action = "test/cron-param-two";
        $param2 = ['firstparam', 2];
        $task3->params = @json_encode($param2, JSON_UNESCAPED_UNICODE);
        $task3->completed = Crontask::COMPLETED_YES;
        $task3->save(false);


    }

    public function actionCron(){

        file_put_contents('actionCron.txt', "crontask test without param");
    }

    public function actionCronParamOne($param){

        $data = "crontak test with param : " . $param;
        file_put_contents('actionCronParamOne.txt', $data);

    }

    public function actionCronParamTwo($param1, $param2){

        $data = "crontak test with param : " . PHP_EOL;
        $data .= "param 1 : " . $param1 . PHP_EOL;
        $data .= "param 2 : " . $param2 . PHP_EOL;

        file_put_contents('actionCronParamTwo.txt', $data);

    }


    public function actionWoocommerce($user_connection_id){

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);
        $woocommerceClientInfo = $store_woocommerce->connection_info;

        $woocommerce_store_url = $woocommerceClientInfo['store_url'];
        $woocommerce_consumer = $woocommerceClientInfo['consumer'];
        $woocommerce_secret = $woocommerceClientInfo['consumer_secret'];

        $check_url = parse_url($woocommerce_store_url);
        $url_protocol = $check_url['scheme'];
        if ($url_protocol == 'http'){
            /* For Http Url */
            $wooCommerceClient = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                    'timeout' => 0,
                ]
            );

        } else {
            $wooCommerceClient = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                    "query_string_auth" => true,
                    'timeout' => 0,
                ]
            );

        }

        try {
            $wooCommerceDetails = $wooCommerceClient->get('settings/general');

            $store_addr_1 = "";
            $store_addr_2 = "";
            $store_city = "";
            $store_county_state = "";
            $store_zip = "";
            $store_currency = "";

            $wooCommerceDetails = @json_decode(@json_encode($wooCommerceDetails), true);
            foreach ($wooCommerceDetails as $eachData){

                if ( $eachData['id'] === 'woocommerce_store_address' ){
                    $store_addr_1 = $eachData['value'];
                }

                if ( $eachData['id'] === 'woocommerce_store_address_2' ){
                    $store_addr_2 = $eachData['value'];
                }

                if ( $eachData['id'] === 'woocommerce_store_city' ){
                    $store_city = $eachData['value'];
                }

                if ( $eachData['id'] === 'woocommerce_default_country' ){
                    $store_county_state = $eachData['value'];
                }

                if ( $eachData['id'] === 'woocommerce_store_postcode' ){
                    $store_zip = $eachData['value'];
                }

                if ( $eachData['id'] === 'woocommerce_currency' ){
                    $store_currency = $eachData['value'];
                }

            }

            echo $store_addr_1;
        } catch (HttpClientException $e) {
            //$errorMsg = $e->getMessage();
            //$array_msg['api_error'] = $errorMsg;
            $array_msg['api_error'] = 'Your API credentials are not working. Please check and try again.';
            return json_encode($array_msg);
        }


    }


    public function actionJet($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $jet_Connection = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($jet_Connection)) {

            $jetClient = ChannelJetComponent::createChannelJetClient($user_connection_id);

            if ( $jetClient->checkValidate() ){

                $sku_list = $jetClient->call('get', 'merchant-skus');
                $sku_urls = $sku_list['sku_urls'];
                $skus_count = 0;
                if ( !empty($sku_urls) ){

                    foreach ($sku_urls as $sku_url){
                        $sku_variation_url = $sku_url."/variation";

                        $variation_result = $jetClient->call('get', $sku_variation_url);

                        if ( !empty($variation_result) ){

                            echo $sku_variation_url . PHP_EOL;

                            $sub_skus_count = count($variation_result);
                            $skus_count += $sub_skus_count;

                            echo PHP_EOL;
                        }
                    }

                }

            }
            echo "total_count = " . $skus_count .PHP_EOL;
            return true;
        }

        return false;


    }

    public function actionFlipkartOrder($user_connection_id){


        ChannelFlipkartComponent::OrderImport($user_connection_id);

    }

    public function actionBgcFromShopify($user_connection_id, $product_id){

        echo BigcommerceComponent::bgcUpstreamProduct($user_connection_id, $product_id);
    }

    public function actionShopifyFromBgc($user_connection_id, $product_id){

        echo ShopifyComponent::shopifyUpdateProduct($user_connection_id, $product_id);
    }

}