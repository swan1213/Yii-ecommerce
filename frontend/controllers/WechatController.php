<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

use common\models\Connection;
use common\models\ConnectionParent;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\WechatConnectionForm;
use frontend\models\WalkthechatConnectionForm;
use frontend\components\CustomFunction;
use frontend\components\ConsoleRunner;


/**
 * WechatController for Channels model.
 */
class WechatController extends Controller {
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

        $wechat_model = new WechatConnectionForm();
        $walkthechat_model = new WalkthechatConnectionForm();
        // \frontend\components\ChannelWechatComponent::updateFeed(2, 3, true);

        return $this->render('index', [
            'id' => $id,
            'is_connect_channel' => $is_connect_channel,
            'connection_name' => $connection_name,
            'wechat_model' => $wechat_model,
            'walkthechat_model' => $walkthechat_model
        ]);
    }

    public function actionAuthorizeWechat($id) {
        try {
            $wechat_post_variable = Yii::$app->request->post('WechatConnectionForm');

            $wechat_user_credential = array(
                'client_id' => $wechat_post_variable['client_id'],
                'client_secret' => $wechat_post_variable['client_secret'],
                'market_id' => $wechat_post_variable['client_id'],
                'type' => 'wechat'
            );

            $connection_row = Connection::find()->where(['id' => $id])->one();

            $this->executeWechatApiCall(
                $wechat_user_credential,
                $connection_row
            );

            echo json_encode([
                'success' => true,
                'message' => 'Your Wechat Channel has been connected successfully. Importing has started and you will be notified once it is completed.'
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
        }
    }

    public function actionAuthorizeWalkthechat($id) {
        try {
            $walkthechat_post_variable = Yii::$app->request->post('WalkthechatConnectionForm');
            $walkthechat_user_credential = array(
                'username' => $walkthechat_post_variable['username'],
                'password' => $walkthechat_post_variable['password'],
                'market_id' => '',
                'type' => 'walkthechat'
            );

            $connection_row = Connection::find()->where(['id' => $id])->one();
                
            $this->executeWalkthechatApiCall(
                $walkthechat_user_credential,
                $connection_row
            );

            echo json_encode([
                'success' => true,
                'message' => 'Your Walkthe Channel has been connected successfully. Importing has started and you will be notified once it is completed.'
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
        }
    }

    /**
    *execute api calls for a wechat connection
    * @$user_credential: user credential
    * @$connection_data: connection data in Connection table
    */
    private function executeWalkthechatApiCall(
        $user_credential,
        $connection_data
    ) {
        $user_id = Yii::$app->user->identity->id;
        $user_connection_id = '';

        try {
            $connection_parent_model = ConnectionParent::find()->where(['id' => $connection_data->parent_id])->one();
            if(empty($connection_parent_model)) {
                throw new \Exception('Invalid Connection.');
            }

            $store_name = $connection_parent_model->name.' '.$connection_data->name;

            $url = 'https://cms-api.walkthechat.com/login/admin';
            $post_data = array(
                'username' => $user_credential['username'],
                'password' => $user_credential['password']
            );

            $response_data = CustomFunction::curlHttp($url, $post_data, 'POST');
            $json_data = json_decode($response_data, true);

            if(isset($json_data['error']) and $json_data['error']['success'] == false) {
                throw new \Exception($json_data['error']['message']);
            }

            $user_connection_id = $this->importUserInfo(
                $user_id,
                $user_credential['market_id'],
                $connection_data->id,
                $user_credential
            );
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            $this->importUserDetail($user_connection_id, $store_name);

            $importCr = new ConsoleRunner(['file' => '@console/yii']);
            $importCmd = 'channel-import/walkthechat '.$user_connection_id;
            $res = $importCr->run($importCmd);

            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $user_connection_row->save(true, ['import_status']);
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            throw new \Exception($e->getMessage());
        }
    }

    public function importUserDetail($user_connection_id, $store_name) {
        $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model = new UserConnectionDetails();
        }

        $user_connection_detail_model->user_connection_id = $user_connection_id;
        $user_connection_detail_model->store_name = $store_name;
        $user_connection_detail_model->store_url = 'http://www.wechat.com';
        $user_connection_detail_model->country = 'China';
        $user_connection_detail_model->country_code = 'CN';
        $user_connection_detail_model->currency = 'CNY';
        $user_connection_detail_model->currency_symbol = '¥';
        $user_connection_detail_model->others = '';
        $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
        $user_connection_detail_model->save(false);
    }

    public function importUserInfo(
        $user_id,
        $market_id,
        $connection_id,
        $user_credential
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
            $new_user_connection_model->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $new_user_connection_model->connected = UserConnection::CONNECTED_NO;
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
            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $user_connection_row->connected = UserConnection::CONNECTED_NO;
            $user_connection_row->smartling_status = 0;
            $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
            $user_connection_row->save(false);
            $saved_user_connection_id = $user_connection_row->id;
        }

        return $saved_user_connection_id;
    }
    

    /**
    *execute api calls for a wechat connection
    * @$user_credential: user credential
    * @$connection_data: connection data in Connection table
    */
    private function executeWechatApiCall(
        $user_credential,
        $connection_data
    ) {
        try {
            $user_connection_id = '';
            $user_id = Yii::$app->user->identity->id;
            $connection_parent_model = ConnectionParent::find()->where(['id' => $connection_data->parent_id])->one();
            if(empty($connection_parent_model)) {
                throw new \Exception('Invalid Connection.');
            }

            $store_name = $connection_parent_model->name.' '.$connection_data->name;

            $this->getAccessToken($user_credential['client_id'], $user_credential['client_secret']);
            $user_connection_id = $this->importUserInfo(
                $user_id,
                $user_credential['market_id'],
                $connection_data->id,
                $user_credential
            );
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            $this->importUserDetail($user_connection_id, $store_name);

            $importCr = new ConsoleRunner(['file' => '@console/yii']);
            $importCmd = 'channel-import/wechat '.$user_connection_id;
            $res = $importCr->run($importCmd);

            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
            $user_connection_row->save(true, ['import_status']);
        } catch (\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            throw new \Exception($e->getMessage());
        }
    }

    /**
    * Getting access_token from customize menus
    * @$appid: app id for wechat api
    * @$secret: secret for wechat api
    */
    private function getAccessToken($appid, $secret){  
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;  
        $json = CustomFunction::curlHttp($url);//here cannot use file_get_contents  
        $data = json_decode($json, true);
        
        if(isset($data['access_token']) and $data['access_token']){
            return $data['access_token'];  
        } else {  
            throw new \Exception($data['errmsg']);
        }
    }
}