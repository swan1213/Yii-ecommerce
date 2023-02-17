<?php

namespace frontend\controllers;

use common\models\Feed;
use common\models\Pinterest;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\UserFeed;
use frontend\components\ChannelFlipkartClient;
use frontend\components\ChannelFlipkartComponent;
use frontend\components\ChannelJetClient;
use frontend\components\ChannelJetComponent;
use frontend\components\ConsoleRunner;
use frontend\components\Helpers;
use frontend\models\RakutenConnectionForm;
use Yii;
use common\models\User;
use frontend\models\search\ChannelSearch;
use common\models\Connection;
use common\models\Product;
use common\models\Order;
use common\models\OrderProduct;

use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\components\BaseController;

/**
 * ChannelsController implements the CRUD actions for Channels model.
 */
class ChannelsController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'update', 'auth-jet', 'auth-flipkart'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index', 'view', 'create', 'update', 'jet', 'auth-jet', 'flipkart', 'auth-flipkart'],
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

    public function actionIndex() {

        $currentUserId = Yii::$app->user->identity->id;

	    $searchModel = new ChannelSearch();
	    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

	    $parentChannelData = Connection::find()->channel()->groupBy('parent_id')->all();
        $showChannelsData = [];
        foreach ($parentChannelData as $eachParentData){
            if($eachParentData->enabled==Connection::CONNECTED_ENABLED_NO)
                continue;
            if($eachParentData->id==1)
                continue;

            $channelProductCnt = 0;
            $channelRevenue = 0;
            $channelSales = 0;
            $status = Connection::CONNECT_STATUS_GET;
            if($eachParentData->name == "Instagram")
                $status = Connection::CONNECT_STATUS_COMINGSOON;
            $connection_url = "";
            $childChannelIds = Connection::findChildConnections($eachParentData->parent_id);
            $childChannelCnt = count($childChannelIds);
            $childChannels = [];
            if ( $childChannelCnt > 1 ) {
                $channel_connections = Connection::findAll(['parent_id' => $eachParentData->parent_id]);

                foreach ($channel_connections as $each_channel_connection) {

                    $channel_connection_id = $each_channel_connection->id;
                    $user_connected_channels_count = UserConnection::find()
                        ->where(['user_id' => $currentUserId, 'connection_id' => $channel_connection_id])
                        ->count();
                    $oneChildChannelConnection = [
                        'connected_count' => $user_connected_channels_count,
                        'name' => $each_channel_connection->getConnectionName(),
                        'link_url' => $each_channel_connection->generateConnectionFormLink(),
                    ];
                    $childChannels[] = $oneChildChannelConnection;

                }
            } else {
                $channel_connection = Connection::find()->where(['parent_id' => $eachParentData->parent_id])->one();
                //$connection_url = $channel_connection->generateConnectionFormLink($eachParentData->parent_id);
                $connection_url = $channel_connection->generateConnectionFormLink();
            }

            $userChannels = UserConnection::findIdsByUserIdandConnectionIds($currentUserId, $childChannelIds);
            if ( count($userChannels) > 0 ) {
                $channelProductCnt = Product::find()
                    ->joinWith(['productConnections'])
                    ->Where(['product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO])
                    ->andWhere(['in', 'product_connection.user_connection_id', $userChannels])
                    ->count();
                $channelOrder = Order::find()->where(['in', 'user_connection_id', $userChannels]);
                $channelRevenue = $channelOrder->sum('total_amount');
                $channelSales = $channelOrder->sum('product_quantity');
                $status = Connection::CONNECT_STATUS_CONNECTED;
            }

            if($eachParentData->parent->name == "Facebook"){

                $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
                $fb_feed_count = UserFeed::find()->where(["feed_id"=>$fb_feed_model->id, "user_id"=>$currentUserId])->count();
                if($fb_feed_count>0)
                    $status = Connection::CONNECT_STATUS_CONNECTED;
                else
                    $status = Connection::CONNECT_STATUS_GET;
            }

            if($eachParentData->parent->name == "Pinterest"){
                $p_feed_model = Feed::findOne(["name"=>"pinterest"]);
                $p_feed_count = UserFeed::find()->where(["feed_id"=>$p_feed_model->id, "user_id"=>$currentUserId])->count();
                if($p_feed_count>0)
                    $status = Connection::CONNECT_STATUS_CONNECTED;
                else
                    $status = Connection::CONNECT_STATUS_GET;
            }
            $oneShowData = [
                'parentId' => $eachParentData->parent_id,
                'channel_image' => $eachParentData->getConnectionImage(),
                'name' => $eachParentData->parent->name,
                'channelProductCnt' => $channelProductCnt,
                'channelRevenue' => $channelRevenue,
                'channelSales' => $channelSales,
                'status' => $status,
                'connected_url' => $connection_url,
                'connection_child_count' => $childChannelCnt,
                'childChannels' => $childChannels
            ];

            $showChannelsData[] = $oneShowData;
        }

	    return $this->render('index', [
		    'searchModel' => $searchModel,
		    'dataProvider' => $dataProvider,
            'currentUserId' => $currentUserId,
            'parentChannelData' => $parentChannelData,
            'showChannelsData' => $showChannelsData,
	    ]);
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionView($id) {
        return $this->render('view', [
                'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate() {
	    $model = new Connection();

	    if ($model->load(Yii::$app->request->post()) && $model->save()) {
	        return $this->redirect(['view', 'id' => $model->id]);
	    } else {
	        return $this->render('create', [
			    'model' => $model,
	        ]);
	    }
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->channel_ID]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id) {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id) {
        if (($model = Connection::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function isValidChannel($customer_id) {
        $checkcustomer = CustomerAbbrivation::find()->Where(['customer_id' => $customer_id])->all();
        if (!empty($checkcustomer)) {
            foreach ($checkcustomer as $_customer) {
                $channel_name = $_customer->channel_accquired;
                return $this->isValidChannelName($channel_name);
            }
        }
        return false;
    }

    public static function isValidChannelName($channel_name) {
        $channel_data = Channels::find()->where(['channel_name' => $channel_name])->one();
        if (!empty($channel_data)) {
            if (Helpers::isExistSubChannelItem(Yii::$app->session->get("channel_permission", $channel_data->stripe_Channel_id))) {
                return true;
            }
        }
        return false;
    }

    public function getAllStoreName() {
        $stores_names = Stores::find()->select('store_name')->asArray()->all();
        $store_array = array();
        foreach ($stores_names as $_store) {
            $store_array[] = $_store['store_name'];
        }
        return $store_array;
    }
    
    public static function getStartRating($customer_id,$customer_email,$total_cus){
        $currentUserId = Yii::$app->user->identity->id;
        $connection = \Yii::$app->db;
        $star_count = 1;
        if($customer_email!=''){
            $star_count=2;/*For One Star By default*/
        }
        $order_count=Order::find()->Where(['customer_id'=>$customer_id, 'user_id'=>$currentUserId])->count();
        if($order_count>=1 && $order_count<=10){
            $star_count=3;
        }
        $order_distinct=Order::find()->Where(['customer_id'=>$customer_id, 'user_id'=>$currentUserId])->distinct()->count();
        if($order_distinct>1 || $order_count>10){
            $star_count=4;
        }

        $total_customer_5_star = ceil($total_cus / 100 * 10);
        $model = $connection->createCommand('SELECT `orderSum`.* FROM `customer` Left JOIN 
                    (SELECT `customer_id`, SUM(total_amount) as order_total FROM `order` GROUP BY `customer_id`)
                    `orderSum` ON orderSum.customer_id = customer.id  ORDER BY order_total DESC limit 0, '.$total_customer_5_star);
        $clv_data = $model->queryAll();
        foreach($clv_data as $_clv_data){
            if($_clv_data['customer_id']==$customer_id){
                $order_total = $_clv_data['order_total'];
                if($order_total>=5){
                    $star_count=5;
                }
                break;
            }
        }
        return $star_count;
    }

    public function actionJet(){

        $user_id = Yii::$app->user->identity->id;
        $jetConnection = Connection::findOne(['name' => 'Jet']);
        $jetId = $jetConnection->id;
        $user_JetChannel = UserConnection::findOne(['user_id' => $user_id, 'connection_id' => $jetId]);


        return $this->render('jet', [
            'channel' => $user_JetChannel,
            'user_id' => $user_id,
            'connection_id' => $jetId,
        ]);
    }

    public function actionAuthJet(){

        $postData = Yii::$app->request->post();

        $jet_api_user = trim($postData['jet_api_user']);
        $jet_secret_key = trim($postData['jet_secret_key']);
        $jet_merchant_id = trim($postData['jet_merchant_id']);
        $user_id = trim($postData['user_id']);
        $connection_id = trim($postData['connection_id']);

        $array_msg = array();

        $jetClientInfo = [
            'apiUser' => $jet_api_user,
            'apiPass' => $jet_secret_key,
        ];

        $jetClientInfoJson = @json_encode($jetClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $jetClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Jet API Info has already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }

        $jetClient = new ChannelJetClient($jetClientInfo);

        if ( $jetClient->checkValidate() ) {

            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connection_id;
            $connectionModel->user_id = $user_id;
            $connectionModel->market_id = $jet_merchant_id;
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->connection_info = $jetClientInfoJson;
            if ($connectionModel->save(false)) {

                $userConnectionId = $connectionModel->id;

                ChannelJetComponent::channelInfoDetail($userConnectionId);

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'channel-import/jet '.$userConnectionId;
                $importCr->run($importCmd);

                $array_msg['success'] = "Your Jet Channel has been connected successfully. Importing has started and you will be notified once it is completed.";

            } else {

                $array_msg['api_error'] = "Error Something went wrong. Please try again";

            }

        } else {

            $array_msg['api_error'] = 'Your API credentials is not working. Please check your API data and try again';

        }


        return json_encode($array_msg);

    }

    public function actionFlipkart(){

        $user_id = Yii::$app->user->identity->id;
        $flipkartConnection = Connection::findOne(['name' => 'Flipkart']);
        $flipkartId = $flipkartConnection->id;
        $user_FlipkartChannel = UserConnection::findOne(['user_id' => $user_id, 'connection_id' => $flipkartId]);


        if ( !empty($user_FlipkartChannel) ){

            $user_connection_id = $user_FlipkartChannel->id;

            return $this->render('flipkart-upload', [
               'user_connection_id' => $user_connection_id
            ]);

        }

        return $this->render('flipkart', [
            'channel' => $user_FlipkartChannel,
            'user_id' => $user_id,
            'connection_id' => $flipkartId,
        ]);

    }

    public function actionAuthFlipkart(){

        $postData = Yii::$app->request->post();

        $flipkart_app_id = trim($postData['flipkart_app_id']);
        $flipkart_app_secret = trim($postData['flipkart_app_secret']);
        $user_id = trim($postData['user_id']);
        $connection_id = trim($postData['connection_id']);

        $array_msg = array();

        $flipkartClientInfo = [
            'appId' => $flipkart_app_id,
            'appSecret' => $flipkart_app_secret,
        ];

        $flipkartClientInfoJson = @json_encode($flipkartClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $flipkartClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Flipkart API Info has already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }

        $flipkartClient = new ChannelFlipkartClient($flipkartClientInfo);

        if ( $flipkartClient->checkValidate() ) {

            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connection_id;
            $connectionModel->user_id = $user_id;
            $connectionModel->market_id = '';
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->connection_info = $flipkartClientInfoJson;
            if ($connectionModel->save(false)) {

                $userConnectionId = $connectionModel->id;

                ChannelFlipkartComponent::channelInfoDetail($userConnectionId);

                $array_msg['success'] = "Your Flipkart Channel has been connected successfully. Please, upload your Products XLS and Orders CSV files.";
                $array_msg['user_connection_id'] = $userConnectionId;

            } else {

                $array_msg['api_error'] = "Error Something went wrong. Please try again";

            }

        } else {

            $array_msg['api_error'] = 'Your API credentials is not working. Please check your API data and try again';

        }


        return json_encode($array_msg);

    }

    public function actionUploadFlipkart(){

        $getData = Yii::$app->request->get();

        if ( !empty($getData) && isset($getData['connection']) ){

            $user_connection_id = $getData['connection'];

            return $this->render('flipkart-upload', [
                'user_connection_id' => $user_connection_id
            ]);

        }

        return $this->redirect('/channels/flipkart');
    }

    public function actionUploadFlipkartXls(){

        $response_msg = array();

        $user_connection_id = 0;
        $postData = Yii::$app->request->post();
        if ( !empty($postData) && isset($postData['user_connection_id']) ){
            $user_connection_id = $postData['user_connection_id'];
        } else {
            $response_msg['success'] = false;
            $response_msg['message'] = "Unknown Connection";

            return json_encode($response_msg);
        }

        if (isset($_FILES['file']['name'])) {
            if (0 < $_FILES['file']['error']) {

                $response_msg['success'] = false;
                $response_msg['message'] = 'Error during upload' . $_FILES['file']['error'];

            } else {

                $response_msg['message'] = "This products XLS file already uploaded, please another Product XLS file.";
                $uploadTargetDir = Yii::getAlias('@storage') . '/web/source/flipkart/'.$user_connection_id.'/';

                if (!file_exists($uploadTargetDir)) {
                    @mkdir($uploadTargetDir, 0777, true);
                }

                if (!file_exists($uploadTargetDir . $_FILES['file']['name'])) {

                    $uploadedProductXls = $uploadTargetDir . str_replace(" ", "", $_FILES['file']['name']);
                    @move_uploaded_file($_FILES['file']['tmp_name'], $uploadedProductXls);

                    $importCr = new ConsoleRunner(['file' => '@console/yii']);
                    $importCmd = 'channel-import/flipkart-parse-products ' . $user_connection_id . " " . $uploadedProductXls;
                    $importCr->run($importCmd);

                    //$response_msg['message'] = "Flipkart products importing has started and you will be notified once it is completed.";
                    $response_msg['message'] = "Flipkart importing has started and you will be notified once it is completed.";
                }

                $response_msg['success'] = true;


            }
        } else {

            $response_msg['success'] = false;
            $response_msg['message'] = "Please upload a XLS file.";

        }

        return json_encode($response_msg);
    }

    public function actionUploadFlipkartCsv(){

        $response_msg = array();

        $user_connection_id = 0;
        $postData = Yii::$app->request->post();
        if ( !empty($postData) && isset($postData['user_connection_id']) ){
            $user_connection_id = $postData['user_connection_id'];
        } else {
            $response_msg['success'] = false;
            $response_msg['message'] = "Unknown Connection";

            return json_encode($response_msg);
        }

        if (isset($_FILES['file']['name'])) {
            if (0 < $_FILES['file']['error']) {

                $response_msg['success'] = false;
                $response_msg['message'] = 'Error during upload' . $_FILES['file']['error'];

            } else {

                $uploadTargetDir = Yii::getAlias('@storage') . '/web/source/flipkart/'.$user_connection_id.'/';

                if (!file_exists($uploadTargetDir)) {
                    @mkdir($uploadTargetDir, 0777, true);
                }

                $uploadedProductCsv = $uploadTargetDir . time() . "_" . str_replace(" ", "", $_FILES['file']['name']);
                if (!file_exists($uploadedProductCsv)) {


                    @move_uploaded_file($_FILES['file']['tmp_name'], $uploadedProductCsv);

                    $importCr = new ConsoleRunner(['file' => '@console/yii']);
                    $importCmd = 'channel-import/flipkart-parse-orders ' . $user_connection_id . " " . $uploadedProductCsv;
                    $importCr->run($importCmd);


                }

                $response_msg['success'] = true;
                $response_msg['message'] = "Flipkart orders importing has started and you will be notified once it is completed.";

            }
        } else {

            $response_msg['success'] = false;
            $response_msg['message'] = "Please upload a CSV file.";

        }

        return json_encode($response_msg);

    }


    public function actionRakuten(){

        $user_id = Yii::$app->user->identity->id;
        $model = new RakutenConnectionForm();
        $error_status = false;
        $new_auth = false;
        $error_msg = "";
        $rakutenConnection = Connection::findOne(['name' => 'Rakuten']);
        $rakutenId = $rakutenConnection->id;
        if ($model->load(Yii::$app->request->post())){
            $userRakutenChannel = UserConnection::findOne(['user_id' => $user_id, 'connection_id' => $rakutenId]);
            if(empty($userRakutenChannel)) {
                if ($model->checkAuth()) {
                    $rakutenClientInfo = [
                        'sevice_secret' => $model->sevice_secret,
                        'license_key' => $model->license_key
                    ];
                    $rakutenClientInfoJson = @json_encode($rakutenClientInfo, JSON_UNESCAPED_UNICODE);

                    $connectionModel = new UserConnection();
                    $connectionModel->connection_id = $rakutenId;
                    $connectionModel->user_id = $user_id;
                    $connectionModel->market_id = "";
                    $connectionModel->connected = UserConnection::CONNECTED_YES;
                    $connectionModel->connection_info = $rakutenClientInfoJson;
                    if ($connectionModel->save(false)) {

                        $userConnectionId = $connectionModel->id;
                        $user_Rakuten_connection = UserConnectionDetails::findOne(
                            [
                                'user_connection_id' => $userConnectionId
                            ]
                        );
                        if (empty($user_Rakuten_connection)) {
                            $user_Rakuten_connection = new UserConnectionDetails();
                            $user_Rakuten_connection->user_connection_id = $userConnectionId;
                        }


                        $country_code = "JP";
                        $currency = "JPY";
                        $currency_symbol = "¥";
                        $country_name = "Japan";


                        $user_Rakuten_connection->store_name = $connectionModel->connection->name;
                        $user_Rakuten_connection->store_url = "";
                        //$user_Rakuten_connection->country = $country_name;
                        $user_Rakuten_connection->country_code = $country_code;
                        $user_Rakuten_connection->currency = $currency;
                        $user_Rakuten_connection->currency_symbol = $currency_symbol;

                        $userConnectionSettings = $user_Rakuten_connection->settings;

                        if (empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
                            $userConnectionSettings['currency'] = $user_Rakuten_connection->currency;
                        }

                        $user_Rakuten_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);
                        $user_Rakuten_connection->others = $rakutenClientInfoJson;


                        $user_Rakuten_connection->save(false);

                        $importCr = new ConsoleRunner(['file' => '@console/yii']);
                        $importCmd = 'channel-import/rakuten ' . $userConnectionId;
                        $importCr->run($importCmd);

                        $new_auth = true;
                    } else {
                        $error_status = true;
                        $error_msg = "Error Something went wrong. Please try again";
                    }
                } else {
                    $error_status = true;
                    $error_msg = "Authorization Error!";
                }
            }
        }
        $userRakutenChannel = UserConnection::findOne(['user_id' => $user_id, 'connection_id' => $rakutenId]);

        return $this->render('rakuten', [
            'userConnection' => $userRakutenChannel,
            'user_id' => $user_id,
            'connection_id' => $rakutenId,
            'model' => $model,
            'error_status' => $error_status,
            'error_msg' => $error_msg,
            'new_auth' => $new_auth,
        ]);

    }


}
