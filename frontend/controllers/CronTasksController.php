<?php

namespace frontend\controllers;

use Yii;
use common\models\CronTasks;
use common\models\CronTasksSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Stores;
use common\models\User;
use common\models\CustomFunction;
use common\models\Notification;
use common\models\StoresConnection;
use common\models\Smartling;
use Smartling\AuthApi;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\FileApi;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use common\models\BillingInvoice;
use common\models\Channelsetting;
use common\models\SmartlingPrice;
use common\models\StoreDetails;
use common\models\ProductAbbrivation;
use common\components\ElliShopifyClient as Shopify;
use Bigcommerce\Api\Client as Bigcommerce;
use SoapClient;
use SoapFault;
use Automattic\WooCommerce\Client as Woocommerce;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use common\models\Channels;
use common\models\ChannelConnection;

/**
 * CronTasksController implements the CRUD actions for CronTasks model.
 */
class CronTasksController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
	return [
	    'verbs' => [
		'class' => VerbFilter::className(),
		'actions' => [
		    'delete' => ['POST'],
		],
	    ],
	];
    }

    /**
     * Lists all CronTasks models.
     * @return mixed
     */
    public function actionIndex() {
	$pending_tasks = CronTasks::find()->where(['status' => 'Pending'])->all();
	foreach ($pending_tasks as $task):
	    $uid = $task->elliot_user_id;
	    $user = User::find()->where(['id' => $uid])->one();
	    $user_domain = $user->domain_name;
	    $task_name = $task->task_name;
	    $task_source = $task->task_source;

	    if ($task_name == 'Import'):
		//Import Data
		if ($task_source == 'BigCommerce'):
		    //Import the BigCommerce Store Data          
		    $get_notify = Stores::bigcommerce_initial_import($user);
		    //If Import done successfully
		    if ($get_notify == 'true'):
			//Send Notification Email
			$email = $user->email;
			$company_name = $user->company_name;
			$send_email_notif = CustomFunction::ConnectBigCommerceEmail($email, $company_name);
			//Drop-Down Notification for User
			$notif_type = 'BigCommerce';
			$notif_db = Notification::find()->Where(['notif_type' => $notif_type])->one();
			if (empty($notif_db)):
			    $notification_model = new Notification();
			    $notification_model->user_id = $user->id;
			    $notification_model->notif_type = $notif_type;
			    $notification_model->notif_description = 'Your BigCommerce store data has been successfully imported.';
			    $notification_model->created_at = date('Y-m-d h:i:s', time());
			    $notification_model->save(false);
			endif;
			//Sticky Notification :StoresConnection Model Entry for Import Status
			$get_rec = StoresConnection::find()->Where(['user_id' => $user->id, 'store_id' => 1])->one();
			$get_rec->import_status = 'Completed';
			$get_rec->save(false);
		    endif;
		    //Update the Status of Task in Main DB
		    $get_task = CronTasks::find()->where(['task_id' => $task->task_id])->one();
		    if (!empty($get_task)):
			$get_task->status = 'Completed';
			$get_task->updated_at = date('Y-m-d h:i:s', time());
			$get_task->save(false);
		    endif;
		endif;
	    endif;

	endforeach;

//        $searchModel = new CronTasksSearch();
//        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//
//        return $this->render('index', [
//            'searchModel' => $searchModel,
//            'dataProvider' => $dataProvider,
//        ]);
    }

    /**
     * Displays a single CronTasks model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
	return $this->render('view', [
		    'model' => $this->findModel($id),
	]);
    }

    /**
     * Creates a new CronTasks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
	$model = new CronTasks();

	if ($model->load(Yii::$app->request->post()) && $model->save()) {
	    return $this->redirect(['view', 'id' => $model->task_id]);
	} else {
	    return $this->render('create', [
			'model' => $model,
	    ]);
	}
    }

    /**
     * Updates an existing CronTasks model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
	$model = $this->findModel($id);

	if ($model->load(Yii::$app->request->post()) && $model->save()) {
	    return $this->redirect(['view', 'id' => $model->task_id]);
	} else {
	    return $this->render('update', [
			'model' => $model,
	    ]);
	}
    }

    /**
     * Deletes an existing CronTasks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
	$this->findModel($id)->delete();

	return $this->redirect(['index']);
    }

    /**
     * Finds the CronTasks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CronTasks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
	if (($model = CronTasks::findOne($id)) !== null) {
	    return $model;
	} else {
	    throw new NotFoundHttpException('The requested page does not exist.');
	}
    }

    /* SmartLing Task */

    public function actionSmartlingCron() {
	$basedir = Yii::getAlias('@basedir');
	$baseurl = Yii::getAlias('@baseurl');
	$stripe_init_file = $basedir . '/stripe/init.php';
	require $stripe_init_file;
	\Stripe\Stripe::setApiKey("sk_test_2GPaBt5mT2k0yyTA9ho3CPIQ");
	require_once $basedir . '/example-jobs-api/vendor/autoload.php';

	/* Get Elli All Users */
	$Elli_user = User::find()->Where(['smartling_status' => 'active'])->all();
	if (!empty($Elli_user)) {
	    foreach ($Elli_user as $Elli_user_data) {
		$customer_token = $Elli_user_data->customer_token;
		$email = $Elli_user_data->email;
		$id = $Elli_user_data->id;
		$user_domain = $Elli_user_data->domain_name;

		$smartling = Smartling::find()->Where(['<>', 'token', ''])->andWhere(['connected' => 'yes'])->all();
		foreach ($smartling as $smartling_data) {
		    $user_id		= $smartling_data->elliot_user_id;
		    $token_name		= $smartling_data->token;
		    $folder_name	= $user_domain . '_' . $user_id;
		    $project_id		= $smartling_data->project_id;
		    $locale		= $smartling_data->target_locale;
		    $userSecretKey	= $smartling_data->secret_key;
		    $userIdentifier	= $smartling_data->sm_user_id;
		    $fileName		= $smartling_data->job_callbackUrl;
		    $jobId		= $smartling_data->job_translationJobUid;
		    $project_type_value = $smartling_data->project_type_value;
		    $Channelsetting	= Channelsetting::find()->Where(['user_id' => $user_id, 'channel_name' => $token_name, 'setting_key' => 'Price'])->one();
		    $ChannelsettingCost = Channelsetting::find()->Where(['user_id' => $user_id, 'channel_name' => $token_name, 'setting_key' => 'Translationtype'])->one();
		    $price = 0;
		    if (!empty($Channelsetting)) {
			$price		= $Channelsetting->setting_value;
		    }
		    if (!empty($ChannelsettingCost)) {
			$PriceTaken	= $ChannelsettingCost->setting_value;
		    }
		    $projectId		= $project_id;
		    $retrievalType	= 'published';
		    $locale		= $locale;
		    $userSecretKey	= $userSecretKey;
		    $userIdentifier	= $userIdentifier;
		    $fileName		= $fileName;
		    /* $projectId      = '7289bfb87';
		      $retrievalType  = 'pseudo';
		      $locale         = 'zh-CN';
		      $userSecretKey  = '2anhc34el124ngi7pd46epc21kVM_ui3vgsmu1usvlshgfnva3viu6j';
		      $userIdentifier = 'bhabaskqrnadgqgarxhuhawydiicml';
		      $fileName       = 'test2.json'; */


		    $domain_folder_path = $basedir . '/img/uploaded_files/smartling_files' . '/' . $folder_name;
		    if (is_dir($domain_folder_path)) {
			// is_dir - tells whether the filename is a directory
			$create_token_folder = $domain_folder_path . '/' . $token_name;
			$token_file = $create_token_folder . '/' . $token_name . '.json';
			if (!file_exists($create_token_folder)) {
			    mkdir($create_token_folder);
			    $create_token_file = fopen($token_file, "w") or die("Unable to open file!");
			    /* Call Smartling file Download Function */
			    $this->SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId);
			} else {
			    $this->SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId);
			}
		    }
		    $smartling12 = Smartling::find()->Where(['project_id' => $projectId])->one();
		    $job_referenceNumber = $smartling12->job_referenceNumber;
		    if (empty($job_referenceNumber)) {
			if (!empty($project_type_value)) {
			    if ($PriceTaken == 'Google MT with Edit') {
				$selectRate = 'rate2';
				$SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $locale])->one();
				$priceUnitCost = $SmartlingPrice->post_edit;
			    } else if ($PriceTaken == 'Translation with Edit') {
				$selectRate = 'editing';
				$SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $locale])->one();
				$priceUnitCost = $SmartlingPrice->editing;
			    } else {
				$priceUnitCost = 1;
			    }
			    $data = $price;
			    $this->Subscriptionpaymentcustom($userIdentifier, $userSecretKey, $project_id, $jobId, $customer_token, $email, $user_domain, $id, $priceUnitCost, $project_type_value, $token_name);
			}
			$smartling12->job_referenceNumber = 'Done';
			$smartling12->save(false);
		    }
		    // }
		}
		self::actionSendTranslatedDataChannelStore();
		self::smartLingProductTranslationStatusOn();
	    }
	    
	}
    }

    public function checkFileTranslationStatus($userIdentifier, $userSecret, $projectId, $translationJobUid) {

	$data_string = '{
                    "userIdentifier": "' . $userIdentifier . '",
                    "userSecret": "' . $userSecret . '"
                  }';
	$curl = curl_init();
	$url = 'https://api.smartling.com/auth-api/v2/authenticate';
	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 100,
	    CURLOPT_TIMEOUT => 900,
	    CURLOPT_SSL_VERIFYHOST => FALSE,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "POST",
	    CURLOPT_POSTFIELDS => $data_string,
	    CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"charset: utf-8",
		"X-Auth-Token: BfcqrhanfJghxYZQe8PtDhUMuQHaPtqqRuQZtpZE&jkfnkCfeRBVXLsiBqFdXDaB",
		"content-type: application/json"
	    ),
	));
	$resultAuth = curl_exec($curl);
	$authData = json_decode($resultAuth);

	$access_token = $authData->response->data->accessToken;

	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => "https://api.smartling.com/jobs-api/v3/projects/" . $projectId . "/jobs/" . $translationJobUid,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_SSL_VERIFYHOST => FALSE,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 30,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	    CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"charset: utf-8",
		"Authorization: Bearer " . $access_token,
	    ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	    return 'curl_error';
	} else {
	    $result = json_decode($response);
	    //echo "<pre>"; print_r($result);
	    return $result->response->data->jobStatus;
	}
    }

    /* Smartling file Download Function */

    public function SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId) {
	/* Check File Status  */
	$file_download_status = $this->checkFileTranslationStatus($userIdentifier, $userSecretKey, $projectId, $jobId);
	if ($file_download_status != 'curl_error') {
	    $smartling_model = Smartling::find()->Where(['project_id' => $projectId])->one();
	    $job_download_status = $smartling_model->job_download_status;
	    $job_download_status_array = array('COMPLETED', 'COMPLETED-DOWNLOADED', 'COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED', 'COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED-NOTIFIED');
	    if (!in_array($job_download_status, $job_download_status_array)) {
		if (!empty($smartling_model)) {
		    $smartling_model->job_download_status = $file_download_status;
		    $smartling_model->save(FALSE);
		}
	    }
	    if ($job_download_status == "COMPLETED") {
		/** Download file example */
		try {
		    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);
		    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
		    $params = new \Smartling\File\Params\DownloadFileParameters();
		    $params->setRetrievalType($retrievalType);
		    $file_data = $fileApi->downloadFile($fileName, $locale, $params);
		    file_put_contents($token_file, $file_data);
		    $smartling_model->job_download_status = 'COMPLETED-DOWNLOADED';
		    $smartling_model->save(false);
		    return $file_data;
		} catch (\Smartling\Exceptions\SmartlingApiException $e) {
		    $messageTemplate = 'Error happened while downloading file.' . PHP_EOL
			    . 'Response code: %s' . PHP_EOL
			    . 'Response message: %s' . PHP_EOL;
		    echo vsprintf(
			    $messageTemplate, [
			$e->getCode(),
			$e->getMessage(),
			    ]
		    );
		}
	    }
	}
    }

    public function Subscriptionpaymentcustom($userIdentifier, $userSecretKey, $project_id, $jobId, $customer_token, $email, $user_domain, $id, $priceUnitCost, $project_type_value, $token_name) {
	/* get current user login id */
	$user_id = $id;
	$user_domain = $user_domain;
	$priceUnitCost = substr($priceUnitCost, 1);

	$data_action = $this->actionNewtest($userIdentifier, $userSecretKey, $project_id, $jobId, $project_type_value);
	$data = $data_action * $priceUnitCost;
	$amount_refund_data = intval($data);
	if (!empty($amount_refund_data)) {
	    $invoice_name = $user_id . '_' . $user_domain . '_' . $customer_token;
	    if (!empty($customer_token)) {
                $billing_invoice_check = BillingInvoice::find()->where(['channel_accquired' => $token_name])->one();
                if(!empty($billing_invoice_check)){
                    $stripe_id = $billing_invoice_check->stripe_id;
                    $amount_charged = $billing_invoice_check->amount;
                    if($amount_charged>$amount_refund_data){
                        $amount_to_be_refund = $amount_charged-$amount_refund_data;
                        if($amount_to_be_refund>0){
                            try{
                                $stripe_refund = \Stripe\Refund::create(array(
                                    "charge" => $stripe_id,
                                    "amount" => $amount_to_be_refund*100,
                                ));
                                $refund_status = $stripe_refund->status;
                                if($refund_status=='succeeded'){
                                    $billing_invoice_check->refund_amount = $amount_to_be_refund;
                                    $billing_invoice_check->save(false);
                                }
                            }
                            catch(Exception $ex){
                                $ex->getMessage();
                            }
                        }
                    }
                }
	    }
	}
    }

    public function actionNewtest($userIdentifier, $userSecretKey, $project_id, $jobId, $project_type_value) {
	$data_string = '{
                        "userIdentifier": "' . $userIdentifier . '",
                        "userSecret": "' . $userSecretKey . '"
                      }';

	$curl = curl_init();
	$url = 'https://api.smartling.com/auth-api/v2/authenticate';
	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 100,
	    CURLOPT_TIMEOUT => 900,
	    CURLOPT_SSL_VERIFYHOST => FALSE,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "POST",
	    CURLOPT_POSTFIELDS => $data_string,
	    CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"charset: utf-8",
		"X-Auth-Token: BfcqrhanfJghxYZQe8PtDhUMuQHaPtqqRuQZtpZE&jkfnkCfeRBVXLsiBqFdXDaB",
		"content-type: application/json"
	    ),
	));

	$resultAuth = curl_exec($curl);

	// echo '<pre>'; print_r(json_decode($resultAuth)); die();
	$authData = json_decode($resultAuth);

	$access_token = $authData->response->data->accessToken;

	//​/estimates-api/v2/projects/{projectId}/reports/{reportUid}/status
	$curl = curl_init();
	$url = 'https://api.smartling.com/estimates-api/v2/projects/' . $project_id . '/reports/' . $project_type_value;
	//echo '</br>';
	//echo $url = 'https://api.smartling.com/estimates-api/v2/projects/5ecfd37f7/reports/7pcbcl422dol';
	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 100,
	    CURLOPT_TIMEOUT => 900,
	    CURLOPT_SSL_VERIFYHOST => FALSE,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	    CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"charset: utf-8",
		"Authorization: Bearer " . $access_token,
		"content-type: application/json"
	    ),
	));

	$result2 = curl_exec($curl);
	$datareport = json_decode($result2);
	//echo '<pre>'; print_r($datareport);
	/* die(); */
	$totalWeightedWordCount = $datareport->response->data->totalWeightedWordCount;
	return $totalWeightedWordCount;
	//echo '<pre>'; print_r(json_decode($result2));
    }

    public static function actionSendTranslatedDataChannelStore() {
	$smartling_data = Smartling::find()->Where(['connected' => 'yes', 'translated_data_process' => 'pending', 'job_download_status' => 'COMPLETED-DOWNLOADED'])->andWhere(['!=', 'token', ''])->limit(5)->all();
	if (!empty($smartling_data)) {
	    foreach ($smartling_data as $_smart) {
		$user_id = $_smart->elliot_user_id;
		$token = $_smart->token;
		if ($token != '') {
		    $store_name = strstr($token, ' ', true);
		    $store_country = strstr($token, ' ');
		    $check_for_store = array('Salesforce Commerce Cloud', 'Oracle Commerce Cloud', 'NetSuite SuiteCommerce');
		    if (!in_array($store_name, $check_for_store)) {
			$store_details_data = StoreDetails::find()->where(['channel_accquired' => trim($store_name), 'country' => trim($store_country)])->with('storeConnection')->one();
			//echo "<pre>"; print_r($store_details_data); echo "</pre>";
			if (!empty($store_details_data)) {
			    $current_store = $store_details_data->channel_accquired;
			    if ($current_store == 'Shopify') {
				self::changeShopifyProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			    if ($current_store == 'ShopifyPlus') {
				self::changeShopifyProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			    if ($current_store == 'BigCommerce') {
				self::changeBigcommerceProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			    if ($current_store == 'Magento') {
				self::changeMagentoProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			    if ($current_store == 'Magento2') {
				self::changeMagento2ProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			    if ($current_store == 'WooCommerce') {
				self::changeMagento2ProductDetails($store_details_data, $token, $_smart, $current_store);
				self::addNotification($token, $user_id);
				self::sendMailNotification($token, $user_id);
			    }
			}
		    }
		}
	    }
	}
        
        $channel_connection_data = ChannelConnection::find()->Where(['smartling_status' => 'active'])->all();
        if(!empty($channel_connection_data)){
            foreach($channel_connection_data as $_channel_data){
                $user_id = $_channel_data->elliot_user_id;
                $channel_id = $_channel_data->channel_id;
                $channel_data = Channels::find()->where(['channel_ID' => $channel_id])->one();
                if(!empty($channel_data)){
                    $channel_name = $channel_data->channel_name;
                    if($channel_name=='Service Account' || $channel_name=='Subscription Account'){
                        $channel_name = 'WeChat';
                    }
                    $smartling_data = Smartling::find()->Where(['token' => $channel_name, 'connected' => 'yes', 'translated_data_process' => 'pending', 'job_download_status' => 'COMPLETED-DOWNLOADED'])->andWhere(['!=', 'token', ''])->one();
                    if(!empty($smartling_data)){
                        $token = $smartling_data->token;
                        if($channel_name=='Square'){
                            self::changeSquareProductDetails($_channel_data, $token, $smartling_data, $user_id, $channel_name);
                            self::addNotification($token, $user_id);
                            self::sendMailNotification($token, $user_id);
                        }
                        //if($channel_name=='WeChat'){
                        //    self::changeWechatProductDetails($_channel_data, $token, $smartling_data, $user_id, $channel_name);
                        //    self::addNotification($token, $user_id);
                        //    self::sendMailNotification($token, $user_id);
                        //}
                        if($channel_name=='Lazada Malaysia' || $channel_name=='Lazada Vietnam' || $channel_name=='Lazada Singapore' || $channel_name=='Lazada Indonesia' || $channel_name=='Lazada Thailand'){
                            self::changeLazadaProductDetails($_channel_data, $token, $smartling_data, $user_id, $channel_name);
                            self::addNotification($token, $user_id);
                            self::sendMailNotification($token, $user_id);
                        }
                    }
                }
            }
        }
    }
    
    public static function changeLazadaProductDetails($channel_connection, $token, $_smart, $user_id, $channel_name){
        $user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
        $domain_name = $user_data->domain_name;
        $file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
        if ($file_decode_data != 'no data') {
            $count = 1;
            foreach ($file_decode_data as $file_value) {
                if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];
                    
                    $lazada_product_data = ProductAbbrivation::find()->Where(['product_id' => $product_id, 'channel_accquired' => $channel_name])->with('product')->one();
                    if(!empty($lazada_product_data)){
                        $lazada_product_id = substr($lazada_product_data->channel_abb_id, 2);
                        $sku = $lazada_product_data->product->SKU;
                        $user_email = $channel_connection->lazada_user_email;
                        $url = $channel_connection->lazada_api_url;
                        $api_key = $channel_connection->lazada_api_key;
                        $parameters = array(
                            'UserID' => $user_email,
                            'Version' => '1.0',
                            'Action' => 'UpdateProduct',
                            'Format' => 'JSON',
                            'Timestamp' => date('c')
                        );
                        ksort($parameters);
                        $encoded = array();
                        foreach ($parameters as $name => $value) {
                            $encoded[] = rawurlencode($name) . '=' . rawurlencode($value);
                        }
                        $concatenated = implode('&', $encoded);
                        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $concatenated, $api_key, false));
                        $queryString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
                        $target = $url . '/?' . $queryString;
                        
                        $input_request = "<?xml version='1.0' encoding='UTF-8'?>
                            <Request>
                                <Product>
                                    <Attributes>
                                        <name>" . $product_title . "</name>
                                        <short_description>" . htmlspecialchars($product_des) . "</short_description>
                                    </Attributes>
                                    <Skus>
                                        <Sku>
                                            <SellerSku>" . $sku . "</SellerSku>
                                        </Sku>
                                    </Skus>
                                </Product>
                            </Request>";
                        
                        $basepath_uploaded_files = Yii::getAlias('@upload_files');
                        $file_name = 'SM_'.$domain_name.'_update_product.xml';
                        $dir = $basepath_uploaded_files.'/lazada_xml_files/';
                        if( is_dir($dir) === false ){
                            mkdir($dir);
                        }
                        if (!file_exists($dir)) {
                            chmod($dir, 0777);
                            $myfile = fopen($dir, "w");
                            file_put_contents($basepath_uploaded_files . '/lazada_xml_files/' . $file_name, $input_request);
                        }
                        else{
                            file_put_contents($basepath_uploaded_files . '/lazada_xml_files/' . $file_name, $input_request);
                        }
                        
                        $tmpFile = $basepath_uploaded_files . '/lazada_xml_files/' . $file_name;
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_PUT, 1);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($curl, CURLOPT_INFILESIZE, filesize($tmpFile));
                        curl_setopt($curl, CURLOPT_INFILE, ($in = fopen($tmpFile, 'r')));
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                        curl_setopt($curl, CURLOPT_URL, $target);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($curl);
                        curl_close($curl);
                        fclose($in);
                        $result_decoded = json_decode($result, true);
                        if (!isset($result_decoded['SuccessResponse']) and empty($result_decoded['SuccessResponse'])) {
                            $error_msg = "Product is not update on Lzada product id is => ".$product_id;
                            self::printErrorLog($error_msg);
                        }
                    }
                }
                if($count==3){
                    break;
                }
                $count++;
            }
            $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
        }
    }

    public static function changeWechatProductDetails($channel_connection, $token, $_smart, $user_id, $channel_name){
        $wechat_user = $channel_connection->username;
        $wechat_pass = $channel_connection->password;
        $user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
        $domain_name = $user_data->domain_name;
        $file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
        if ($file_decode_data != 'no data') {
            foreach ($file_decode_data as $file_value) {
                if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];
                    
                    $wechat_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $channel_name])->one();
                    if(!empty($wechat_product_data)){
                        $wechat_product_id = substr($wechat_product_data->channel_abb_id, 2);
                        $access_token_url = 'https://cms-api.walkthechat.com/login/admin';
                        $admin_array = array(
                            'username' => $wechat_user,
                            'password' => $wechat_pass
                        );
                        $access_data = self::wechatGetAccessToken($access_token_url, $admin_array);
                        $access_token = '';
                        if($access_data != 'Curl error'){
                            $access_token = $access_data['token']['token'];
                            $project_id_url = 'https://cms-api.walkthechat.com/projects/';
                            $project_id_data = self::wechatGetProjectId($project_id_url, $access_token);
                            if($project_id_data!='Curl error' && isset($project_id_data['projects'][0]['_id'])){
                                $project_id = $project_id_data['projects'][0]['_id'];
                                $method = 'GET';
                                $product_url = 'https://cms-api.walkthechat.com/products/groups/'.$wechat_product_id;
                                $wechat_product = self::wechatProductCurl($product_url, $access_token, $project_id, $method);
                                if(array_key_exists('products', $wechat_product)){
                                    $new_wechat_product_id = $wechat_product['products']['en']['product']['_id'];
                                    $project_object = $wechat_product['products']['en']['product'];
                                    $project_object['title'] = $product_title;
                                    $project_object['description'] = $product_des;
                                    $method = 'PUT';
                                    $product_data = http_build_query($project_object);
                                    $product_update_url = 'https://cms-api.walkthechat.com/products/'.$new_wechat_product_id;
                                    $wechat_result = self::wechatProductCurl($product_update_url, $access_token, $project_id, $method, $product_data);
                                    if(!array_key_exists('products', $wechat_result)){
                                        $error_msg = "Product is not update on wechat product id is =>".$product_id;
                                        self::printErrorLog($error_msg);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
        }
    }
    
    public static function changeSquareProductDetails($channel_connection, $token, $_smart, $user_id, $channel_name){
        $square_access_token = $channel_connection->square_personal_access_token;
        $user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
        $domain_name = $user_data->domain_name;
        $file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
        if ($file_decode_data != 'no data') {
            foreach ($file_decode_data as $file_value) {
                if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];
                    
                    $square_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $channel_name])->one();
                    if(!empty($square_product_data)){
                        try{
                            $square_product_id = substr($square_product_data->channel_abb_id, 2);
                            require_once($_SERVER['DOCUMENT_ROOT'].'/backend/web/square/vendor/autoload.php');
                            \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($square_access_token);
                            $api_instance = new \SquareConnect\Api\V1ItemsApi();
                            $item_body = new \SquareConnect\Model\V1Item();
                            $location_id = self::getSquareLocationId();
                            $item_body = array(
                                'name' => $product_title,
                                'description' => $product_des
                            );
                            $product_result = $api_instance->updateItem($location_id, $square_product_id, $item_body);
                        }
                        catch(\SquareConnect\ApiException $ex){
                            $error_msg = "Product is not found on Square Channel product id is =>".$product_id.". API msg is =>".$ex->getResponseBody()->message;
                            self::printErrorLog($error_msg);
                        }
                    }
                }
            }
            $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
        }
    }
    
    public static function getSquareLocationId(){
        try {
            $api = new \SquareConnect\Api\LocationsApi();
            $location_result = $api->listLocations();
            return $location_result['locations'][0]['id'];
        } catch (\SquareConnect\ApiException $ex) {
            echo 'Exception when calling : '; echo  $ex->getResponseBody()->message;
        }
    }

    public static function changeShopifyProductDetails($store_details_data, $token, $_smart, $current_store) {
	$store_connection_id = $store_details_data->store_connection_id;
	$shopify_shop = $store_details_data->storeConnection->url;
	$shopify_api_key = $store_details_data->storeConnection->api_key;
	$shopify_api_password = $store_details_data->storeConnection->key_password;
	$shopify_shared_secret = $store_details_data->storeConnection->shared_secret;
	$user_id = $store_details_data->storeConnection->user_id;
	/* Shopify object here */
	$sc = new Shopify($shopify_shop, $shopify_api_password, $shopify_api_key, $shopify_shared_secret);
	$user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
	$domain_name = $user_data->domain_name;
	$file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
	if ($file_decode_data != 'no data') {
	    foreach ($file_decode_data as $file_value) {
		if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];

		    $shopify_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $current_store, 'mul_store_id' => $store_connection_id])->one();
		    if (!empty($shopify_product_data)) {
			try {
			    $Shopify_product_id = substr($shopify_product_data->channel_abb_id, 2);
			    try {
				$sc->call('GET', '/admin/products/' . $Shopify_product_id . '.json');
			    } catch (Exception $e) {
				$error_msg = "Product is not found on Shopify Product id is =>" . $Shopify_product_id . " => " . $e->getMessage();
				self::printErrorLog($error_msg);
				continue;
			    }
			    $product_data = array(
				'product' => array(
				    'id' => $Shopify_product_id,
				    'title' => $product_title,
				    'body_html' => $product_des,
				),
			    );
			    $sc->call('PUT', '/admin/products/' . $Shopify_product_id . '.json', $product_data);
			    $call_left = $sc->callsLeft();
			    if ($call_left <= 8) {
				sleep(20);
			    }
			} catch (Exception $e) {
			    $msg = "Shopify product not updated because of this error => " . $e->getMessage() . " Product id is " . $product_id . " Multi store id is => " . $store_connection_id;
			    self::printErrorLog($msg);
			}
		    } else {
			$msg = "Product not found id is => " . $product_id . " for shopify multi store id is => " . $store_connection_id;
			self::printErrorLog($msg);
		    }
		}
	    }
	    $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
	}
    }

    public static function changeBigcommerceProductDetails($store_details_data, $token, $_smart, $current_store) {
	$store_connection_id = $store_details_data->store_connection_id;
	$big_client_id = $store_details_data->storeConnection->big_client_id;
	$big_access_token = $store_details_data->storeConnection->big_access_token;
	$big_store_hash = $store_details_data->storeConnection->big_store_hash;
	$user_id = $store_details_data->storeConnection->user_id;
	$user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
	$domain_name = $user_data->domain_name;
	$file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
	if ($file_decode_data != 'no data') {
	    foreach ($file_decode_data as $file_value) {
		if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];

		    $bigcommerce_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $current_store, 'mul_store_id' => $store_connection_id])->one();
		    if (!empty($bigcommerce_product_data)) {
			try {
			    $bigcommerce_product_id = substr($bigcommerce_product_data->channel_abb_id, 3);
			    $product_data = array(
				'name' => $product_title,
				'description' => $product_des,
			    );
			    $url = 'https://api.bigcommerce.com/stores/' . $big_store_hash . '/v3/catalog/products/' . $bigcommerce_product_id;
			    $get_method = 'GET';
			    $check_for_product = self::bigCommerceCurl($url, $big_client_id, $big_access_token, $get_method);
			    if ($check_for_product['http_code'] == '200') {
				$put_method = 'PUT';
				$product_update = self::bigCommerceCurl($url, $big_client_id, $big_access_token, $put_method, $product_data);
			    }
			} catch (Exception $e) {
			    $msg = "Bigcommerce product not updated because of this error => " . $e->getMessage() . " Product id is " . $product_id . " Multi store id is => " . $store_connection_id;
			    self::printErrorLog($msg);
			}
		    } else {
			$msg = "Product not found id is => " . $product_id . " for BigCommerce multi store id is => " . $store_connection_id;
			self::printErrorLog($msg);
		    }
		}
	    }
	    $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
	}
    }

    public static function changeMagentoProductDetails($store_details_data, $token, $_smart, $current_store) {
	$store_connection_id = $store_details_data->store_connection_id;
	$magento_shop = $store_details_data->storeConnection->mag_shop;
	$magento_soap_user = $store_details_data->storeConnection->mag_soap_user;
	$magento_soap_api_key = $store_details_data->storeConnection->mag_soap_api;
	$user_id = $store_details_data->storeConnection->user_id;
	$user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
	$domain_name = $user_data->domain_name;
	$file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
	if ($file_decode_data != 'no data') {
	    foreach ($file_decode_data as $file_value) {
		if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];

		    $magento_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $current_store, 'mul_store_id' => $store_connection_id])->one();
		    if (!empty($magento_product_data)) {
			$magento_product_id = substr($magento_product_data->channel_abb_id, 2);
			$api_url = $magento_shop . '/api/soap/?wsdl';
			$cli = new SoapClient($api_url);
			$session_id = $cli->login($magento_soap_user, $magento_soap_api_key);

			try {
			    $check_prodct_exists = $cli->call($session_id, 'catalog_product.info', $magento_product_id);
			    if (array_key_exists('product_id', $check_prodct_exists)) {
				$product_data = array(
				    'websites' => array(1),
				    'name' => $product_title,
				    'description' => $product_des,
				);
				$product_result = $cli->call($session_id, 'catalog_product.update', array($magento_product_id, $product_data));
			    }
			} catch (SoapFault $e) {
			    $error_msg = "This product is not exists on Magento store. Product id is =>" . $magento_product_id . " Error msg is => " . $e->faultstring;
			    self::printErrorLog($error_msg);
			}
		    } else {
			$msg = "Product not found id is => " . $product_id . " for BigCommerce multi store id is => " . $store_connection_id;
			self::printErrorLog($msg);
		    }
		}
	    }
	    $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
	}
    }

    public static function changeMagento2ProductDetails($store_details_data, $token, $_smart, $current_store) {
	$store_connection_id = $store_details_data->store_connection_id;
	$magento_shop = $store_details_data->storeConnection->mag_shop;
	$magento_2_access_token = $store_details_data->storeConnection->mag_soap_api;
	$user_id = $store_details_data->storeConnection->user_id;
	$user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
	$domain_name = $user_data->domain_name;
	$file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
	if ($file_decode_data != 'no data') {
	    foreach ($file_decode_data as $file_value) {
		if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];
		    $magento_product_data = ProductAbbrivation::find()->Where(['product_id' => $product_id, 'channel_accquired' => $current_store, 'mul_store_id' => $store_connection_id])->with('product')->one();
		    if (!empty($magento_product_data)) {
			$sku = $magento_product_data->product->SKU;
			$magento_product_id = substr($magento_product_data->channel_abb_id, 3);
			$product_check_url = $magento_shop . "/index.php/rest/V1/products/" . $sku;
			$post_data = '';
			$check_product = self::magentoProductCurl($product_check_url, $magento_2_access_token, $post_data, "GET");
			if (!array_key_exists('message', $check_product)) {
			    $product_data = array(
				"product" => array(
				    "sku" => $sku,
				    "name" => $product_title,
				    "custom_attributes" => array(
					array(
					    "attribute_code" => "description",
					    "value" => $product_des
					),
				    ),
				),
			    );
			    $product_update_url = $magento_shop . "/index.php/rest/V1/products/";
			    $result = self::magentoProductCurl($product_update_url, $magento_2_access_token, $product_data, "POST");
			} else {
			    $error_msg = "This product is not exists on Magento store. Product id is =>" . $magento_product_id;
			    self::printErrorLog($error_msg);
			}
		    } else {
			$msg = "Product not found id is => " . $product_id . " for BigCommerce multi store id is => " . $store_connection_id;
			self::printErrorLog($msg);
		    }
		}
	    }
	    $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
	}
    }

    public static function changeWooCommerceProductDetails($store_details_data, $token, $_smart, $current_store) {
	$store_connection_id = $store_details_data->store_connection_id;
	$woo_store_url = $store_details_data->storeConnection->woo_store_url;
	$woo_consumer_key = $store_details_data->storeConnection->woo_consumer_key;
	$woo_secret_key = $store_details_data->storeConnection->woo_secret_key;
	$user_id = $store_details_data->storeConnection->user_id;
	$user_data = User::find()->select('domain_name')->Where(['id' => $user_id])->one();
	$domain_name = $user_data->domain_name;
	$file_decode_data = self::getTranslatedData($domain_name, $user_id, $token, $_smart);
	if ($file_decode_data != 'no data') {
	    foreach ($file_decode_data as $file_value) {
		if (isset($file_value['id'])) {
		    $product_id = preg_replace('/[^A-Za-z0-9-]/', '', $file_value['id']);
		    $product_title = $file_value['name'];
		    $product_des = $file_value['description'];
                    $woocommerce_product_data = ProductAbbrivation::find()->select('channel_abb_id')->Where(['product_id' => $product_id, 'channel_accquired' => $current_store, 'mul_store_id' => $store_connection_id])->with('product')->one();
                    if (!empty($woocommerce_product_data)) {
                        $woocommerce_product_id = substr($woocommerce_product_data->channel_abb_id, 3);
                        $check_url = parse_url($woo_store_url);
                        $url_protocol = $check_url['scheme'];
                        if ($url_protocol == 'http') {
                            /* For Http Url */
                            $woocommerce = new Woocommerce(
                                    $woo_store_url, $woo_consumer_key, $woo_secret_key, [
                                'wp_api' => true,
                                'version' => 'wc/v1',
                                    ]
                            );
                        } else {
                            /* For Https Url */
                            $woocommerce = new Woocommerce($woo_store_url . '/wp-json/wc/v1/products/' . $woocommerce_product_id . '?name=' . $product_title . 'description' . $product_des, [
                                'wp_api' => true,
                                'version' => 'wc/v1',
                                "query_string_auth" => true,
                                    ]
                            );
                        }
                        try {
                            $Prdoucts_woocommerce_check = $woocommerce->get('products/' . $woocommerce_product_id);
                            if (isset($Prdoucts_woocommerce_check['id'])) {
                                $product_data = array(
                                    'name' => $product_title,
                                    'description' => $product_des,
                                );
                                $woocommerce_product_update = $woocommerce->put('products/' . $woocommerce_product_id, $product_data);
                            }
                        } catch (HttpClientException $e) {
                            $error_msg = "Product is not update or not found on WooCommerce. Product id is =>" . $woocommerce_product_id . " and Error msg is =>" . $e->getMessage();
                            self::printErrorLog($msg);
                        }
                    } else {
                        $msg = "Product not found id is => " . $product_id . " for BigCommerce multi store id is => " . $store_connection_id;
                        self::printErrorLog($msg);
                    }
		}
	    }
	    $_smart->translated_data_process = 'complete';
	    $_smart->save(false);
	}
    }

    public function actionTest() {
    }

    public static function addNotification($token = FALSE, $user_id = FALSE) {
	$notification_check = Notification::find()->Where(['notif_type' => $token])->count();
	if ($notification_check == 1 || $notification_check == 0) {
	    $notification_model = new Notification();
	    $notification_model->user_id = $user_id;
	    $notification_model->notif_type = $token;
	    $notification_model->notif_description = 'Your data for ' . $token . ' is successfully translated.';
	    $notification_model->created_at = date('Y-m-d H:i:s', time());
	    $notification_model->updated_at = date('Y-m-d H:i:s', time());
	    $notification_model->save(false);
	}
    }

    public static function sendMailNotification($token = False, $user_id = FALSE) {
	$user_data = User::find()->Where(['id' => $user_id])->one();
	if (!empty($user_data)) {
	    $company_name = $user_data->company_name;
	    $email = $user_data->email;
	    $email_message = 'Your data for ' . $token . ' is successfully translated.';
	    $send_email_notif = CustomFunction::ConnectBigCommerceEmail($email, $company_name, $email_message);
	}
    }

    public static function getTranslatedData($domain_name, $user_id, $token, $_smart) {
	$smartling_files_dir = Yii::getAlias('@smartling_files');
	$folder_name = $domain_name . '_' . $user_id;
	$file_read_data = $smartling_files_dir . '/' . $folder_name . '/' . $token . '/' . $token . '.json';
	if (file_exists($file_read_data)) {
	    $file_data = file_get_contents($file_read_data);
	    $file_decode_data = json_decode($file_data, true);
	    if (count($file_decode_data) == 0) {
		self::printErrorLog("Right now file is not translated");
		return 'no data';
	    } else {
		$_smart->translated_data_process = 'process';
		$_smart->save(false);
		return $file_decode_data;
	    }
	} else {
	    self::printErrorLog("Right now this file is not exitst name is => " . $token);
	    return 'no data';
	}
    }

    public function printErrorLog($msg) {
	Yii::warning($msg);
    }

    public static function smartLingProductTranslationStatusOn() {
	$smartling_data = Smartling::find()->Where(['translated_data_process' => 'complete', 'job_download_status' => 'COMPLETED-DOWNLOADED'])->andWhere(['!=', 'token', ''])->all();
	if (!empty($smartling_data)) {
	    foreach ($smartling_data as $_smart) {
		$user_id = $_smart->elliot_user_id;
		$token = $_smart->token;
		if ($token != '') {
		    $store_name = strstr($token, ' ', true);
		    $store_country = strstr($token, ' ');
		    $check_for_store = array('Salesforce Commerce Cloud', 'Oracle Commerce Cloud', 'NetSuite SuiteCommerce');
		    if (!in_array($store_name, $check_for_store)) {
			$store_details_data = StoreDetails::find()->where(['channel_accquired' => trim($store_name), 'country' => trim($store_country)])->with('storeConnection')->one();
			if (!empty($store_details_data)) {
			    $store_connection_id = $store_details_data->store_connection_id;
			    $user_id = $store_details_data->storeConnection->user_id;
			    ProductAbbrivation::updateAll(array('translation_status' => 'yes'), 'channel_accquired = "' . trim($store_name) . '" AND mul_store_id = "' . $store_connection_id . '"');
			    $_smart->job_download_status = 'COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED';
			    $_smart->save(FALSE);
			}
		    }
		}
	    }
	}
        
        $channel_connection_data = ChannelConnection::find()->Where(['smartling_status' => 'active'])->all();
        if(!empty($channel_connection_data)){
            foreach($channel_connection_data as $_channel_data){
                $user_id = $_channel_data->elliot_user_id;
                $channel_id = $_channel_data->channel_id;
                $channel_data = Channels::find()->where(['channel_ID' => $channel_id])->one();
                if(!empty($channel_data)){
                    $channel_name = $channel_data->channel_name;
                    if($channel_name=='Service Account' || $channel_name=='Subscription Account'){
                        $channel_name = 'WeChat';
                    }
                    $smartling_data = Smartling::find()->Where(['token' => $channel_name, 'translated_data_process' => 'complete', 'job_download_status' => 'COMPLETED-DOWNLOADED'])->one();
                    if(!empty($smartling_data)){
                        ProductAbbrivation::updateAll(array('translation_status' => 'yes'), 'channel_accquired = "' . trim($channel_name) . '"' );
                        $smartling_data->job_download_status = 'COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED';
                        $smartling_data->save(FALSE);
                    }
                }
            }
        }
    }

    public static function bigCommerceCurl($url, $client_id, $token, $method, $post_data = FALSE) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_SSL_VERIFYPEER => FALSE,
	    CURLOPT_SSL_VERIFYHOST => FALSE,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => FALSE,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => $method,
	    CURLOPT_POSTFIELDS => json_encode($post_data),
	    CURLOPT_HTTPHEADER => array(
		"accept: application/json",
		"cache-control: no-cache",
		"postman-token: 469842ed-167e-79fa-c5e4-9d2377fb1500",
		"x-auth-client: " . $client_id,
		"x-auth-token: " . $token
	    ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
	    echo "cURL Error #:" . $err;
	} else {
	    $result = json_decode($response, true);
	    $result['http_code'] = $httpcode;
	    return $result;
	}
    }

    /** For magento product update curl */
    public static function magentoProductCurl($url, $access_token, $post_data = FALSE, $methode) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 30,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => $methode,
	    CURLOPT_POSTFIELDS => json_encode($post_data),
	    CURLOPT_HTTPHEADER => array(
		"authorization: Bearer " . $access_token,
		"cache-control: no-cache",
		"content-type: application/json",
		"postman-token: a42259e6-9a49-399c-084d-e195a4247a5b"
	    ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	    //echo "cURL Error #:" . $err;
	    $result = array("message" => 'Curl error');
	    return $result;
	} else {
	    $result = json_decode($response, true);
	    return $result;
	}
    }
    
    
    public static function wechatGetAccessToken($url, $form_data) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => FALSE,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "POST",
	    CURLOPT_POSTFIELDS => http_build_query($form_data),
	    CURLOPT_HTTPHEADER => array(
		"Content-Type: application/x-www-form-urlencoded",
	    ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	    $result = array("message" => 'Curl error', 'error' => $err);
	    return $result;
	} else {
	    $result = json_decode($response, true);
	    return $result;
	}
    }
    
    public static function wechatGetProjectId($url, $access_token) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	    CURLOPT_HTTPHEADER => array(
		"Content-Type: application/x-www-form-urlencoded",
                "x-access-token: $access_token",
	    ),
	));
        $response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	    $result = array("message" => 'Curl error');
	    return $result;
	} else {
	    $result = json_decode($response, true);
	    return $result;
	}
    }
    
    public static function wechatProductCurl($url, $access_token, $project_id, $method='GET', $post_data='') {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 30,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_data,
	    CURLOPT_HTTPHEADER => array(
		"Content-Type: application/x-www-form-urlencoded",
                "x-access-token: $access_token",
                "x-id-project: $project_id"
	    ),
	));
        $response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	    $result = array("message" => 'Curl error');
	    return $result;
	} else {
	    $result = json_decode($response, true);
	    return $result;
	}
    }
    
}
