<?php

namespace frontend\controllers;

use console\controllers\StoreImportController;
use frontend\components\ReactionComponent;
use frontend\components\ReactionSimpleRestClient;
use frontend\components\VtexClient;
use common\models\Country;
use common\models\CurrencySymbol;
use common\models\UserConnectionDetails;
use frontend\components\BigcommerceComponent;
use frontend\components\MagentoComponent;
use frontend\components\ShopifyApiException;
use frontend\components\ShopifyComponent;
use frontend\components\ShopifyCurlException;
use frontend\components\VtexComponent;
use frontend\components\WoocommerceComponent;
use Yii;
use common\models\Connection;
use frontend\models\search\StoreSearch;
use common\models\UserConnection;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\components\ElliBigCommerce as Bigcommerce;
use yii\filters\AccessControl;
use common\models\User;
use frontend\components\ElliShopifyClient as Shopify;
use SoapClient;
use SoapFault;
use Automattic\WooCommerce\Client as Woocommerce;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use common\models\CurrencyConversion;
use frontend\components\ConsoleRunner;
use frontend\components\BaseController;
/**
 * StoresController implements the CRUD actions for Stores model.
 */

class StoresController extends BaseController {
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
            'class' => AccessControl::className(),
            'only' => [
                'index', 'view', 'create', 'update',
                'auth-bigcommerce', 'bigcommerce',
                'shopify', 'auth-shopify',
                'magento', 'auth-magento', 'magento-importing',
                'woocommerce', 'auth-woocommerce',
                'magento-soap-configuration', 'test'],
            'rules' => [
                [
                'actions' => [
                    'magento-importing',
                    'auth-woocommerce'],
                'allow' => true,
                'roles' => ['?'],
                ],
                [
                    'actions' => [
                        'index', 'view', 'create', 'update',
                        'auth-bigcommerce', 'bigcommerce',
                        'shopify', 'auth-shopify',
                        'magento', 'auth-magento', 'magento-importing',
                        'woocommerce', 'auth-woocommerce',
                        'magento-soap-configuration',
                        'test'],
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

    /**
     * Lists all Stores models.
     * @return mixed
     */
    public function actionIndex() {

        $user_id = Yii::$app->user->identity->id;
        $userConnections = UserConnection::find()->where(['user_id' => $user_id])->available()->all();


        $searchModel = new StoreSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_connections' => $userConnections,
        ]);
    }

    /**
     * Displays a single Stores model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Stores model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Stores();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->store_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->store_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Stores model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
	    $this->findModel($id)->delete();

	    return $this->redirect(['index']);
    }

    /**
     * Finds the Stores model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Stores the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Stores::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * For Shopify Store
     * @return mixed
     */
    public function actionShopify() {

        $connection = Connection::findOne(['name' => 'Shopify']);

        $id = $connection->id;


	    return $this->render('shopify', [
            'plusCheck' => '',
            'connection_id' => $id
        ]);
    }
    /**
     * For ShopifyPlus Store
     * @return mixed
     */
    public function actionShopifyplus() {

        $connection = Connection::findOne(['name' => 'ShopifyPlus']);

        $id = $connection->id;

	    return $this->render('shopify', [
            'plusCheck' => 'checked',
            'connection_id' => $id
        ]);
    }

    /**
     * Authorize the shopify API Credentials
     */
    public function actionAuthShopify() {

        $array_msg = array();
        $postData = Yii::$app->request->post();
        $shopify_shop = trim($postData['shopify_shop']);
        $shopify_api = trim($postData['shopify_api']);
        $shopify_pass = trim($postData['shopify_pass']);
        $shopify_access_token = trim($postData['shopify_shared_secret']);
        $shopify_country = trim($postData['shopify_country']);
        $shopify_plus = $postData['shopify_plus'];
        $user_id = $postData['user_id'];
        $connectionId = $postData['connection_id'];

        $countryInfo = Country::countryInfoFromCode($shopify_country);
        if ($shopify_plus == "true") {
            $connection = Connection::find()->select('id')->where(['name' => 'ShopifyPlus'])->one();
            $connectionId = $connection->id;
        } else {
            $connection = Connection::find()->select('id')->where(['name' => 'Shopify'])->one();
            $connectionId = $connection->id;
        }

        $shopifyClientInfo = [
            'url' => $shopify_shop,
            'api_key' => $shopify_api,
            'key_password' => $shopify_pass,
            'shared_secret' => $shopify_access_token
        ];
        $shopifyClientInfoJson = @json_encode($shopifyClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $shopifyClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Shopify shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }

        try{

            $sc = new Shopify($shopify_shop, $shopify_pass, $shopify_api, $shopify_access_token);
            $store_details = $sc->call('GET', '/admin/shop.json');

            $store_shopId = $store_details['id'];

//            $check_Shop = UserConnection::findOne(
//                [
//                    'user_id' => $user_id,
//                    'market_id' => $store_shopId,
//                    'connection_id' => $connectionId
//                ]);
//
//            if (!empty($check_Shop)) {
//                $array_msg['api_error'] = 'This Shopify shop with same Shop <b>' . $store_shopName . '</b> is already integrated with Eliiot.';
//                return json_encode($array_msg);
//            }

            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connectionId;
            $connectionModel->user_id = $user_id;
            $connectionModel->market_id = $store_shopId;
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->connection_info = $shopifyClientInfoJson;
            if ($connectionModel->save(false)) {

                $userConnectionId = $connectionModel->id;

                ShopifyComponent::importShop($userConnectionId, $store_details, $countryInfo);

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'store-import/shopify '.$userConnectionId;
                $importCr->run($importCmd);

                $array_msg['success'] = "Your Shopify store has been connected successfully. Importing has started and you will be notified once it is completed.";
            } else {
                $array_msg['error'] = "Error Something went wrong. Please try again";
            }

        } catch (ShopifyApiException $shopifyApiEx) {

            $msg = $shopifyApiEx->getMessage();
            $array_msg['api_error'] = 'Your API credentials are not working. Because of following reason ' . $msg . '. Please check and try again.';

            return json_encode($array_msg);

        } catch (ShopifyCurlException $shopifyCurlEx) {

            $array_msg['api_error'] = $shopifyCurlEx->getMessage();
            return json_encode($array_msg);
        }

        return json_encode($array_msg);

    }

    /**
     * Bigcommerce Connection Wizard.
     * @return mixed
     */
    public function actionBigcommerce() {

        $connection = Connection::findOne(['name' => 'BigCommerce']);

        $id = $connection->id;


        return $this->render('bigcommerce', [
            'connection_id' => $id,
        ]);
    }

    /**
     * Authorize the Bigcommerce API Credentials
     * @return mixed
     */
    public function actionAuthBigcommerce() {

        $array_msg = array();

        $postData = Yii::$app->request->post();

        $big_api_path = trim($postData['api_path']);
        $big_access_token = trim($postData['access_token']);
        $big_client_id = trim($postData['client_id']);
        $big_client_secret = trim($postData['client_secret']);
        $parsed_api_path = parse_url($big_api_path);
        $user_id = $postData['user_id'];
        $connectionId = $postData['connection_id'];


        if (array_key_exists('path', $parsed_api_path)) {
            $store_path = $parsed_api_path['path'];
            $store_path_array = explode('/', $store_path);
            if (!array_key_exists(2, $store_path_array)) {
                $array_msg['api_error'] = 'Your BigCommerce API Path is not valid. Please check and try again.';
                return json_encode($array_msg);
            } else {
                $merchant_store_hash = $store_path_array[2];
            }
        } else {
            $array_msg['api_error'] = 'Your BigCommerce API Path is not valid. Please check and try again.';
            return json_encode($array_msg);
        }

        $bgcClientInfo = [
            'client_id' => $big_client_id,
            'auth_token' => $big_access_token,
            'store_hash' => $merchant_store_hash
        ];
        $bgcClientInfoJson = @json_encode($bgcClientInfo, JSON_UNESCAPED_UNICODE);

        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $bgcClientInfoJson
        ]);

        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This BigCommerce store is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }

        Bigcommerce::configure($bgcClientInfo);

        $ping = Bigcommerce::getTime();
        if (!isset($ping) || empty($ping)) {
            $array_msg['api_error'] = 'Your API credentials are not working. Please check and try again.';
            return json_encode($array_msg);
        } else {
            $bgcStoreDetail = Bigcommerce::getStore();

            $store_shopId = $bgcStoreDetail->id;

            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connectionId;
            $connectionModel->user_id = $user_id;
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->market_id = $store_shopId;
            $connectionModel->connection_info = $bgcClientInfoJson;

            if ($connectionModel->save(false)) {

                $userConnectionId = $connectionModel->id;

                BigcommerceComponent::importShop($userConnectionId, $bgcStoreDetail);
                $importBigCr = new ConsoleRunner(['file' => '@console/yii']);
                $importBigCmd = 'store-import/bigcommerce '.$userConnectionId;
                $importBigCr->run($importBigCmd);


                $array_msg['success'] = "Your BigCommerce store has been connected successfully. Importing has started and you will be notified once it is completed.";

            } else {

                $array_msg['error'] = "Error, Something went wrong. Please try again";

            }

        }

        return json_encode($array_msg);
    }



    /**
     * For WOO-COMMERCE Store
     */
    public function actionWoocommerce() {

        $connection = Connection::findOne(['name' => 'WooCommerce']);

        $id = $connection->id;


        return $this->render('woocommerce', [
            'connection_id' => $id,
        ]);
    }

    /* For  WOOCommerce */

    public function actionAuthWoocommerce() {

        $array_msg = array();

        $postData = Yii::$app->request->post();

        $woocommerce_store_url = trim($postData['woocommerce_store_url']);
        $woocommerce_consumer = trim($postData['woocommerce_consumer']);
        $woocommerce_secret = trim($postData['woocommerce_secret']);
        $user_id = trim($postData['user_id']);
        $connectionId = trim($postData['connection_id']);


        $wooCommerceClientInfo = [
            'store_url' => $woocommerce_store_url,
            'consumer' => $woocommerce_consumer,
            'consumer_secret' => $woocommerce_secret,
        ];
        $wooCommerceClientInfoJson = @json_encode($wooCommerceClientInfo, JSON_UNESCAPED_UNICODE);

        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $wooCommerceClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Woocommerce shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }



        /* For Https server */
        $check_url = parse_url($woocommerce_store_url);
        $url_protocol = $check_url['scheme'];
        if ($url_protocol == 'http') {
            /* For Http Url */
            $woocommerce = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret, [
                    'wp_api' => true,
                    'version' => 'wc/v2'
                ]
            );
        } else {

            $woocommerce = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                    "query_string_auth" => true
                ]
            );
        }

        try {
            $wooCommerceDetails = $woocommerce->get('settings/general');

            if ( isset( $wooCommerceDetails->code)){
                $array_msg['api_error'] = 'Your API credentials are not working. Please check and try again.';
                if ( isset( $wooCommerceDetails->message) && !empty( $wooCommerceDetails->message)  ){
                    $array_msg['api_error'] .= "<br>" . $wooCommerceDetails->message;
                }

                return json_encode($array_msg);
            }

            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connectionId;
            $connectionModel->user_id = $user_id;
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->connection_info = $wooCommerceClientInfoJson;


            if ($connectionModel->save()){
                $userConnectionId = $connectionModel->id;

                $wooCommerceDetailsInfo = [
                    'store_url' => $woocommerce_store_url,
                    'details' => $wooCommerceDetails
                ];


                WoocommerceComponent::importShop($userConnectionId, $wooCommerceDetailsInfo);

                $importWooCommerceCr = new ConsoleRunner(['file' => '@console/yii']);
                $importWooCommerceCmd = 'store-import/woocommerce '.$userConnectionId;
                $importWooCommerceCr->run($importWooCommerceCmd);

                $array_msg['success'] = "Your WooCommerce store has been connected successfully. Syncing will start soon.";


            } else {
                $array_msg['error'] = "Error Something went wrong. Please try again";
            }

            return json_encode($array_msg);

        } catch (HttpClientException $wooEx) {

            $errorMsg = $wooEx->getMessage();

            $array_msg['api_error'] = 'Your API credentials are not working. Please check and try again.';

            if ( !empty($errorMsg) ){
                $array_msg['api_error'] .= "<br>" . $errorMsg;
            }

            return json_encode($array_msg);
        }

    }

    /**
     * form of vtex
     */
    public function actionVtex()
    {
        $connection = Connection::findOne(['name' => 'VTEX']);

        $id = $connection->id;


        return $this->render('vtex', [
            'connection_id' => $id,
        ]);
    }

    public function actionAuthVtex(){

        $array_msg = array();

        $postData = Yii::$app->request->post();


        $vtex_account = trim($postData['vtex_account']);
        $vtex_app_key = trim($postData['vtex_app_key']);
        $vtex_app_token = trim($postData['vtex_app_token']);
        $user_id = trim($postData['user_id']);
        $vtex_country = trim($postData['vtex_country']);
        $connection_id = trim($postData['connection_id']);

        $country = Country::countryInfoFromCode($vtex_country);
        $vtex_account_settings = explode('.', $vtex_account);
        $vtex_account_name = $vtex_account_settings[0];
        $vtex_account_env = $vtex_account_settings[1];
        $vtexClientInfo = [
            'account' => $vtex_account_name,
            'enviroment' => $vtex_account_env,
            'app_key' => $vtex_app_key,
            'app_token' => $vtex_app_token,
        ];

        $vtexClientInfoJson = @json_encode($vtexClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $vtexClientInfoJson
        ]);

        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Vtex shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }


        $vtexClient = new VtexClient($vtexClientInfo);
        $response = $vtexClient->call('get', 'catalog_system/pvt/brand/list');

        if(!is_scalar($response)){
            //echo "<pre>"; print_r($response); echo "<pre>";
            if(isset($response['error']['message'])){
                $array_msg['api_error'] = "Your App Key and App Token is not correct. It's giving Unauthorized access. Please check and try again.";
                return json_encode($array_msg);
            }
            else{

//                $check_country = StoreDetails::find()->where(['country' => $country->name, 'channel_accquired' => 'VTEX'])->one();
//                if (!empty($check_country)) {
//                    $array_msg['api_error'] = 'This Vtex shop with same country <b>' . $country->name . '</b> is already integrated with Eliiot.';
//                    return json_encode($array_msg);
//                }

//                $user = User::find()->where(['id' => $user_id])->one();
//                $user_domain = $user->domain_name;
//                $store = Stores::find()->select('store_id')->where(['store_name' => 'VTEX'])->one();
//                $vtex_store_id = $store->store_id;
                //$checkConnection = StoresConnection::find()->where(['store_id' => $vtex_store_id, 'user_id' => $user_id])->one();
                //if (empty($checkConnection)){
                $connectionModel = new UserConnection();
                $connectionModel->connection_id = $connection_id;
                $connectionModel->user_id = $user_id;
                $connectionModel->connected = UserConnection::CONNECTED_YES;
                $connectionModel->connection_info = $vtexClientInfoJson;

                if ($connectionModel->save()){
                    $userConnectionId = $connectionModel->id;

                    $shopDetails = [
                        'account' => $vtex_account_name,
                        'account_env' => $vtex_account_env,
                        'country_name' => $country->name,
                        'country_code' => $country->sortname,
                        'currency_code' => $country->currency_code,
                        'currency_symbol' => $country->currency_symbol,
                    ];
                    VtexComponent::importShop($userConnectionId, $shopDetails);

                    $importVtexCr = new ConsoleRunner(['file' => '@console/yii']);
                    $importVtexCmd = 'store-import/vtex '.$userConnectionId;
                    $importVtexCr->run($importVtexCmd);

                    $array_msg['success'] = "Your Vtex shop has been connected successfully. Importing is started. Once importing is done you will get a nofiy message.";

                    return json_encode($array_msg);


                }
                else{
                    $array_msg['api_error'] = "Error Something went wrong. Please try again";
                    return json_encode($array_msg);
                }
                //}
            }
        }
        else{
            $array_msg['api_error'] = "It's look like you have enter wrong Vtex account name. Please check and try again.";
            return json_encode($array_msg);
        }
    }

    /**
     * form of Reaction
     */
    public function actionReaction()
    {

        $connection = Connection::findOne(['name' => 'Reaction']);

        $id = $connection->id;

        return $this->render('reaction', [
            'connection_id' => $id,
        ]);
    }

    public function actionAuthReaction(){
        $array_msg = array();

        $postData = Yii::$app->request->post();


        $reaction_url = trim($postData['reaction_url']);
        $reaction_user_email = trim($postData['reaction_user_email']);
        $reaction_user_pwd = trim($postData['reaction_user_pwd']);
        $user_id = trim($postData['user_id']);
        $reaction_country = trim($postData['reaction_country']);
        $connection_id = trim($postData['connection_id']);

        $country = Country::countryInfoFromCode($reaction_country);

        $reactionClientInfo = [
            'reaction_url' => $reaction_url,
            'reaction_user_email' => $reaction_user_email,
            'reaction_user_pwd' => $reaction_user_pwd,
            'reaction_country' => $country->sortname,
        ];

        $reactionClientInfoJson = @json_encode($reactionClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $reactionClientInfoJson,
        ]);

        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Reaction shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }


        $reactionClient = new ReactionSimpleRestClient($reactionClientInfo);

        if ( $reactionClient->checkValidate() ){

            $reaction_accountId = $reactionClient->getReactionAcctounId();

            $response = $reactionClient->call('get', 'publications/PrimaryShop');

            if(!is_scalar($response)){

                if(isset($response['Shops'])){

                    $reaction_currency = "USD";
                    $reaction_shop_id = "";
                    $reaction_shop_name = "";

                    if ( isset($response['Shops']) && !empty($response['Shops'][0]) ){
                        $primaryShopInfo = $response['Shops'][0];

                        if ( isset($primaryShopInfo['currency']) && !empty($primaryShopInfo['currency']) ){
                            $reaction_currency = $primaryShopInfo['currency'];
                        }

                        if ( isset($primaryShopInfo['_id']) && !empty($primaryShopInfo['_id']) ){
                            $reaction_shop_id = $primaryShopInfo['_id'];
                        }
                        if ( isset($primaryShopInfo['name']) && !empty($primaryShopInfo['name']) ){
                            $reaction_shop_name = $primaryShopInfo['name'];
                        }

                    }

                    $connectionModel = new UserConnection();
                    $connectionModel->connection_id = $connection_id;
                    $connectionModel->user_id = $user_id;
                    $connectionModel->market_id = $reaction_shop_id;
                    $connectionModel->connected = UserConnection::CONNECTED_YES;
                    $connectionModel->connection_info = $reactionClientInfoJson;

                    if ($connectionModel->save()){
                        $userConnectionId = $connectionModel->id;

                        $shopDetails = [
                            'reaction_url' => $reaction_url,
                            'shop_currency' => $reaction_currency,
                            'shop_id' => $reaction_shop_id,
                            'shop_name' => $reaction_shop_name,
                            'shop_account_id' => $reaction_accountId,
                            'country_name' => $country->name,
                            'country_code' => $country->sortname,
                            'currency_code' => $country->currency_code,
                            'currency_symbol' => $country->currency_symbol,
                        ];
                        ReactionComponent::importShop($userConnectionId, $shopDetails);

                        //$importReactionCr = new ConsoleRunner(['file' => '@console/yii']);
                        //$importReactionCmd = 'store-import/reaction '.$userConnectionId;
                        //$importReactionCr->run($importReactionCmd);

                        $array_msg['success'] = "Your Reaction shop has been connected successfully. Importing is started. Once importing is done you will get a nofiy message.";

                        return json_encode($array_msg);


                    }

                }
            }

        }

        $array_msg['api_error'] = "Your Reaction Store Url is not correct or not installed simple:rest package. If you need help, contact Elliot Support Team, please.";
        return json_encode($array_msg);


    }

    //Disable channels 
    public function actionChanneldisable() {
        $dt = Yii::$app->request->post();
        $_SESSION['access_token'] = '';
        if (isset($dt['ch_id'])) {
            if ($dt['ch_type'] == "store") {
            $store_big = StoresConnection::find()->where(['stores_connection_id' => $dt['ch_id']])->one();
            $user_id = $store_big->user_id;
            $store_id = $store_big->store_id;
            $get_shopify_id = Stores::find()->select('store_id')->where(['store_name' => 'Shopify'])->one();
            $shopify_store_id = $get_shopify_id->store_id;
            if ($store_id == $shopify_store_id) {
                $shopify_shop = $store_big->url;
                $shopify_api_key = $store_big->api_key;
                $shopify_api_password = $store_big->key_password;
                $shopify_shared_secret = $store_big->shared_secret;
                $user_id = $store_big->user_id;
                $sc = new Shopify($shopify_shop, $shopify_api_password, $shopify_api_key, $shopify_shared_secret);
                $hooks = $sc->call('GET', '/admin/webhooks.json');
                foreach ($hooks as $_hook) {
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-product-create?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-product-delete?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-product-update?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-order-create?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-order-paid?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-order-update?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-customer-create?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                    if (in_array(Yii::$app->params['BASE_URL'] . "people/shopify-customer-update?id=" . $user_id, $_hook)) {
                        $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
                    }
                }
            }
            $store_big->delete();
            //Update magnto shop url when store is disable by user
            $get_magento_id = Stores::find()->select('store_id')->where(['store_name' => 'Magento'])->one();
            $magento_store_id = $get_magento_id->store_id;
            if ($store_id == $magento_store_id) {
                $main_data = User::find()->where(['id' => $user_id])->one();
                $main_data->magento_shop_url = '';
                $main_data->save(false);
            }
            return;
            } elseif ($dt['ch_type'] == "channel") {
            $store_big = ChannelConnection::find()->where(['channel_connection_id' => $dt['ch_id']])->one();
            $store_big->delete();
            return;
            }
        }
        $all_conntection = StoresConnection::find()->all();
        $all_channels = ChannelConnection::find()->all();

        $cnt = 0;
        $arr1 = array();
        foreach ($all_conntection as $conn) {
            $arr1[$cnt]['connection_id'] = $conn->stores_connection_id;
            $store = Stores::find()->select(['store_name', 'store_image'])->where(['store_id' => $conn->store_id])->one();

            $arr1[$cnt]['store_name'] = $store->store_name;
            $arr1[$cnt]['store_image'] = $store->store_image;
        }

        $cnt1 = 0;
        $arr2 = array();
        foreach ($all_channels as $channel) {
            $arr2[$cnt]['connection_id'] = $channel->channel_connection_id;
            $chnl = Channels::find()->select(['channel_name', 'channel_image', 'parent_name'])->where(['channel_id' => $channel->channel_id])->one();

            $arr2[$cnt]['channel_name'] = $chnl->channel_name;
            $arr2[$cnt]['channel_image'] = $chnl->channel_image;
            $arr2[$cnt]['parent_name'] = $chnl->parent_name;
        }

        $arr = array("store" => $arr1, "channel" => $arr2);
        return $this->render('channeldisable', [
            'connections' => $arr,
        ]);
    }
    /*
     * Magento sign up frontend
     */

    public function actionMagento() {

        $connection = Connection::findOne(['name' => 'Magento']);

        $id = $connection->id;


	    return $this->render('magento');
    }

    /**
     * For Magento Soap Configuration link
     * @return mixed
     */
    public function actionMagentoSoapConfiguration() {
        return $this->render('magentosoapconfiguration');
    }

    public function actionAuthMagento() {
        $array_msg = array();
        $magento_shop = $_POST['magento_shop'];
        $magento_soap_user = $_POST['magento_soap_user'];
        $magento_soap_api = $_POST['magento_soap_api'];
        $user_id = $_POST['user_id'];
        $magento_country = $_POST['magento_country'];
        $connection = Connection::find()->select('id')->where(['name' => 'Magento'])->one();
        $connectionId = $connection->id;
        $api_url = $magento_shop . "/api/soap/?wsdl";
		$magentoClientInfo = [
            'magento_soap_url' => $api_url,
            'magento_soap_user' => $magento_soap_user,
            'magento_soap_api' => $magento_soap_api,
            'magento_country' => $magento_country
        ];
        $magentoClientInfoJson = @json_encode($magentoClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $magentoClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Magento shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }
        
        try {
            $cli = new SoapClient($api_url, array('trace' => true, 'exceptions' => true));
            $session_id = $cli->login($magento_soap_user, $magento_soap_api);
            $connectionModel = new UserConnection();
            $connectionModel->connection_id = $connectionId;
            $connectionModel->user_id = $user_id;
            $connectionModel->market_id = $session_id;
            $connectionModel->connected = UserConnection::CONNECTED_YES;
            $connectionModel->connection_info = $magentoClientInfoJson;
            if ($connectionModel->save(false)) {

                $userConnectionId = $connectionModel->id;
                $user_Shopify_connection = UserConnectionDetails::findOne(
                    [
                        'user_connection_id' => $userConnectionId
                    ]
                );
                if (empty($user_Shopify_connection)) {
                    $user_Shopify_connection = new UserConnectionDetails();
                    $user_Shopify_connection->user_connection_id = $userConnectionId;
                }

                $country_detail = Country::countryInfoFromCode($magento_country);
                $magento_country_code = "US";
                $magento_currency = "USD";
                $magento_currency_symbol = "$";
                $magento_country_name = "United States";
                if (!empty($country_detail)) {
                    $magento_country_code = $country_detail->sortname;
                    $magento_country_name = $country_detail->name;
                    $magento_currency = $country_detail->currency_code;
                    $magento_currency_symbol = $country_detail->currency_symbol;
                }

                $user_Shopify_connection->store_name = $connectionModel->connection->name;
                $user_Shopify_connection->store_url = $api_url;
                $user_Shopify_connection->country = $magento_country_name;
                $user_Shopify_connection->country_code = $magento_country_code;
                $user_Shopify_connection->currency = $magento_currency;
                $user_Shopify_connection->currency_symbol = $magento_currency_symbol;

                $userConnectionSettings = $user_Shopify_connection->settings;

                if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
                    $userConnectionSettings['currency'] = $user_Shopify_connection->currency;
                }

                $user_Shopify_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


                $user_Shopify_connection->save(false);

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'store-import/magento '.$userConnectionId;
                $importCr->run($importCmd);

	            $array_msg['success'] = "Your Magento shop has been connected successfully. Importing is started. Once importing is done you will get a notify message.";
	            $array_msg['store_connection_id'] = $connectionId;
            } else {
                $array_msg['error'] = "Error Something went wrong. Please try again";
            }
        } catch (SoapFault $e) {
            $msg = $e->faultstring;
            $array_msg['api_error'] = 'Your API credentials are not working.' . $msg;
        }

        return json_encode($array_msg);
    }


    /**
     * Magento 2 configuration action
     */
    public function actionMagento2() {
        $connection = Connection::findOne(['name' => 'Magento2']);

        $id = $connection->id;

        return $this->render('magento2');
    }

    public function actionMagento2xConfiguration() {
        return $this->render('magento2xconfiguration');
    }

    public function actionAuthMagento2(){
        $array_msg = array();
        $magento_shop_url = rtrim($_POST['magento_2_shop'],'/');
        $magento_shop = $magento_shop_url.'/';
        $magento_2_access_token = $_POST['magento_2_access_token'];
        $user_id = $_POST['user_id'];
        $magento_country = $_POST['magento_2_country'];

        $adminUrl=$magento_shop.'index.php/rest/V1/store/websites?searchCriteria=';
        $headers = array("Authorization: Bearer $magento_2_access_token");
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            $array_msg['api_error'] = 'It\'s look like You have enter wrong Magento url. Please check and try again.<br>Error is '.$err;
            $array_msg['err_desctription'] = $err;
            return json_encode($array_msg);
        }
        else{
            $result =  json_decode($result, TRUE);
            if($result==''){
                $array_msg['api_error'] = 'You Credentials are not working. Please check and try again';
                $array_msg['err_desctription'] = "none result";
                return json_encode($array_msg);
            }
            else{
                if(array_key_exists('message', $result) && $result['message']=='Consumer is not authorized to access %resources'){
                    $array_msg['api_error'] = 'You Access Token is no correct. Please check and try again.<br>Error is '.$result['message'];
                    $array_msg['err_desctription'] = $result;
                    return json_encode($array_msg);
                }
            }
        }
        $magentoClientInfo = [
            'magento_shop' => $magento_shop,
            'admin_url' => $adminUrl,
            'magento_2_access_token' => $magento_2_access_token,
            'magento_country' => $magento_country
        ];
        $magentoClientInfoJson = @json_encode($magentoClientInfo, JSON_UNESCAPED_UNICODE);
        $check_api_key = UserConnection::findOne([
            'user_id' => $user_id,
            'connection_info' => $magentoClientInfoJson
        ]);
        if (!empty($check_api_key)) {
            $array_msg['api_error'] = 'This Magento shop is already integrated with Elliot. Please use another API details to integrate with Elliot.';
            return json_encode($array_msg);
        }
        $connection = Connection::find()->select('id')->where(['name' => 'Magento2'])->one();
        $connectionId = $connection->id;
        $connectionModel = new UserConnection();
        $connectionModel->connection_id = $connectionId;
        $connectionModel->user_id = $user_id;
        $connectionModel->market_id = "";
        $connectionModel->connected = UserConnection::CONNECTED_YES;
        $connectionModel->connection_info = $magentoClientInfoJson;
        if ($connectionModel->save(false)) {

            $userConnectionId = $connectionModel->id;
            $user_Shopify_connection = UserConnectionDetails::findOne(
                [
                    'user_connection_id' => $userConnectionId
                ]
            );
            if (empty($user_Shopify_connection)) {
                $user_Shopify_connection = new UserConnectionDetails();
                $user_Shopify_connection->user_connection_id = $userConnectionId;
            }

            $country_detail = Country::countryInfoFromCode($magento_country);
            $magento_country_code = "US";
            $magento_currency = "USD";
            $magento_currency_symbol = "$";
            $magento_country_name = "United States";
            if (!empty($country_detail)) {
                $magento_country_code = $country_detail->sortname;
                $magento_country_name = $country_detail->name;
                $magento_currency = $country_detail->currency_code;
                $magento_currency_symbol = $country_detail->currency_symbol;
            }

            $user_Shopify_connection->store_name = $connectionModel->connection->name;
            $user_Shopify_connection->store_url = $adminUrl;
            $user_Shopify_connection->country = $magento_country_name;
            $user_Shopify_connection->country_code = $magento_country_code;
            $user_Shopify_connection->currency = $magento_currency;
            $user_Shopify_connection->currency_symbol = $magento_currency_symbol;

            $userConnectionSettings = $user_Shopify_connection->settings;

            if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
                $userConnectionSettings['currency'] = $user_Shopify_connection->currency;
            }

            $user_Shopify_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


            $user_Shopify_connection->save(false);

            $importCr = new ConsoleRunner(['file' => '@console/yii']);
            $importCmd = 'store-import/magento2 '.$userConnectionId;
            $importCr->run($importCmd);

            $array_msg['success'] = "Your Magento shop has been connected successfully. Importing is started. Once importing is done you will get a notify message.";
            $array_msg['store_connection_id'] = $connectionId;
            $array_msg['err_desctription'] = $result;
        } else {
            $array_msg['error'] = "Error Something went wrong. Please try again";
        }

        return json_encode($array_msg);
    }

    public function actionProductxml() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        $users = Products::find('product_name', 'SKU', 'description')->one();
        return $users;
    }

}
