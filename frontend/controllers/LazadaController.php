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
use frontend\models\LazadaConnectionForm;
use frontend\components\ChannelLazadaComponent;
use frontend\components\ConsoleRunner;

/**
 * LazadaController implements for Channels model.
 */
class LazadaController extends Controller {
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
    * index render page
    * @$id: id of the connection table
    */
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

        $model = new LazadaConnectionForm();
        // $re = \frontend\components\ChannelLazadaComponent::feedProduct('3', '2', false);
        // $re = \frontend\components\ChannelLazadaComponent::feedProduct('2', '1', false);
        // var_dump($re);
        // die;

        return $this->render('index', [
            'id' => $id,
            'is_connect_channel' => $is_connect_channel,
            'connection_name' => $connection_name,
            'model' => $model
        ]);
    }

    public function actionAuthorize($id) {
        $user_id = Yii::$app->user->identity->id;
        $model = new LazadaConnectionForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                // check if this connection is still existed in Connection table
                $connection_row = \common\models\Connection::find()->where(['id' => $id])->one();

                // connection is not exsited
                if(empty($connection_row)) {
                    throw new \Exception('There is no channel.');
                }

                $this->executeLazadaAPI(
                    Yii::$app->request->post('LazadaConnectionForm'),
                    $connection_row,
                    $user_id
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Your Lazada Channel has been connected successfully. Importing has started and you will be notified once it is completed.'
                ]);
            } catch(\Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credential.'
            ]);
        }
    }

    /**
    * execute the lazada api
    * @$form_variables: input form values
    * @$connection_data: connection row data
    * @$user_id: user id
    */
    private function executeLazadaAPI($form_variables, $connection_data, $user_id) {
        try {
            $user_connection_id = '';
            $country_info = $this->getCountryInfo($connection_data->name);
            $connection_parent_model = ConnectionParent::find()->where(['id' => $connection_data->parent_id])->one();
            if(empty($connection_parent_model)) {
                throw new \Exception('Invalid Connection.');
            }

            $store_name = $connection_parent_model->name.' '.$connection_data->name;
            $user_credential = array(
                'market_id' => $form_variables['email'],
                'api_url' => $country_info['api_url'],
                'api_key' => $form_variables['api_key']
            );

            $lazada = new \common\components\lazada\LazadaSellerCenter(
                $user_credential['api_url'],
                $user_credential['market_id'],
                $user_credential['api_key']
            );
            $lazada->Product()->GetCategoryTree();

            // update user info in UserConnection table
            $user_connection_id = $this->importUserInfo(
                $user_id,
                $user_credential['market_id'],
                $connection_data->id,
                $user_credential
            );
            // update user details in UserConnectionDetails table
            $user_connection_detail_row = $this->importUserDetail(
                $user_connection_id,
                $country_info,
                $store_name
            );
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();

            $importCr = new ConsoleRunner(['file' => '@console/yii']);
            $importCmd = 'channel-import/lazada '.$user_connection_id;
            $res = $importCr->run($importCmd);

            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $user_connection_row->save(true, ['import_status']);

        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            throw new \Exception($e->getMessage());
        } catch(\InvalidArgumentException $e) {
            UserConnection::setFailStatus($user_connection_id);
            throw new \Exception($e->getMessage());
        }
    }

    /**
    * save the data in UserConnectionDetails table
    * $user_connection_id: id in User table
    * $country_info: information based on country
    * $store_name: store name
    */
    public function importUserDetail($user_connection_id, $country_info, $store_name) {
        $country_row = Country::find()->where(['sortname' => $country_info['country_code']])->one();
        $country_name = '';
        $country_code = '';
        $currency_code = '';
        $currency_symbol = '';

        if(!empty($country_row)) {
            $country_name = $country_row->name;
            $country_code = $country_row->sortname;
            $currency_code = $country_row->currency_code;
            $currency_symbol = $country_row->currency_symbol;
        }

        $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model = new UserConnectionDetails();
        }

        $user_connection_detail_model->user_connection_id = $user_connection_id;
        $user_connection_detail_model->store_name = $store_name;
        $user_connection_detail_model->store_url = $country_info['store_url'];
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
            'user_id' => $user_id])->one();

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
    private function getCountryInfo($country_name) {
        $country_code = '';
        $store_url = '';
        $api_url = '';

        switch ($country_name) {
            case 'Malaysia':
                $country_code = 'MY';
                $store_url = 'lazada.com.my';
                $api_url = 'https://api.sellercenter.lazada.com.my';
                break;
            case 'Singapore':
                $country_code = 'SG';
                $store_url = 'lazada.sg';
                $api_url = 'https://api.sellercenter.lazada.sg';
                break;
            case 'Indonesia':
                $country_code = 'ID';
                $store_url = 'lazada.co.id';
                $api_url = 'https://api.sellercenter.lazada.co.id';
                break;
            case 'Thailand':
                $country_code = 'TH';
                $store_url = 'lazada.co.th';
                $api_url = 'https://api.sellercenter.lazada.co.th';
                break;
            case 'Vietnam':
                $country_code = 'VN';
                $store_url = 'lazada.vn';
                $api_url = 'https://api.sellercenter.lazada.vn';
                break;
            case 'Philippines':
                $country_code = 'PH';
                $store_url = 'lazada.com.ph';
                $api_url = 'https://api.sellercenter.lazada.com.ph';
                break;
            default:
                throw new \InvalidArgumentException('Invalid Country in Lazada Store.');
        }

        return array(
            'country_code' => $country_code,
            'store_url' => $store_url,
            'api_url' => $api_url
        );
    }
}
