<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use common\models\Connection;
use common\models\Country;
use common\models\ProductConnection;
use common\models\User;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\SquareConnectionForm;
use frontend\models\SouqPublishForm;
use frontend\components\ConsoleRunner;
use frontend\components\PosSquareComponent;


class SquareController extends \yii\web\Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'test', 'square-auth', 'square-importing', 'square-webhook-response', 'square-cron-importing'],
                'rules' => [
                    [
                        /* action hit without log-in */
                        'actions' => ['login', 'test', 'square-auth', 'square-importing', 'square-webhook-response', 'square-cron-importing'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        /* action hit only with log-in */
                        'actions' => ['index', 'test', 'square-auth', 'square-importing', 'square-webhook-response', 'square-cron-importing'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
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

        $connection_list = [];
        if(!empty($user_connection_row) and 
            ($user_connection_row->connected == UserConnection::CONNECTED_YES or 
            $user_connection_row->import_status == UserConnection::IMPORT_STATUS_PROCESSING)) {
            $is_connect_channel = true;
            $parent_connection_rows = 
            $connection_rows = Connection::find()->where(['type_id' => array(0, 1, 2)])->all();
            $user_connection_rows = UserConnection::find()->select('connection_id')->where(['user_id' => $user_id])->all();
            $connection_id_list = array_column($user_connection_rows, 'connection_id');

            foreach ($connection_rows as $_connection) {
                if(in_array($_connection['id'], $connection_id_list)) {
                    if(!empty($_connection->parent) and count($_connection->parent->childConnections) > 1) {
                        $connection_list[$_connection['id']] = $_connection->parent->name.' '.$_connection['name'];
                    } else {
                        $connection_list[$_connection['id']] = $_connection['name'];
                    }
                }
            }
        }

        $model = new SquareConnectionForm();
        $publish_model = new SouqPublishForm();

        return $this->render('index', [
            'id' => $id,
            'is_connect_channel' => $is_connect_channel,
            'connection_name' => $connection_name,
            'model' => $model,
            'publish_model' => $publish_model,
            'connection_list' => $connection_list
        ]);
    }

    public function actionPublish($id) {
        $user_id = Yii::$app->user->identity->id;
        $publish_model = new SouqPublishForm();

        if ($publish_model->load(Yii::$app->request->post()) && $publish_model->validate()) {
            $post_variable = Yii::$app->request->post('SouqPublishForm');
            $connections = $post_variable['connections'];
            
            $user_connection_rows = UserConnection::find()->where([
                'user_id' => $user_id,
                'connection_id' => $connections
            ])->all();
            $user_connection_id_list = array_column($user_connection_rows, 'id');

            foreach ($user_connection_id_list as $user_connection_id) {
                $product_connection_rows = ProductConnection::find()->where([
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id
                ])->all();
                $product_id_list = array_column($product_connection_rows, 'connection_product_id');

                if(!empty($product_id_list) and count($product_id_list) > 0) {
                    foreach ($product_id_list as $product_id) {
                        PosSquareComponent::feedProduct($user_connection_id, $product_id, false);
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Products export to square is successful'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill all fields required'
            ]);
        }

    }

    public function actionAuthorize($id) {
        $user_id = Yii::$app->user->identity->id;
        $model = new SquareConnectionForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $post_variable = Yii::$app->request->post('SquareConnectionForm');

            try {
                $connection_row = \common\models\Connection::find()->where(['id' => $id])->one();
                if(empty($connection_row)) {
                    throw new \Exception('There is no square channel.');
                }

                \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($post_variable['access_token']);
                $api = new \SquareConnect\Api\LocationsApi();
                $response = $api->listLocations();

                if(empty($response['locations']) or count($response['locations']) == 0) {
                    throw new \Exception('Your account business location is empty.');
                }

                $user_credential = array(
                    'app_id' => $post_variable['app_id'],
                    'access_token' => $post_variable['access_token'],
                    'location_id' => $response['locations'][0]['id']
                );

                $user_connection_id = $this->importUserInfo(
                    $user_id,
                    $response['locations'][0]['merchant_id'],
                    $connection_row->id,
                    $user_credential
                );
                $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
                $user_connection_detail_instance = $this->importUserDetail(
                    $user_connection_id,
                    $response['locations'][0]['country'],
                    $response['locations'][0]['website_url']
                );

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'channel-import/square '.$user_connection_id;
                $res = $importCr->run($importCmd);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
                $user_connection_row->save(true, ['import_status']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Your Square POS has been connected successfully. Importing has started and you will be notified once it is completed.'
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
                'message' => 'Please fill all fields required'
            ]);
        }
    }

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

    public function importUserDetail(
        $user_connection_id,
        $country_code,
        $website_url
    ) {
        $country_row = Country::find()->where(['sortname' => $country_code])->one();
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
        $user_connection_detail_model->store_name = 'Square';
        $user_connection_detail_model->store_url = empty($website_url)?'https://squareup.com/shop':$website_url;
        $user_connection_detail_model->country = $country_name;
        $user_connection_detail_model->country_code = $country_code;
        $user_connection_detail_model->currency = $currency_code;
        $user_connection_detail_model->currency_symbol = $currency_symbol;
        $user_connection_detail_model->others = '';

        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
            $user_connection_detail_model->updated_at = date('Y-m-d H:i:s', time());
        } else {
            $user_connection_detail_model->updated_at = date('Y-m-d H:i:s', time());
        }
        $user_connection_detail_model->save(false);

        return $user_connection_detail_model;
    }
}        