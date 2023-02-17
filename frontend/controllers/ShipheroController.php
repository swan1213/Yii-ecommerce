<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 1/15/2018
 * Time: 11:28 AM
 */

namespace frontend\controllers;


use common\models\Connection;
use common\models\Country;
use common\models\Crontask;
use common\models\Fulfillment;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\components\ChannelShipheroComponent;
use frontend\components\ConsoleRunner;
use frontend\components\ShipheroComponent;
use frontend\models\ShipheroConnectionForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ShipheroController extends Controller
{
    /**
     * action filter
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

    public function actionIndex($id) {
        $error_msg = '';
        $error_status = false;
        $is_connect_channel = false;
        $connect_status = UserConnection::IMPORT_STATUS_FAIL;
        $user_id = Yii::$app->user->identity->id;

        // check if this connection is still existed in Connection table
        $connection_row = Connection::find()->where(['parent_id' => $id])->one();

        // connection is not exsited
        if(empty($connection_row)) {
            return $this->redirect(['/channels']);
        }

        $connection_name = $connection_row->name;
        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where(['connection_id' => $connection_row->id, 'user_id' => $user_id])->one();

        // the connection is already existed
        if(!empty($user_connection_row)) {
            if($user_connection_row->connected == UserConnection::CONNECTED_YES) {
                $is_connect_channel = true;
            } else {
                $connect_status = $user_connection_row->import_status;
            }
        }

        $model = new ShipheroConnectionForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $post_variable = Yii::$app->request->post('ShipheroConnectionForm');
            $user_credential = array(
                'api_key' => $post_variable['api_key'],
                'api_secret' => $post_variable['api_secret']
            );

            try {
                $user_connection_id = $this->importUserInfo(
                    $user_id,
                    '',
                    $connection_row->id,
                    $user_credential
                );
                $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
                $this->importUserDetail($user_connection_id, 240);

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'channel-import/shiphero '.$user_connection_id;
                $res = $importCr->run($importCmd);

                $task = new Crontask();
                $task->name = 'Shiphero';
                $task->action = 'channel-import/shiphero';
                $param = [$user_connection_id, false];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
                $user_connection_row->save(true, ['import_status']);
                $connect_status = UserConnection::IMPORT_STATUS_PROCESSING;

            } catch(\Exception $e) {
                UserConnection::setFailStatus($user_connection_id);
                $error_status = true;
                $error_msg = $e->getMessage();
            }
        }

        // either the page is initially displayed or there is some validation error
        return $this->render('index', [
            'model' => $model,
            'error_status' => $error_status,
            'error_msg' => $error_msg,
            'is_connect_channel' => $is_connect_channel,
            'connect_status' => $connect_status
        ]);
    }

    /**
     * save the data in UserConnectionDetails table
     * $user_connection_id: id in User table
     * $country_id id: id in Country table
     */
    public function importUserDetail($user_connection_id, $country_id) {
        $country_row = Country::find()->where(['id' => $country_id])->one();
        $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model = new UserConnectionDetails();
        }

        $user_connection_detail_model->user_connection_id = $user_connection_id;
        $user_connection_detail_model->store_name = 'Shiphero';
        $user_connection_detail_model->store_url = 'https://shiphero.com';
        $user_connection_detail_model->country = $country_row->name;
        $user_connection_detail_model->country_code = $country_row->sortname;
        $user_connection_detail_model->currency = $country_row->currency_code;
        $user_connection_detail_model->currency_symbol = $country_row->currency_symbol;
        $user_connection_detail_model->others = '';
        $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
        $user_connection_detail_model->save(false);
    }

    /**
     * save the data in UserConnection table
     * $user_id: id in User table
     * $market id: market id
     * $connection_id: id in Connection table
     * $user_credential: credential for a channel connection
     */
    public function importUserInfo($user_id, $market_id, $connection_id, $user_credential
    ) {
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
            $new_user_connection_model->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $new_user_connection_model->connected = UserConnection::CONNECTED_YES;
            $new_user_connection_model->smartling_status = 0;
            $new_user_connection_model->created_at = date('Y-m-d h:i:s', time());
            $new_user_connection_model->save(false);
            $saved_user_connection_id = $new_user_connection_model->id;
        } else {
            //update the UserConnection row
            $user_connection_row->user_id = $user_id;
            $user_connection_row->connection_id = $connection_id;
            $user_connection_row->market_id = $market_id;
            $user_connection_row->connection_info = json_encode($user_credential);
            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $user_connection_row->connected = UserConnection::CONNECTED_YES;
            $user_connection_row->smartling_status = 0;
            $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
            $user_connection_row->save(false);
            $saved_user_connection_id = $user_connection_row->id;
        }

        return $saved_user_connection_id;
    }

    public static function sendOrder($user_fulfillment_id) {

        $shipheroConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);

        if (!empty($shipheroConection)) {

            $connection_info = $shipheroConection->connection_info;
            $shipheroComponent = new ShipheroComponent($connection_info);

            $connection_user = $shipheroConection->user;
            $user_id = $connection_user->id;
            $fulfillment_list_id = $shipheroConection->fulfillment_list_id;

            $shiphero_userConnection = UserConnection::findAll(['user_id' => $user_id, 'fulfillment_list_id' => $fulfillment_list_id]);

            if (!empty($shiphero_userConnection)) {
                foreach ($shiphero_userConnection as $each_userConnection) {
                    $user_connection_id = $each_userConnection->id;
                    $shipheroComponent->sendOrder($user_connection_id, $user_fulfillment_id);
                }
            }
        }
    }

    public static function actionTest() {

        $user_fulfillment_id = 3;
        $shipheroConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);

        if (!empty($shipheroConection)) {

            $connection_info = $shipheroConection->connection_info;
            $shipheroComponent = new ShipheroComponent($connection_info);

            $connection_user = $shipheroConection->user;
            $user_id = $connection_user->id;
            $fulfillment_list_id = $shipheroConection->fulfillment_list_id;

            $shiphero_userConnection = UserConnection::findAll(['user_id' => $user_id, 'fulfillment_list_id' => $fulfillment_list_id]);

            if (!empty($shiphero_userConnection)) {
                foreach ($shiphero_userConnection as $each_userConnection) {
                    $user_connection_id = $each_userConnection->id;
                    $shipheroComponent->sendOrder($user_connection_id, $user_fulfillment_id);
                }
            }
        }
    }
}