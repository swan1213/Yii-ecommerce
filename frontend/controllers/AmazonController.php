<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

use common\models\Category; 
use common\models\Country;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductImage;
use common\models\User;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\AmazonConnectionForm;
use frontend\components\ConsoleRunner;

/**
 * AmazonController for Channels model.
 */
class AmazonController extends Controller {
	public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                    	'actions' => 'index',
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actionIndex($id) {
        $is_connect_channel = false;
        $user_id = Yii::$app->user->identity->id;

        // check if this connection is still existed in Connection table
        $connection_row = Connection::find()->where(['id' => $id])->one();

        // connection is not exsited
        if(empty($connection_row)) {
            return $this->redirect(['/channels']);
        }

        $connection_name = $connection_row->name;

        $user_connection_row = UserConnection::find()->where([
            'connection_id' => $id,
            'user_id' => $user_id])->one();

        if(!empty($user_connection_row) and 
            ($user_connection_row->connected == UserConnection::CONNECTED_YES or 
            $user_connection_row->import_status == UserConnection::IMPORT_STATUS_PROCESSING)) {
            $is_connect_channel = true;
        }

        $model = new AmazonConnectionForm();

        return $this->render('index', [
            'id' => $id,
            'is_connect_channel' => $is_connect_channel,
            'connection_name' => $connection_name,
            'model' => $model
        ]);
    }

    public function actionAuthorize($id) {
        $user_id = Yii::$app->user->identity->id;
        $model = new AmazonConnectionForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $post_variable = Yii::$app->request->post('AmazonConnectionForm');
            $access_key  = $post_variable['access_key'];
            $secret_key  = $post_variable['secret_key'];
            $merchant_id = $post_variable['merchant_id'];
            $user_connection_id = '';

            try {
                $connection_instance = Connection::find()->where(['id' => $id])->one();

                // connection is not exsited
                if(empty($connection_instance)) {
                    throw new \Exception('There is no channel.');
                }
                $connection_name = $connection_instance->name;

                $country_info = $this->getCountryDetails($connection_name);
                if(empty($country_info)) {
                    throw new \Exception('Your country is not available.');
                }

                $marketplace_id = $country_info['marketplace_id'];

                $connection_parent_instance = ConnectionParent::find()->where(['id' => $connection_instance->parent_id])->one();
                if(empty($connection_parent_instance)) {
                    throw new \Exception('Invalid Connection.');
                }

                $store_name = $connection_parent_instance->name.' '.$connection_name;

                $client = new \MCS\MWSClient([
                    'Marketplace_Id' => $marketplace_id,
                    'Seller_Id' => $merchant_id,
                    'Access_Key_ID' => $access_key,
                    'Secret_Access_Key' => $secret_key
                ]);

                if (!$client->validateCredentials()) {
                    throw new \Exception('Your credential is invalid.');
                }

                // update user info
                $user_credential = array(
                    'market_id' => $merchant_id,
                    'access_key' => $access_key,
                    'secret_key' => $secret_key,
                    'marketplace_id' => $marketplace_id
                );

                $user_connection_id = $this->importUserInfo(
                    $user_id,
                    $merchant_id,
                    $connection_instance->id,
                    $user_credential
                );
                $user_connection_instance = UserConnection::find()->where(['id' => $user_connection_id])->one();
                $user_connection_detail_instance = $this->importUserDetail(
                    $user_connection_id,
                    $country_info['country_code'],
                    $store_name
                );

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'channel-import/amazon '.$user_connection_id;
                $res = $importCr->run($importCmd);

                $user_connection_instance->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
                $user_connection_instance->save(true, ['import_status']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Your Amazon Channel has been connected successfully. Importing has started and you will be notified once it is completed.'
                ]);
            } catch(\Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            } catch(\InvalidArgumentException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            } catch (\common\components\amazon\MarketplaceWebServiceProducts_Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill all fields required'
            ]);
        }
    }

    /**
    * save the data in UserConnectionDetails table
    * $user_connection_id: id in User table
    * $country_code: country code in Country table
    * $store_name: store name
    */
    public function importUserDetail(
        $user_connection_id,
        $country_code,
        $store_name
    ) {
        $country_row = Country::find()->where(['sortname' => $country_code])->one();
        $country_name = '';
        $currency_code = '';
        $currency_symbol = '';

        if(!empty($country_row)) {
            $country_name = $country_row->name;
            $currency_code = $country_row->currency_code;
            $currency_symbol = $country_row->currency_symbol;
        }

        $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model = new UserConnectionDetails();
        }

        $user_connection_detail_model->user_connection_id = $user_connection_id;
        $user_connection_detail_model->store_name = $store_name;
        $user_connection_detail_model->store_url = 'https://www.amazon.com';
        $user_connection_detail_model->country = '';
        $user_connection_detail_model->country_code = $country_code;
        $user_connection_detail_model->currency = $currency_code;
        $user_connection_detail_model->currency_symbol = $currency_symbol;
        $user_connection_detail_model->others = '';
        $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
        $user_connection_detail_model->save(false);

        return $user_connection_detail_model;
    }

    /**
    * save the data in UserConnection table
    * $user_id: id in User table
    * $market id: market id
    * $connection_id: id in Connection table
    * $user_credential: credential for a channel connection
    */
    public function importUserInfo(
        $user_id,
        $market_id,
        $connection_id,
        $user_credential
    ) {
        $fulfillment = 0;

        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where([
            'connection_id' => $connection_id,
            'user_id' => $user_id,
            'market_id' => $market_id])->one();

        date_default_timezone_set("UTC");

        if(empty($user_connection_row)) {
            // create new UserConnection
            $new_user_connection_model = new UserConnection();
            $new_user_connection_model->user_id = $user_id;
            $new_user_connection_model->connection_id = $connection_id;
            $new_user_connection_model->market_id = $market_id;
            $new_user_connection_model->connection_info = json_encode($user_credential);
            $new_user_connection_model->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $new_user_connection_model->connected = UserConnection::CONNECTED_NO;
            $new_user_connection_model->smartling_status = 0;
            $new_user_connection_model->fulfillment_list_id = $fulfillment;
            $new_user_connection_model->created_at = date('Y-m-d h:i:s', time());
            $new_user_connection_model->save(false);
            $saved_user_connection_id = $new_user_connection_model->id;
        } else {
            //update the UserConnection row
            $user_connection_row->user_id = $user_id;
            $user_connection_row->connection_id = $connection_id;
            $user_connection_row->market_id = $market_id;
            $user_connection_row->connection_info = json_encode($user_credential);
            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $user_connection_row->connected = UserConnection::CONNECTED_NO;
            $user_connection_row->smartling_status = 0;
            $user_connection_row->fulfillment_list_id = $fulfillment;
            $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
            $user_connection_row->save(false);
            $saved_user_connection_id = $user_connection_row->id;
        }

        return $saved_user_connection_id;
    }

    /**
    * get country information with country name
    * @$country_name: country name
    */
    private function getCountryDetails($country_name) {
        $currency_code = 'USD';
        $country_code = 'US';
        $service_url = 'https://mws.amazonservices.com';//???
        $store_url = 'www.amazon.com';
        $marketplace_id = 'ATVPDKIKX0DER';

        switch ($country_name) {
            case 'Germany':
                $currency_code = 'EUR';
                $country_code = 'DE';
                $service_url = 'https://mws-eu.amazonservices.com';
                $store_url = 'www.amazon.de';
                $marketplace_id = 'A1PA6795UKMFR9';
                break;
            case 'Spain':
                $currency_code = 'EUR';
                $country_code = 'ES';
                $service_url = 'https://mws-eu.amazonservices.com';
                $store_url = 'www.amazon.es';
                $marketplace_id = 'A1RKKUPIHCS9HS';
                break;
            case 'France':
                $currency_code = 'EUR';
                $country_code = 'FR';
                $service_url = 'https://mws-eu.amazonservices.com';
                $store_url = 'www.amazon.fr';
                $marketplace_id = 'A13V1IB3VIYZZH';
                break;
            case 'Italy':
                $currency_code = 'EUR';
                $country_code = 'IT';
                $service_url = 'https://mws-eu.amazonservices.com';
                $store_url = 'www.amazon.it';
                $marketplace_id = 'APJ6JRA9NG5V4';
                break;
            case 'United Kingdom':
                $currency_code = 'GBP';
                $country_code = 'GB';
                $service_url = 'https://mws-eu.amazonservices.com';
                $store_url = 'www.amazon.co.uk';
                $marketplace_id = 'A1F83G8C2ARO7P';
                break;
            case 'India':
                $currency_code = 'INR';
                $country_code = 'IN';
                $service_url = 'https://mws.amazonservices.in';
                $store_url = 'www.amazon.in';
                $marketplace_id = 'A21TJRUUN4KGV';
                break;
            case 'China':
                $currency_code = 'CNY';
                $country_code = 'CN';
                $service_url = 'https://mws.amazonservices.com.cn';
                $store_url = 'www.amazon.cn';
                $marketplace_id = 'AAHKV2X7AFYLW';
                break;
            case 'Japan':
                $currency_code = 'JPY';
                $country_code = 'JP';
                $service_url = 'https://mws.amazonservices.jp';
                $store_url = 'www.amazon.co.jp';
                $marketplace_id = 'A1VC38T7YXB528';
                break;
            case 'Mexico':
                $currency_code = 'MXN';
                $country_code = 'MX';
                $service_url = 'https://mws.amazonservices.com.mx';
                $store_url = 'www.amazon.com.mx';
                $marketplace_id = 'A1AM78C64UM0Y8';
                break;
            case 'United States':
                $currency_code = 'USD';
                $country_code = 'US';
                $service_url = 'https://mws.amazonservices.com';
                $store_url = 'www.amazon.com';
                $marketplace_id = 'ATVPDKIKX0DER';
                break;
            default:
                return null;
        }

        return array(
            'currency_code' => $currency_code,
            'country_code' => $country_code,
            'service_url' => $service_url,
            'store_url' => $store_url,
            'marketplace_id' => $marketplace_id
        );
    }
}