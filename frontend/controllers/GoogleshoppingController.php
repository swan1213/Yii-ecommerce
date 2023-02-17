<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

use common\models\Country;
use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Feed;
use common\models\UserFeed;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\Product;
use common\models\ProductImage;
use common\models\ProductCategory;
use frontend\models\GoogleShopppingFeedForm;
use frontend\models\GoogleShopppingConnectionForm;
use frontend\models\search\CategorySearch;


/**
 * GoogleShoppingController implements for Channels model.
 */
class GoogleshoppingController extends Controller {
    protected $channel_type = 'Google shopping';
	/**
     * action filter
     */
	public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'get-token'],
                'rules' => [
                    [
                        'actions' => ['index', 'get-token'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function generateTimezoneList() {
        static $regions = array(
            \DateTimeZone::AFRICA,
            \DateTimeZone::AMERICA,
            \DateTimeZone::ANTARCTICA,
            \DateTimeZone::ASIA,
            \DateTimeZone::ATLANTIC,
            \DateTimeZone::AUSTRALIA,
            \DateTimeZone::EUROPE,
            \DateTimeZone::INDIAN,
            \DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach( $regions as $region )
        {
            $timezones = array_merge( $timezones, \DateTimeZone::listIdentifiers( $region ) );
        }

        $timezone_offsets = array();
        foreach( $timezones as $timezone )
        {
            $tz = new \DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
        }

        // sort timezone by offset
        asort($timezone_offsets);

        $timezone_list = array();
        foreach( $timezone_offsets as $timezone => $offset )
        {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate( 'H:i', abs($offset) );

            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

            $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
        }

        return $timezone_list;
    }

    public function actionIndex($id) {
        $error_msg = '';
        $error_status = false;
        $is_connect_channel = false;
        $connect_status = UserConnection::IMPORT_STATUS_FAIL;
        $user_id = Yii::$app->user->identity->id;

        $target_countries = [
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BR' => 'Brazil',
            'CA' => 'Canada',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CZ' => 'Czechia',
            'DK' => 'Denmark',
            'FR' => 'France',
            'DE' => 'Germany',
            'HK' => 'Hong Kong',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IT' => 'Italy',
            'JP' => 'Japan',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NO' => 'Norway',
            'PH' => 'Phillippines',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RW' => 'Russia',
            'SG' => 'Singapore',
            'ZA' => 'South Africa',
            'ES' => 'Spain',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'TW' => 'Taiwan',
            'TR' => 'Turkey',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States'
        ];
        $timezone_list = $this->generateTimezoneList();

        // check if this connection is still existed in Connection table
        $connection_row = \common\models\Connection::find()->where(['id' => $id])->one();
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();

        // connection is not exsited
        if(empty($connection_row)) {
            return $this->redirect(['/channels']);
        }

        $connection_name = $connection_row->name;

        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where(['connection_id' => $id, 'user_id' => $user_id])->one();
        $is_authorize = false;

        // the connection is already existed
        if(!empty($user_connection_row)) {
            try {
                if(
                    isset($user_connection_row->connection_info['access_token']) &&
                    isset($user_connection_row->connection_info['merchant_id']) &&
                    !empty($user_connection_row->connection_info['access_token']) &&
                    !empty($user_connection_row->connection_info['merchant_id'])
                ) {
                    $this->listDatafeeds(
                        $user_id,
                        $user_connection_row->connection_info['access_token'],
                        $user_connection_row->connection_info['merchant_id']
                    );

                    $is_authorize = true;
                }
            } catch(\Exception $e) {
                $error_status = true;
                $error_msg = $e->getMessage();
            }

            if($user_connection_row->connected == UserConnection::CONNECTED_YES) {
                $is_connect_channel = true; 
            } else {
                $connect_status = $user_connection_row->import_status;
            }
        }

        $session = Yii::$app->session;
        if(!empty($session->get('google_connection_error'))) {
            $error_status = true;
            $error_msg = $session->get('google_connection_error');
            $session->remove('google_connection_error');
        } else {
            $error_status = false;
            $error_msg = '';
        }

        $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        $redirectUri = $root . 'googleshopping/get-token';
        $connection_model = new GoogleShopppingConnectionForm();
    	$feed_model = new GoogleShopppingFeedForm();
        $session = Yii::$app->session;
        $session->set('google_shopping_id', $id);

        if ($connection_model->load(Yii::$app->request->post()) && $connection_model->validate()) {
            $post_variable = Yii::$app->request->post('GoogleShopppingConnectionForm');
            $session->set('google_merchant_id', $post_variable['merchant_id']);
            header('Location: ' . $redirectUri);
            die;
        }

        // either the page is initially displayed or there is some validation error
        return $this->render('index', [
            'id' => $id,
            'connection_model' => $connection_model,
            'feed_model' => $feed_model,
            'is_authorize' => $is_authorize,
            'error_status' => $error_status,
            'error_msg' => $error_msg,
            'is_connect_channel' => $is_connect_channel,
            'connect_status' => $connect_status,
            'categories' => $categories,
            'target_countries' => $target_countries,
            'timezone_list' => $timezone_list
        ]);
    }

    public function actionFeedsAjax($id) {
        $user_id = Yii::$app->user->identity->id;
        $session = Yii::$app->session;

        $user_connection_row = UserConnection::find()->Where([
            'user_id' => $user_id,
            'connection_id' => $id
        ])->one();

        $feed_list = array();

        if(!empty($user_connection_row)) {
            try {
                $feed_list = $this->listDatafeeds(
                    $user_id,
                    $user_connection_row->connection_info['access_token'],
                    $user_connection_row->connection_info['merchant_id']
                );
            } catch(\Exception $e) {

            }  
        }

        $data = array();
        if(empty($feed_list)) {
            $feed_list = array();
        }

        foreach ($feed_list as $single_feed) {
            $arr = array();
            $arr[0] = $single_feed['name'];
            $arr[1] = $single_feed['fetchUrl'];
            $arr[2] = Html::a('<i class="mdi mdi-edit"></i>', ['/googleshopping/update', 'feed_id' => $single_feed['id'], 'id' => $id], ['class' => 'btn btn-success'])."<button class='btn btn-danger' onclick=\"onDeleteGoogleFeed(".$id.", ".$single_feed['id'].", '".$single_feed['fetchUrl']."')\"><i class='mdi mdi-delete'></i></button>";
            $data[] = $arr;
        }
        echo json_encode(array(
            "data" => $data
        ));
    }

    public function actionSavechannel() {
        $is_check = Yii::$app->request->post('is_check');
        $id = Yii::$app->request->post('connection_id');
        $user_id = Yii::$app->user->identity->id;
        try {
            $connection_row = Connection::find()->where(['id' => $id])->one();
            if(empty($connection_row)) {
                throw new \Exception('Invalid connection id');
            }

            $user_connection_row = UserConnection::find()->Where([
                'user_id' => $user_id,
                'connection_id' => $connection_row->id])->one();

            if($is_check == 'yes') {
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
            } else {
                $user_connection_row->connected = UserConnection::CONNECTED_NO;
            }
            $user_connection_row->save(true, ['connected']);

            if($is_check == 'yes') {
                echo json_encode([
                    'success' => true,
                    'message' => 'Your Google Shopping Channel has been connected successfully.'
                ]);
            } else {
                $feed_row = Feed::findOne(['name' => 'google']);

                if(empty($feed_row)) {
                    throw new \Exception('Invalid Feed');  
                }

                $user_feed_rows = UserFeed::find()->where([
                    'user_id' => $user_id,
                    'feed_id' => $feed_row->id
                ])->all();

                foreach ($user_feed_rows as $single_user_feed) {
                    $this->deleteFeed(
                        $user_connection_row->connection_info['merchant_id'],
                        $user_connection_row->connection_info['access_token'],
                        $single_user_feed['feed_connection_id'],
                        $single_user_feed['link']
                    );
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Your Google Shopping Channel has been disconnected successfully.'
                ]);
            }
            
        } catch(\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function deleteFeed(
        $merchant_id,
        $access_token,
        $datafeed_id,
        $fetch_url
    ) {
        try {
            $client = new \Google_Client();
            $client->setAccessToken($access_token);
            $service = new \Google_Service_ShoppingContent($client);
            $service->datafeeds->delete($merchant_id, $datafeed_id);
            // convert the fetch url to real file path url
            $fetch_file_info = pathinfo($fetch_url);
            $fetch_file_url = $fetch_file_info['filename'].'.'.$fetch_file_info['extension'];
            $this->removeFeedFile($fetch_file_url);

            $user_id = Yii::$app->user->identity->id;
            $feed_instance = Feed::findOne(['name' => 'google']);

            $user_feed_instance = UserFeed::find()->where([
                'user_id' => $user_id,
                'feed_id' => $feed_instance->id,
                'feed_connection_id' => $datafeed_id
            ])->one();

            if(!empty($user_feed_instance)) {
                $user_feed_instance->delete();
            }
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        } catch(\Google_Service_Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function actionDeleteFeed() {
        $request = Yii::$app->request;
        $connection_id = $request->post('connection_id');
        $datafeed_id = $request->post('feed_id');
        $fetch_url = $request->post('fetch_url');
        
        try {
            $user_id = Yii::$app->user->identity->id;
            $connection_row = Connection::find()->where(['id' => $connection_id])->one();
            if(empty($connection_row)) {
                throw new \Exception('Invalid connection id');
            }

            $user_connection_row = UserConnection::find()->Where([
                'user_id' => $user_id,
                'connection_id' => $connection_row->id])->one();

            $this->deleteFeed(
                $user_connection_row->connection_info['merchant_id'],
                $user_connection_row->connection_info['access_token'],
                $datafeed_id,
                $fetch_url
            );

            echo json_encode(['success' => true]);
        } catch(\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionReAuthorize($id) {
        $user_id = Yii::$app->user->identity->id;
        $user_connection_row = UserConnection::find()->Where([
            'user_id' => $user_id,
            'connection_id' => $id
        ])->one();

        if(!empty($user_connection_row)) {
            $user_connection_row->connection_info = json_encode(array(
                'merchant_id' => $user_connection_row->connection_info['merchant_id']
            ));

            $user_connection_row->save(true, ['connection_info']);
        }
        return $this->redirect(['index', 'id' => $id]);
    }

    public function actionCreate($id) {
        $user_id = Yii::$app->user->identity->id;
        $category_list = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $timezone_list = $this->generateTimezoneList();
        $feed_model = new GoogleShopppingFeedForm();

        return $this->render('create', [
            'id' => $id,
            'feed_model' => $feed_model,
            'action' => 'create-feed',
            'category_list' => $category_list,
            'timezone_list' => $timezone_list,
            'selected_categories' => []
            ]
        );
    }

    public function actionUpdate($id, $feed_id) {
        $user_id = Yii::$app->user->identity->id;
        $feed_instance = Feed::findOne(['name' => 'google']);

        //get categories selected
        $user_feed_instance = UserFeed::find()->where([
            'user_id' => $user_id,
            'feed_id' => $feed_instance->id,
            'feed_connection_id' => $feed_id
        ])->one();
        if(!empty($user_feed_instance)) {
            $selected_categories = json_decode($user_feed_instance->categories);
        } else {
            $selected_categories = [];
        }

        $session = Yii::$app->session;
        $id = $session->get('google_shopping_id');
        //get the datafeed with the id
        $datafeed = $this->getDatafeed($feed_id);
        if(empty($datafeed)) {
            return $this->redirect(['index', 'id' => $id]);
        }

        $category_list = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $timezone_list = $this->generateTimezoneList();
        $feed_model = new GoogleShopppingFeedForm();
        $feed_model->feed_name = $datafeed->name;
        $feed_model->destinations = $datafeed->intendedDestinations;
        $feed_model->target_country = $datafeed->targetCountry;
        $feed_model->language = $datafeed->contentLanguage;

        if(!empty($datafeed->targets)) {
            $feed_model->target_country = $datafeed->targets[0]->country;
            $feed_model->language = $datafeed->targets[0]->language;
        }

        if(!empty($datafeed->fetchSchedule)) {
            $feed_model->fetch_time = $datafeed->fetchSchedule->hour;
            $feed_model->timezone = $datafeed->fetchSchedule->timeZone;

            $feed_model->fetch_frequency = 'daily';
            if(!empty($datafeed->fetchSchedule->dayOfMonth)) {
                $feed_model->fetch_frequency = 'monthly';
                $feed_model->fetch_date = $datafeed->fetchSchedule->dayOfMonth;
            }

            if(!empty($datafeed->fetchSchedule->weekday)) {
                $feed_model->fetch_frequency = 'weekly';
                $feed_model->fetch_weekday = $datafeed->fetchSchedule->weekday;
            }

            $fetch_file_info = pathinfo($datafeed->fetchSchedule->fetchUrl);
            $fetch_file_name = $fetch_file_info['filename'];
            $fetch_file_url = $fetch_file_info['filename'].'.'.$fetch_file_info['extension'];
        }

        return $this->render('update', [
            'id' => $id,
            'feed_model' => $feed_model,
            'action' => ['/googleshopping/update-feed', 'feed_id' => $feed_id, 'id' => $id],
            'category_list' => $category_list,
            'timezone_list' => $timezone_list,
            'selected_categories' => $selected_categories
            ]
        );
    }

    public function actionUpdateFeed($feed_id, $id) {
        $user_id = Yii::$app->user->identity->id;
        $feed_model = new GoogleShopppingFeedForm();
        $post_variable = Yii::$app->request->post('GoogleShopppingFeedForm');
        $datafeed = $this->getDatafeed($feed_id);

        if ($feed_model->load(Yii::$app->request->post()) && $feed_model->validate()) {
            $post_variable = Yii::$app->request->post('GoogleShopppingFeedForm');
            $error_status = false;

            try {
                if(!empty($fetch_file_url)) {
                    $this->removeFeedFile($fetch_file_url);
                }
                
                $file_info = $this->createFeedFile(
                    $post_variable['category_ids'],
                    $datafeed->fileName,
                    false
                );
            } catch(\Exception $e) {
                $error_status = true;
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            if($error_status == false) {
                try {
                    $datafeed = $this->updateDatafeed(
                        $datafeed,
                        $post_variable,
                        $file_info['filename'],
                        $file_info['file_full_path']
                    );
                } catch(\Google_Service_Exception $e) {
                    $error_status = true;
                    $error_object = json_decode($e->getMessage(), true);
                    if(isset($error_object['error']) and isset($error_object['error']['message'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => $error_object['error']['message']
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'There is some google service error'
                        ]);
                    }
                }
            }
            
            if($error_status == false) {
                try {
                    $selected_country = $post_variable['target_country'];
                    $feed_instance = Feed::findOne(['name' => 'google']);

                    $user_feed_instance = UserFeed::find()->where([
                        'user_id' => $user_id,
                        'feed_id' => $feed_instance->id,
                        'feed_connection_id' => $datafeed->id
                    ])->one();

                    if(empty($user_feed_instance)) {
                        $user_feed_instance = new UserFeed();    
                    }

                    $user_feed_instance->name = $datafeed->fileName;
                    $user_feed_instance->user_id = $user_id;
                    $user_feed_instance->feed_id = $feed_instance->id;
                    $user_feed_instance->feed_connection_id = $datafeed->id;
                    $user_feed_instance->categories = json_encode($post_variable['category_ids']);
                    $user_feed_instance->country_codes = json_encode([$selected_country]);
                    $user_feed_instance->save(false);
                    $base_url = env('SEVER_URL');
                    $user_feed_instance->code = base64_encode($user_feed_instance->id);
                    $fetch_url = join(DIRECTORY_SEPARATOR, [
                        $file_info['file_full_path'],
                        $file_info['filename']
                    ]);
                    $fetch_url = str_replace('\\','/',$fetch_url);
                    $user_feed_instance->link = $fetch_url;
                    $user_feed_instance->save(false);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Success! Google Shopping Feed has been updated.'
                    ]);
                } catch(\Exception $e) {
                    $this->removeFeedFile($file_info['filename']);

                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => ActiveForm::validate($feed_model)
            ]);
        }
    }

    public function actionCreateFeed() {
        $user_id = Yii::$app->user->identity->id;
        $feed_model = new GoogleShopppingFeedForm();

        if ($feed_model->load(Yii::$app->request->post()) && $feed_model->validate()) {
            $post_variable = Yii::$app->request->post('GoogleShopppingFeedForm');
            
            try {
                $file_info = $this->createFeedFile(
                    $post_variable['category_ids'],
                    $post_variable['feed_name']
                );
            } catch(\Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            try {
                $datafeed = $this->createDatafeed(
                    $post_variable,
                    $file_info['filename'],
                    $file_info['file_full_path']
                );

                $datafeed = $this->insertDatafeed($datafeed);
             } catch(\Google_Service_Exception $e) {
                $error_object = json_decode($e->getMessage(), true);
                if(isset($error_object['error']) and isset($error_object['error']['message'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => $error_object['error']['message']
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'There is some google service error'
                    ]);
                }
            }

            try {
                $selected_country = $post_variable['target_country'];
                $feed_instance = Feed::findOne(['name' => 'google']);

                $user_feed_instance = UserFeed::find()->where([
                    'user_id' => $user_id,
                    'feed_id' => $feed_instance->id,
                    'feed_connection_id' => $datafeed->id
                ])->one();

                if(empty($user_feed_instance)) {
                    $user_feed_instance = new UserFeed();    
                }

                $user_feed_instance->name = $post_variable['feed_name'];
                $user_feed_instance->user_id = $user_id;
                $user_feed_instance->feed_id = $feed_instance->id;
                $user_feed_instance->feed_connection_id = $datafeed->id;
                $user_feed_instance->categories = json_encode($post_variable['category_ids']);
                $user_feed_instance->country_codes = json_encode([$selected_country]);
                $user_feed_instance->save(false);
                $base_url = env('SEVER_URL');
                $user_feed_instance->code = base64_encode($user_feed_instance->id);
                $fetch_url = join(DIRECTORY_SEPARATOR, [
                    $file_info['file_full_path'],
                    $file_info['filename']
                ]);
                $fetch_url = str_replace('\\','/',$fetch_url);
                $user_feed_instance->link = $fetch_url;
                $user_feed_instance->save(false);

                echo json_encode([
                    'success' => true,
                    'message' => 'Success! Google Shopping Feed has been created.'
                ]);
            } catch(\Exception $e) {
                $this->removeFeedFile($file_info['filename']);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => ActiveForm::validate($feed_model)
            ]);
        }
    }

    public function actionGetToken() {
        $session = Yii::$app->session;
        $id = $session->get('google_shopping_id');

        $oauth_file = join(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@frontend'),
            'web',
            'credentials',
            env('OAUTH_CLIENT_FILE_NAME')
        ]);
        $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        $redirect_uri = $root . 'googleshopping/get-token';

        $client = new \Google_Client();
        $client->setAuthConfigFile($oauth_file);
        $client->setRedirectUri($redirect_uri);
        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessType('offline');

        if (! isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
            die;
        } else {
            $client->authenticate($_GET['code']);
            $user_id = Yii::$app->user->identity->id;
            $connection_row = Connection::find()->where(['id' => $id])->one();

            $user_connection_row = UserConnection::find()->Where([
                'user_id' => $user_id,
                'connection_id' => $connection_row->id
            ])->one();

            if(empty($user_connection_row)) {
                $user_connection_row = new UserConnection();    
            }

            date_default_timezone_set("UTC");
            $user_connection_row->user_id = $user_id;
            $user_connection_row->connection_id = $connection_row->id;

            $session = Yii::$app->session;
            $merchant_id = $session->get('google_merchant_id');
            if(empty($merchant_id)) {
                throw new \Exception('You need to re-authorize.');
            }

            $user_connection_row->market_id = $merchant_id;
            $user_connection_row->connection_info = json_encode(array(
                'merchant_id' => $merchant_id,
                'access_token' => $client->getAccessToken()
            ));

            $user_connection_row->created_at = date('Y-m-d h:i:s', time());
            $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
            $user_connection_row->save(false);
            $saved_user_connection_id = $user_connection_row->id;

            $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $saved_user_connection_id])->one();
            if(empty($user_connection_detail_model)) {
                $user_connection_detail_model = new UserConnectionDetails();
            }

            $user_connection_detail_model->user_connection_id = $saved_user_connection_id;
            $user_connection_detail_model->store_name = 'Google Shopping';
            $user_connection_detail_model->store_url = 'https://www.google.com/shopping';
            $user_connection_detail_model->country = 'United State';
            $user_connection_detail_model->country_code = 'US';
            $user_connection_detail_model->currency = 'USD';
            $user_connection_detail_model->currency_symbol = '$';
            $user_connection_detail_model->others = '';
            $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
            $user_connection_detail_model->updated_at = date('Y-m-d H:i:s', time());
            $user_connection_detail_model->save(false); 

            return $this->redirect(['index', 'id' => $id]);
        }
    }

    /**
    * remove feed file from server
    * @$base_file_name: file name of the full url
    */
    private function removeFeedFile($base_file_name) {
        $file_url = join(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@storage'),
            'web',
            'source',
            'googleshopping',
            $base_file_name
        ]);

        if(file_exists($file_url)) {
            unlink($file_url);    
        }
    }

    /**
    * create the feed file with the category list selected
    * @$category_list: category id list in Category table
    */
    private function createFeedFile($category_list, $feed_name, $new = true) {
        $product_category_list = ProductCategory::find()->select('product_id')->where(['category_id' => $category_list])->all();
        
        if(empty($product_category_list) or count($product_category_list) == 0) {
            throw new \Exception('There is no product category');
        }

        $product_id_list = array_column($product_category_list, 'product_id');
        $product_list = Product::find()->where(['id' => $product_id_list])->all();
        
        if($new) {
            // create the feed file path
            $date = new \DateTime();
            $date->format("U");
            $timestamp = $date->getTimestamp();
            $filename = $feed_name . '_' . $timestamp . '.xml';            
        } else {
            $filename = $feed_name;
        }

        $file_full_path = join(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@storage'),
            'web',
            'source',
            'googleshopping'
        ]);

        $nsUrl = 'http://base.google.com/ns/1.0';

        $doc = new \DOMDocument('1.0', 'UTF-8');

        $rootNode = $doc->appendChild($doc->createElement('rss'));
        $rootNode->setAttribute('version', '2.0');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', $nsUrl);

        $channelNode = $rootNode->appendChild($doc->createElement('channel'));
        $channelNode->appendChild($doc->createElement('title', $feed_name));
        $channelNode->appendChild($doc->createElement('description', 'Elliot feed'));
        $channelNode->appendChild($doc->createElement('link', env('SEVER_URL')));

        if(empty($product_list) or count($product_list) == 0) {
            throw new \Exception('There is no product');
        }

        foreach ($product_list as $single_product) {
            $itemNode = $channelNode->appendChild($doc->createElement('item'));
            $itemNode->appendChild($doc->createElement('title'))->appendChild($doc->createTextNode($single_product->name));
            $itemNode->appendChild($doc->createElement('description'))->appendChild($doc->createTextNode($single_product->description));
            $itemNode->appendChild($doc->createElement('link'))->appendChild($doc->createTextNode($single_product->url));
            $itemNode->appendChild($doc->createElement('g:id'))->appendChild($doc->createTextNode( uniqid() ));
            $itemNode->appendChild($doc->createElement('g:price'))->appendChild($doc->createTextNode( $single_product->sales_price ));
            $itemNode->appendChild($doc->createElement('g:brand'))->appendChild($doc->createTextNode( $single_product->brand ));
            $itemNode->appendChild($doc->createElement('g:condition'))->appendChild($doc->createTextNode( $single_product->condition?$single_product->condition:Product::PRODUCT_CONDITION_NEW ));
            $product_image_row = ProductImage::find()->where(['product_id' => $single_product->id])->one();
            $itemNode->appendChild($doc->createElement('g:image_link'))->appendChild($doc->createTextNode( (!empty($product_image_row))?$product_image_row->link:'' ));
        }

        if (!file_exists($file_full_path)) {
            mkdir($file_full_path, 0777, true);
        }



        $result = $doc->save(join(DIRECTORY_SEPARATOR, [
            $file_full_path,
            $filename
        ]));

        $file_link_path = join(DIRECTORY_SEPARATOR, [
            env('SEVER_URL'),
            'storage',
            'web',
            'source',
            'googleshopping'
        ]);

        return array(
            'filename' => $filename,
            'file_full_path' => $file_link_path
        );
    }


    public function getDatafeed($datafeed_id) {
        try {
            $session = Yii::$app->session;
            $merchant_id = $session->get('google_merchant_id');
            $client = new \Google_Client();
            $client->setAccessToken($session->get('google_access_token'));
            $service = new \Google_Service_ShoppingContent($client);
            $datafeed = $service->datafeeds->get($merchant_id, $datafeed_id);
            return $datafeed;
        } catch(\Exception $e) {
            return null;
        }
    }

    public function insertDatafeed(\Google_Service_ShoppingContent_Datafeed $datafeed) {
        $session = Yii::$app->session;
        $merchant_id = $session->get('google_merchant_id');
        $client = new \Google_Client();
        $client->setAccessToken($session->get('google_access_token'));
        $service = new \Google_Service_ShoppingContent($client);
        $response = $service->datafeeds->insert($merchant_id, $datafeed);
        return $response;
    }

    public function updateDatafeed(
        \Google_Service_ShoppingContent_Datafeed $datafeed,
        $post_variable,
        $file_name,
        $full_file_path
    ) {
        $session = Yii::$app->session;
        $merchant_id = $session->get('google_merchant_id');
        $client = new \Google_Client();
        $client->setAccessToken($session->get('google_access_token'));
        $service = new \Google_Service_ShoppingContent($client);

        $datafeed->setContentType('products');
        $datafeed->setContentLanguage($post_variable['language']);

        $datafeed->setIntendedDestinations(empty($post_variable['destinations'])?array():$post_variable['destinations']);
        $datafeed->setTargetCountry($post_variable['target_country']);

        $target = new \Google_Service_ShoppingContent_DatafeedTarget();
        $target->setCountry($post_variable['target_country']);
        $target->setLanguage($post_variable['language']);
        $datafeed->setTargets([$target]);

        if(empty($datafeed->getFetchSchedule())) {
            $fetch_schedule = new \Google_Service_ShoppingContent_DatafeedFetchSchedule();
            switch ($post_variable['fetch_frequency']) {
                case 'weekly':
                    if(!empty($post_variable['fetch_weekday'])) {
                        $fetch_schedule->setWeekday($post_variable['fetch_weekday']);
                    }
                    break;

                case 'monthly':
                    if(!empty($post_variable['fetch_date'])) {
                        $fetch_schedule->setDayOfMonth($post_variable['fetch_date']);
                    }
                    break;
            }
            
            $fetch_schedule->setHour((int)$post_variable['fetch_time']);
            $fetch_schedule->setTimeZone($post_variable['timezone']);

            $fetch_url = join(DIRECTORY_SEPARATOR, [
                $full_file_path,
                $file_name
            ]);
            $fetch_url = str_replace('\\','/',$fetch_url);
            $fetch_schedule->setFetchUrl($fetch_url);

            $fetch_schedule->setUsername('');
            $fetch_schedule->setPassword('');

            $format = new \Google_Service_ShoppingContent_DatafeedFormat();
            $format->setFileEncoding('utf-8');
            $format->setColumnDelimiter('tab');
            $format->setQuotingMode('value quoting');
            $datafeed->setFetchSchedule($fetch_schedule);
            $datafeed->setFormat($format);
        } else {
            switch ($post_variable['fetch_frequency']) {
                case 'weekly':
                    if(!empty($post_variable['fetch_weekday'])) {
                        $datafeed->getFetchSchedule()->setWeekday($post_variable['fetch_weekday']);
                    }
                    break;

                case 'monthly':
                    if(!empty($post_variable['fetch_date'])) {
                        $datafeed->getFetchSchedule()->setDayOfMonth($post_variable['fetch_date']);
                    }
                    break;
            }

            $datafeed->getFetchSchedule()->setHour((int)$post_variable['fetch_time']);
            $datafeed->getFetchSchedule()->setTimeZone($post_variable['timezone']);
        }

        $response = $service->datafeeds->update($merchant_id, $datafeed->getId(), $datafeed);
        return $response;
    }

    private function createDatafeed(
        $post_variable,
        $file_name,
        $full_file_path
    ) {
        $datafeed = new \Google_Service_ShoppingContent_Datafeed();
        // The file name must unique per account, so we add a unique part to avoid
        // clashing with any existing feeds.
        $datafeed->setName($post_variable['feed_name']);
        $datafeed->setContentType('products');
        $datafeed->setAttributeLanguage($post_variable['language']);
        $datafeed->setContentLanguage($post_variable['language']);
        $datafeed->setIntendedDestinations(empty($post_variable['destinations'])?array():$post_variable['destinations']);
        $datafeed->setFileName($file_name);
        $datafeed->setTargetCountry($post_variable['target_country']);

        $fetch_schedule = new \Google_Service_ShoppingContent_DatafeedFetchSchedule();
        switch ($post_variable['fetch_frequency']) {
            case 'weekly':
                if(!empty($post_variable['fetch_weekday'])) {
                    $fetch_schedule->setWeekday($post_variable['fetch_weekday']);
                }
                break;

            case 'monthly':
                if(!empty($post_variable['fetch_date'])) {
                    $fetch_schedule->setDayOfMonth($post_variable['fetch_date']);
                }
                break;
        }
        
        $fetch_schedule->setHour((int)$post_variable['fetch_time']);
        $fetch_schedule->setTimeZone($post_variable['timezone']);
        
        $fetch_url = join(DIRECTORY_SEPARATOR, [
            $full_file_path,
            $file_name
        ]);
        $fetch_url = str_replace('\\','/',$fetch_url);
        $fetch_schedule->setFetchUrl($fetch_url);

        $fetch_schedule->setUsername('');
        $fetch_schedule->setPassword('');

        $format = new \Google_Service_ShoppingContent_DatafeedFormat();
        $format->setFileEncoding('utf-8');
        $format->setColumnDelimiter('tab');
        $format->setQuotingMode('value quoting');
        $datafeed->setFetchSchedule($fetch_schedule);
        $datafeed->setFormat($format);
        return $datafeed;
    }

    /**
    * get the data feed list
    *@ $user_id: id in the User table
    *@ $token: google token
    *@ $merchant_id: merchant id
    */
    public function listDatafeeds($user_id, $token, $merchant_id) {
        $feed_instance = Feed::findOne(['name' => 'google']);

        $user_feed_rows = UserFeed::find()->select(['feed_connection_id'])->where([
            'user_id' => $user_id,
            'feed_id' => $feed_instance->id
        ])->all();

        if(!empty($user_feed_rows) and count($user_feed_rows) > 0) {
            $feed_id_list = array_column($user_feed_rows, 'feed_connection_id');

            $client = new \Google_Client();
            $client->setAccessToken($token);
            $service = new \Google_Service_ShoppingContent($client);

            try {
                $session = Yii::$app->session;
                $merchant_id = $session->get('google_merchant_id');
                $service->accounts->get($merchant_id, $merchant_id);
                $datafeed_handler = $service->datafeeds->listDatafeeds($merchant_id);

                // obtain the feed list
                $returns = [];
                foreach ($datafeed_handler->getResources() as $single_feed_hanlder) {
                    if(array_search($single_feed_hanlder->getId(), $feed_id_list, true) === false) {
                        continue;
                    }

                    $single_feed = array(
                        'id' => $single_feed_hanlder->getId(),
                        'name' => $single_feed_hanlder->getName(),
                        'contentType' => $single_feed_hanlder->getContentType(),
                        'fetchUrl' => empty($single_feed_hanlder->getFetchSchedule())?'':$single_feed_hanlder->getFetchSchedule()->getFetchUrl(),
                        'targets' => $single_feed_hanlder->getTargets()
                    );

                    $returns[] = $single_feed;
                }

                return $returns;
            } catch(\Google_Service_Exception $e) {
                $message = $e->getMessage();
                $message = json_decode($message);
                throw new \Exception($message->error->message);
            }
        }
    }
}