<?php

namespace frontend\controllers;

use common\models\ChannelConnection;
use common\models\Channels;
use common\models\User;
use common\models\StoresConnection;
use Bigcommerce\Api\Connection;
use common\models\ProductImages;
use frontend\controllers\ChannelsController;
use common\models\Categories;
use common\models\Products;
use common\models\Variations;
use common\models\ProductAbbrivation;
use common\models\VariationsItemList;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\CustomerUser;
use common\models\Notification;
use common\models\ProductCategories;
use common\models\ProductVariation;
use common\models\ProductChannel;
use common\models\Orders;
use common\models\Smartling;
use common\models\MerchantProducts;
use common\models\Channelsetting;
use common\models\GoogleProductCategories;
use common\models\Stores;
use common\models\OrderFullfillment;
use yii\filters\AccessControl;
use Smartling\AuthApi;
use common\components\ElliShopifyClient as Shopify;
use yii\filters\VerbFilter;
use Yii;

class ChannelsettingorderController extends \yii\web\Controller {

    public function behaviors() {
	return ['access' => [
		'class' => AccessControl::className(),
		'only' => ['index', 'tracking', 'save', 'sforders', 'channeldisable', 'translation', 'callback'],
		'rules' => [
			[
			'actions' => ['signup', 'callback'],
			'allow' => true,
			'roles' => ['?'],
		    ],
			[
			'actions' => ['index', 'tracking', 'save', 'sforders', 'channeldisable', 'translation', 'callback'],
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
	return $this->render('index');
    }

    public function actionTracking() {
	//$hawbs = base64_decode($_GET['hawbs']);
	$hawbs = $_GET['hawbs'];
	if (!empty($hawbs)) {
	    $KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds',);
	    $order_data_json = array(
		'Routes' => array(array('SfWaybillNo' => $hawbs, 'TrackingType' => '1',),),
		'NetworkCredential' => $KEYJsonArrayData,
	    );

	    $data_string = json_encode($order_data_json);
	    // echo  $data_string; die('dsfasd');
	    $curl = curl_init();

	    curl_setopt_array($curl, array(
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_URL => "https://sit.api.sf-express-us.com/api/routeservice/query",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_SSL_VERIFYHOST => FALSE,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data_string,
		CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "charset: utf-8",
		    "content-type: application/json"
		),
	    ));

	    $result = curl_exec($curl);
	    $result = json_decode($result);
	    $err = curl_error($curl);
	    //echo '<pre>'; print_r(json_decode($result)); echo '</pre>'; die('dsfa');
	    return $this->render('track', array('result' => $result));
	} else {
	    return $this->render('index');
	}
    }

    public function actionSave() {
	$control = $_POST['control'];
	$channelID = $_POST['channelID'];
	$fulfillmentName = $_POST['fulfillmentName'];
	$fulfillmentID = $_POST['fulfillmentID'];
	$users_Id = Yii::$app->user->identity->id;

	$store_connection = Stores::find()->where(['store_id' => $channelID])->one();
	if (!empty($store_connection)) {
	    $name = $store_connection->store_name;
	} else {
	    $Channels = Channels::find()->where(['channel_id' => $channelID])->one();
	    $name = $Channels->channel_name;
	}
	if ($name == 'Service Account') {
	    $name = 'WeChat';
	}

	if ($control == 'no') {
	    $Channelsetting_delete = Channelsetting::find()->Where(['user_id' => $users_Id, 'channel_id' => $channelID, 'setting_key' => 'fulfillmentID', 'setting_value' => $fulfillmentID])->one();
	    if (!empty($Channelsetting_delete)) {
		$Channelsetting_delete->delete();
	    }
	} else {


	    $Channelsetting = new Channelsetting();
	    $Channelsetting->channel_id = $channelID;
	    $Channelsetting->channel_name = $name;
	    $Channelsetting->setting_key = 'fulfillmentID';
	    $Channelsetting->user_id = $users_Id;
	    $Channelsetting->setting_value = $fulfillmentID;
	    $Channelsetting->created_at = date('Y-m-d H:i:s');
	    if ($Channelsetting->save(false)) {
		echo 'success';
	    } else {
		echo 'error';
	    }
	    //$this->getSforders($channelID);
	}

	//echo $control.'---'.$channelID.'-----'.$fulfillmentName.'----'.$fulfillmentID;
    }

    public function actionSforders() {
	
	$storeid = $_POST['storeid'];
	
	$fulfillmentID = $_POST['fulfillmentID'];
	
	$order = Orders::find()->all();
	
	$store_connection_data = StoresConnection::find()->Where(['store_id' => $storeid])->one();
	if ($store_connection_data) {
	    $iduse = $store_connection_data->stores_connection_id;
	    $sf_orders_data = OrderChannel::find()->where(['store_id' => $iduse])->all();
	} else {
	    $sf_orders_data = OrderChannel::find()->where(['channel_id' => $storeid])->all();
	}
	if (empty($sf_orders_data)) {
	    die('No orders found');
	}
	
	$orderJsonArrayData = array();
	$url = 'https://sit.api.sf-express-us.com/api/orderservice/submitorder';
	//$url = 'https://usshipapi.sf-express-us.com/api/orderservice/submitorder';		
	$userData = Yii::$app->user->identity;
	$users_Id = Yii::$app->user->identity->id;
	
	$not_work = '';
	if (!isset($userData->first_name) || $userData->first_name == '' || $userData->first_name == 'Empty') {
	    $not_work = "First name is required.";
	} elseif (!isset($userData->general_company) || $userData->general_company == '' || $userData->general_company == 'Empty') {
	    $not_work = "Company name is required.";
	} elseif (!isset($userData->corporate_add_street1) || $userData->corporate_add_street1 == '' || $userData->corporate_add_street1 == 'Empty') {
	    $not_work = "Address is required.";
	} elseif (!isset($userData->corporate_add_state) || $userData->corporate_add_state == '' || $userData->corporate_add_state == 'Empty') {
	    $not_work = "State is required.";
	} elseif (!isset($userData->corporate_add_city) || $userData->corporate_add_city == '' || $userData->corporate_add_city == 'Empty') {
	    $not_work = "City is required.";
	} elseif (!isset($userData->corporate_add_country) || $userData->corporate_add_country == '' || $userData->corporate_add_country == 'Empty') {
	    $not_work = "Country is required.";
	} elseif (!isset($userData->corporate_add_zipcode) || $userData->corporate_add_zipcode == '' || $userData->corporate_add_zipcode == 'Empty') {
	    $not_work = "Zipcode is required.";
	} elseif (!isset($userData->general_phone_number) || $userData->general_phone_number == '' || $userData->general_phone_number == 'Empty') {
	    $not_work = "Phone number is required.";
	}
	if ($not_work != '') {
	    $Channelsetting_delete = Channelsetting::find()->Where(['user_id' => $users_Id, 'channel_id' => $storeid, 'setting_key' => 'fulfillmentID'])->all();
	    if (!empty($Channelsetting_delete)) {
		foreach ($Channelsetting_delete as $Cs_delete) {
		    $Cs_delete->delete();
		}
		die($not_work);
	    }
	}
	for ($i = 0; $i < 1; $i++) {
	    $sf = $sf_orders_data[0];
	    $order_id = $sf['order_id'];
	    $sf_orders_data_all = Orders::find()->Where(['order_ID' => $order_id])->with('customer')->with('ordersProducts')->one();
	    $userData = Yii::$app->user->identity;
	    //echo '<pre>';print_r($userData); die;
	    $CneeContactName = $userData->first_name;
	    $CneeCompany = $userData->general_company;
	    $CneeAddress = $userData->corporate_add_street1;
	    $CneeCity = $userData->corporate_add_city;
	    $CneeProvince = $userData->billing_address_state;
	    $CneeCountry = $userData->corporate_add_country;
	    $CneePostCode = $userData->corporate_add_zipcode;
	    $CneePhone = $userData->general_phone_number;
	    // echo '<pre>'; print_r($sf_orders_data_all); echo '</pre>'; die('sdfa');
	    $product_data = $sf_orders_data_all->ordersProducts;
	    $customer_data = $sf_orders_data_all->customer;
	    $orderIdForSF = $sf_orders_data_all->order_ID . '-' . $sf_orders_data_all->customer_id;
	    $orderJsonArrayData['CneeContactName'] = $CneeContactName;
	    $orderJsonArrayData['CneeCompany'] = $CneeCompany;
	    $orderJsonArrayData['CneeAddress'] = $CneeAddress;
	    $orderJsonArrayData['CneeCity'] = $CneeCity;
	    // $orderJsonArrayData['CneeProvince'] = $customer_data->state;
	    $orderJsonArrayData['CneeProvince'] = $CneeProvince;
	    //$orderJsonArrayData['CneeCountry'] = $customer_data->country;
	    $orderJsonArrayData['CneeCountry'] = $CneeCountry;
	    $orderJsonArrayData['CneePostCode'] = $CneePostCode;
	    $orderJsonArrayData['CneePhone'] = $CneePhone;
	    $orderJsonArrayData['ReferenceNo1'] = $orderIdForSF;
	    $orderJsonArrayData['ReferenceNo2'] = 'IZIEF4342324324D23434';
	    $orderJsonArrayData['ExpressType'] = '101';
	    $orderJsonArrayData['ParcelQuantity'] = '1';
	    $orderJsonArrayData['PayMethod'] = '3';
	    $orderJsonArrayData['TaxPayType'] = '2';
	    $orderJsonArrayData['Currency'] = 'USD';

	    $i = 0;
	    foreach ($product_data as $prodata):
		$id = $prodata->product_Id;
		$productData = Products::find()->Where(['id' => $id])->one();
		if (!empty($productData->product_name)) {
		    $orderJsonArrayData['Items'][$i]['Name'] = $productData->product_name;
		} else {
		    $orderJsonArrayData['Items'][$i]['Name'] = 'Testing';
		}
		if (!empty($productData->stock_quantity)) {
		    $orderJsonArrayData['Items'][$i]['Count'] = str_replace(',', '', number_format($productData->stock_quantity, 0));
		} else {
		    $orderJsonArrayData['Items'][$i]['Count'] = '1';
		}
		$orderJsonArrayData['Items'][$i]['Unit'] = 'pcs';
		if (!empty($productData->price)) {
		    $orderJsonArrayData['Items'][$i]['Amount'] = $productData->price;
		} else {
		    $orderJsonArrayData['Items'][$i]['Amount'] = '0';
		}
		$orderJsonArrayData['Items'][$i]['SourceArea'] = 'US';
		if (!empty($productData->weight)) {
		    $orderJsonArrayData['Items'][$i]['Weight'] = $productData->weight;
		} else {
		    $orderJsonArrayData['Items'][$i]['Weight'] = '1.0';
		}

		$i++;
	    endforeach;
	   
	    $KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds');
	    //$KEYJsonArrayData = array('UserName' => '90000006', 'Password' => '=!p96PytJkBxJQUg',);
	 
	    $order_data_json = array(
		'Order' => $orderJsonArrayData,
		'Gateway' => 'JFK',
		'NetworkCredential' => $KEYJsonArrayData,
	    );

	    $data_string = json_encode($order_data_json);
	    $curl = curl_init();

	    curl_setopt_array($curl, array(
		CURLOPT_SSL_VERIFYPEER => false,
		//CURLOPT_URL => "https://usshipapi.sf-express-us.com/api/orderservice/submitorder",
		CURLOPT_URL => "https://sit.api.sf-express-us.com/api/orderservice/submitorder",
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
		    "content-type: application/json"
		),
	    ));

	    $result = curl_exec($curl);
	    $err = curl_error($curl);


	    $result_decode = json_decode($result);

	    if (!$result_decode->Success || $result_decode->Success == '') {
		$message = $result_decode->Data->Message;
		if ($message == "Order has been confirmed,can't be modified.") {
		    die('Already fulfiled');
		} else {
		    $Channelsetting_delete = Channelsetting::find()->Where(['user_id' => $users_Id, 'channel_id' => 1, 'setting_key' => 'fulfillmentID'])->all();
		    if (!empty($Channelsetting_delete)) {
			foreach ($Channelsetting_delete as $Cs_delete) {
			    $Cs_delete->delete();
			   
			}
		    }
		    die($message);
		}
	    }
	}
	echo 'success';

    }

    public function actionSearchsforder() {
	$order_id = $_GET['order_id'];
	$OrderSearch = array('OrderId' => $order_id);
	$KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds');
	//$KEYJsonArrayData = array('UserName' => '90000006', 'Password' => '!p96PytJkBxJQUg');

	$order_data_json = array(
	    'OrderSearch' => $OrderSearch,
	    'NetworkCredential' => $KEYJsonArrayData,
	);

	$data_string = json_encode($order_data_json);
	echo $data_string;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => "https://sit.api.sf-express-us.com/api/orderservice/searchorder",
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
		"content-type: application/json"
	    ),
	));

	$result = curl_exec($curl);
	$err = curl_error($curl);
	echo '<pre>';
	print_r($err);
	echo '</pre>';
	echo '<pre>';
	print_r(json_decode($result));
	echo '</pre>';
	die('dsfa');
    }

    public function actionSfordercancel() {
	$SfWaybillNo = $_GET['sf_waybill_no'];
	$CancelType = $_GET['cancel_type'];
	$OrderParam = array('SfWaybillNo' => $SfWaybillNo, 'CancelType' => $CancelType);
	$KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds');
	//$KEYJsonArrayData = array('UserName' => '90000006', 'Password' => '!p96PytJkBxJQUg');
	$order_data_json = array(
	    'OrderParam' => $OrderParam,
	    'NetworkCredential' => $KEYJsonArrayData,
	);

	$data_string = json_encode($order_data_json);
	echo $data_string;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => "https://sit.api.sf-express-us.com/api/orderservice/searchorder",
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
		"content-type: application/json"
	    ),
	));

	$result = curl_exec($curl);
	$err = curl_error($curl);
	echo '<pre>';
	print_r($err);
	echo '</pre>';
	echo '<pre>';
	print_r(json_decode($result));
	echo '</pre>';
	die('dsfa');
    }

    public function actionSfprintorder() {
	$SfWaybillNo = $_GET['sf_waybill_no'];
	$PrintType = $_GET['print_type'];
	$OrderPrint = array('SfWaybillNo' => $SfWaybillNo, 'PrintType' => $PrintType);
	$KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds');
	$order_data_json = array(
	    'OrderPrint' => $OrderPrint,
	    'NetworkCredential' => $KEYJsonArrayData,
	);
	$data_string = json_encode($order_data_json);
	echo $data_string;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => "https://sit.api.sf-express-us.com/api/orderservice/printorder",
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
		"content-type: application/json"
	    ),
	));

	$result = curl_exec($curl);
	$err = curl_error($curl);
	echo '<pre>';
	print_r($err);
	echo '</pre>';
	echo '<pre>';
	print_r(json_decode($result));
	echo '</pre>';
	die('dsfa');
    }

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
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-product-create?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-product-delete?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-product-update?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-order-create?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-order-paid?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-order-update?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-customer-create?id=" . $user_id, $_hook)) {
			    $sc->call('DELETE', '/admin/webhooks/' . $_hook['id'] . '.json');
			}
			if (in_array(Yii::$app->params['BASE_URL'] . "customer-user/shopify-customer-update?id=" . $user_id, $_hook)) {
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

    public function actionTranslation() {

	/* $upload_docs_dir = Yii::getAlias('@upload_files');
	  echo $upload_docs_dir;
	  die; */

	//require_once('/var/www/html/smartling-api/vendor/composer/autoload_real.php'); 
	//$user_id = Yii::$app->user->identity->id;
	$ds = DIRECTORY_SEPARATOR;
	$basedir = Yii::getAlias('@basedir');


	$translateFile = $_FILES['smart-2'];
	$translate_filename = $translateFile['name'];
	$upload_docs_dir = Yii::getAlias('@upload_files');



	/* Starts buisness_file Document Upload */
	$buisness_tempFile = $translateFile['tmp_name'];
	$buisness_file_name = $translateFile['name'];
	$buisness_final_name = uniqid() . '_' . $buisness_file_name;
	$buisness_targetFile = $upload_docs_dir . $ds . $buisness_final_name;
	$buisness_file_upload = move_uploaded_file($buisness_tempFile, $buisness_targetFile);
	$filePath = $upload_docs_dir . '/' . $buisness_final_name;




	$options = [
	    'project-id' => '7289bfb87',
	    'user-id' => 'bhabaskqrnadgqgarxhuhawydiicml',
	    'secret-key' => '2anhc34el124ngi7pd46epc21kVM_ui3vgsmu1usvlshgfnva3viu6j',
	];


	if (
		!array_key_exists('project-id', $options) || !array_key_exists('user-id', $options) || !array_key_exists('secret-key', $options)
	) {
	    echo 'Missing required params.' . PHP_EOL;
	    exit;
	}

	$autoloader = $basedir . '/smartling-api/vendor/autoload.php';

	if (!file_exists($autoloader) || !is_readable($autoloader)) {
	    echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
	    exit;
	} else {
	    require_once $basedir . '/smartling-api/vendor/autoload.php';
	}



	$projectId = $options['project-id'];
	$userIdentifier = $options['user-id'];
	$userSecretKey = $options['secret-key'];

	$fileName = $translate_filename;
	$fileUri = $filePath;
	$fileRealPath = realpath($fileUri);
	$fileType = 'xml';
	$newFileName = 'new_test_file.xml';
	$retrievalType = 'published';
	$content = file_get_contents(realpath($fileUri));
	$fileContentUri = $translate_filename;
	$translationState = 'PUBLISHED';
	$locale = 'zh-CN';
	$locales_array = [$locale];

	$this->resetFiles($userIdentifier, $userSecretKey, $projectId, [$fileName, $newFileName]);


	/**
	 * Upload file example
	 */
	try {
	    echo '::: File Upload Example :::' . PHP_EOL;

	    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
	    $uploadParams = new \Smartling\File\Params\UploadFileParameters();
	    $uploadParams->setCallbackUrl('https://s86.co/channelsetting/callback/');
	    $result = $fileApi->uploadFile($fileRealPath, $fileName, $fileType, $uploadParams);
	    echo 'File upload result:' . PHP_EOL;
	    echo var_export($result, true) . PHP_EOL . PHP_EOL;
	} catch (\Smartling\Exceptions\SmartlingApiException $e) {
	    $messageTemplate = 'Error happened while uploading file.' . PHP_EOL
		    . 'Response code: %s' . PHP_EOL
		    . 'Response message: %s' . PHP_EOL;

	    echo vsprintf(
		    $messageTemplate, [
		$e->getCode(),
		$e->getMessage(),
		    ]
	    );
	}


	/**
	 * Download file example
	 */
	try {
	    echo '::: File Download Example :::' . PHP_EOL;

	    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	    $params = new \Smartling\File\Params\DownloadFileParameters();
	    $params->setRetrievalType($retrievalType);

	    $result = $fileApi->downloadFile('test.xml', 'zh-CN', $params);

	    echo 'File download result:' . PHP_EOL;
	    echo var_export($result, true) . PHP_EOL . PHP_EOL;
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
	/**
	 * Getting file status example
	 */
	/*  try {
	  echo '::: Get File Status Example :::' . PHP_EOL;

	  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	  $result = $fileApi->getStatus($fileName, $locale);

	  echo 'Get File Status result:' . PHP_EOL;
	  echo var_export($result, true) . PHP_EOL . PHP_EOL;
	  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
	  $messageTemplate = 'Error happened while getting file status.' . PHP_EOL
	  . 'Response code: %s' . PHP_EOL
	  . 'Response message: %s' . PHP_EOL;

	  echo vsprintf(
	  $messageTemplate, [
	  $e->getCode(),
	  $e->getMessage(),
	  ]
	  );
	  } */

	/**
	 * Getting Authorized locales for file
	 */
	/* try {
	  echo '::: Get File Authorized Locales Example :::' . PHP_EOL;

	  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	  $result = $fileApi->getAuthorizedLocales($fileName);

	  echo 'Get File Authorized Locales result:' . PHP_EOL;
	  echo var_export($result, true) . PHP_EOL . PHP_EOL;
	  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
	  $messageTemplate = 'Error happened while getting file authorized locales.' . PHP_EOL
	  . 'Response code: %s' . PHP_EOL
	  . 'Response message: %s' . PHP_EOL;

	  echo vsprintf(
	  $messageTemplate, [
	  $e->getCode(),
	  $e->getMessage(),
	  ]
	  );
	  } */

	/**
	 * Listing Files
	 */
	/*  try {
	  echo '::: List Files Example :::' . PHP_EOL;

	  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	  $params = new \Smartling\File\Params\ListFilesParameters();
	  $params
	  ->setFileTypes($fileType)
	  ->setUriMask('test')
	  ->setLimit(5);

	  $result = $fileApi->getList($params);

	  echo 'List Files result:' . PHP_EOL;
	  echo var_export($result, true) . PHP_EOL . PHP_EOL;
	  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
	  $messageTemplate = 'Error happened while getting file list.' . PHP_EOL
	  . 'Response code: %s' . PHP_EOL
	  . 'Response message: %s' . PHP_EOL;

	  echo vsprintf(
	  $messageTemplate, [
	  $e->getCode(),
	  $e->getMessage(),
	  ]
	  );
	  } */

	/**
	 * Importing Files
	 */
	/*  try {
	  echo '::: File Import Example :::' . PHP_EOL;

	  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	  $result = $fileApi->import($locale, $fileName, $fileType, $fileRealPath, $translationState, true);

	  echo 'File Import result:' . PHP_EOL;
	  echo var_export($result, true) . PHP_EOL . PHP_EOL;
	  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
	  $messageTemplate = 'Error happened while importing file.' . PHP_EOL
	  . 'Response code: %s' . PHP_EOL
	  . 'Response message: %s' . PHP_EOL;

	  echo vsprintf(
	  $messageTemplate, [
	  $e->getCode(),
	  $e->getMessage(),
	  ]
	  );
	  } */

	/**
	 * Renaming Files
	 */
	/* try {
	  echo '::: Rename File Example :::' . PHP_EOL;

	  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

	  $result = $fileApi->renameFile($fileName, $newFileName);

	  echo 'Rename File result:' . PHP_EOL;
	  echo var_export($result, true) . PHP_EOL . PHP_EOL;
	  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
	  $messageTemplate = 'Error happened while renaming files.' . PHP_EOL
	  . 'Response code: %s' . PHP_EOL
	  . 'Response message: %s' . PHP_EOL;

	  echo vsprintf(
	  $messageTemplate, [
	  $e->getCode(),
	  $e->getMessage(),
	  ]
	  );
	  } */
    }

    function resetFiles($userIdentifier, $userSecretKey, $projectId, $files = []) {
	$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

	foreach ($files as $file) {
	    try {
		$fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
		$fileApi->deleteFile($file);
	    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
		
	    }
	}
    }

    public function actionCallback() {

	$user = User::find()->where(['id' => 1453])->one();
	$user_domain = $user->domain_name;


	$locale = $_GET['locale'];
	$fileUri = $_GET['fileUri'];
	echo $locale . '--' . $fileUri;
	$OrderFullfillmentAlready = OrderFullfillment::find()->Where(['id' => '529'])->one();

	$OrderFullfillmentAlready->data = $locale . '--' . $fileUri;
	if ($OrderFullfillmentAlready->save(false)) {
	    die('save');
	} else {
	    echo'<pre>';
	    print_r($OrderFullfillmentAlready->getErrors());
	}
	die('end here');
	// }
    }

    public function actionTranslationchannelsetting() {
	//echo '<pre>'; print_r($_POST); echo '</per>';

	$channelID = $_POST['channel_id'];
	$enable = $_POST['enable'];
	$catenable = $_POST['catenable'];
	$language = $_POST['language'];
	$users_Id = Yii::$app->user->identity->id;
	$arrayData = array($enable, $catenable);
	$store_connection_data = StoresConnection::find()->Where(['store_id' => $channelID])->one();
	$iduse = $store_connection_data->stores_connection_id;
	$store_connection = Stores::find()->where(['store_id' => $iduse])->one();
	if (!empty($store_connection)) {
	    $namestore = $store_connection->store_name;
	} else {
	    $Channels = Channels::find()->where(['channel_id' => $channelID])->one();
	    $name = $Channels->parent_name;
	    if ($name == 'channel_WeChat') {
		$namestore = 'WeChat';
	    }
	}
	$domain_name = Yii::$app->user->identity->domain_name;
	$account_name = $domain_name . '+' . $users_Id . 'Elliot-A';
	$SmartlingAccount = Smartling::find()->where(['elliot_user_id' => $users_Id, 'account_name' => $account_name])->one();
	if (empty($SmartlingAccount)) {
	    $Smartling = new Smartling();

	    $account_name = $domain_name . '+' . $users_Id . 'Elliot-A';
	    $data_string = '{
								"accountName": "' . $account_name . '",
								"productUid": "cbd55c56a878"
							}';
	    $curl = curl_init();

	    curl_setopt_array($curl, array(
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_URL => "https://api.smartling.com/accounts-api/v2/affiliated-accounts",
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

	    $result = curl_exec($curl);
	    //echo '<pre>'; print_r(json_decode($result)); die();
	    $data = json_decode($result);
	    $accountUid = $data->response->data->accountUid;
	    $userIdentifier = $data->response->data->apiCredentials->userIdentifier;
	    $userSecret = $data->response->data->apiCredentials->userSecret;
	    $Smartling->elliot_user_id = $users_Id;
	    $Smartling->sm_user_id = $userIdentifier;
	    $Smartling->token = $namestore;
	    $Smartling->account_id = $accountUid;
	    $Smartling->account_name = $account_name;
	    $Smartling->secret_key = $userSecret;
	    $Smartling->save();

	    // $err = curl_error($curl);

	    /* StartProject Added */

	    $SmartlingProject = Smartling::find()->where(['elliot_user_id' => $users_Id])->andWhere(['<>', 'token', $namestore])->one();
	    if (empty($SmartlingProject)) {
		$Smartling = new Smartling();
		$Smartling->elliot_user_id = $users_Id;
		$Smartling->sm_user_id = $userIdentifier;
		$Smartling->token = $namestore;
		$Smartling->account_id = $accountUid;
		$Smartling->account_name = $account_name;
		$Smartling->secret_key = $userSecret;
		$accountUid = $SmartlingAccount->account_id;

		/* Account Athentication */
		$data_string = '{
						  "userIdentifier": "' . $SmartlingAccount->sm_user_id . '",
						  "userSecret": "' . $SmartlingAccount->secret_key . '"
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

		//echo '<pre>'; print_r(json_decode($resultAuth)); die();
		$authData = json_decode($resultAuth);

		$access_token = $authData->response->data->accessToken;

		/* Account Athentication */





		$domain_name = Yii::$app->user->identity->domain_name;
		$Project_name = $domain_name . '+' . $users_Id . 'Elliot-P';
		$data_string = '{
						  "projectName": "' . $Project_name . '",
						  "projectTypeCode": "APPLICATION_RESOURCES",
						  "sourceLocaleId": "en",
						  "targetLocaleIds": ["' . $language . '"]
						}';
		$curl = curl_init();
		$url = 'https://api.smartling.com/accounts-api/v2/accounts/' . $accountUid . '/projects';
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
			"Authorization: Bearer " . $access_token,
			"content-type: application/json"
		    ),
		));

		$result = curl_exec($curl);
		//echo '<pre>'; print_r(json_decode($result)); die();
		$data = json_decode($result);
		$projectId = $data->response->data->projectId;
		$projectTypeCode = $data->response->data->projectTypeCode;
		$projectTypeDisplayValue = $data->response->data->projectTypeDisplayValue;
		$targetLocales = $data->response->data->targetLocales;

		//$Smartling->elliot_user_id = $users_Id;
		$Smartling->project_name = $Project_name;
		$Smartling->project_id = $projectId;
		$Smartling->project_type = $projectTypeCode;
		$Smartling->project_type_value = $projectTypeDisplayValue;
		//$Smartling->target_locale = $targetLocales;
		/* 	$Smartling->secret_key = $userSecret; */
		echo '<pre>';
		print_r($Smartling);
		echo '</pre>';
		$Smartling->save();
	    }

	    /* StartProject Added */
	} else {
	    $SmartlingProject = Smartling::find()->where(['elliot_user_id' => $users_Id])->andWhere(['<>', 'token', $namestore])->one();
	    if (empty($SmartlingProject)) {
		$Smartling = new Smartling();
		$Smartling->elliot_user_id = $users_Id;
		$Smartling->sm_user_id = $SmartlingAccount->sm_user_id;
		$Smartling->token = $namestore;
		$Smartling->account_id = $SmartlingAccount->account_id;
		$Smartling->account_name = $SmartlingAccount->account_name;
		$Smartling->secret_key = $SmartlingAccount->secret_key;
		$accountUid = $SmartlingAccount->account_id;

		/* Account Athentication */
		$data_string = '{
						  "userIdentifier": "' . $SmartlingAccount->sm_user_id . '",
						  "userSecret": "' . $SmartlingAccount->secret_key . '"
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

		//echo '<pre>'; print_r(json_decode($resultAuth)); die();
		$authData = json_decode($resultAuth);

		$access_token = $authData->response->data->accessToken;

		/* Account Athentication */





		$domain_name = Yii::$app->user->identity->domain_name;
		$Project_name = $domain_name . '+' . $users_Id . 'Elliot-P';
		$data_string = '{
						  "projectName": "' . $Project_name . '",
						  "projectTypeCode": "APPLICATION_RESOURCES",
						  "sourceLocaleId": "en",
						  "targetLocaleIds": ["' . $language . '"]
						}';
		$curl = curl_init();
		$url = 'https://api.smartling.com/accounts-api/v2/accounts/' . $accountUid . '/projects';
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
			"Authorization: Bearer " . $access_token,
			"content-type: application/json"
		    ),
		));

		$result = curl_exec($curl);
		//echo '<pre>'; print_r(json_decode($result)); die();
		$data = json_decode($result);
		$projectId = $data->response->data->projectId;
		$projectTypeCode = $data->response->data->projectTypeCode;
		$projectTypeDisplayValue = $data->response->data->projectTypeDisplayValue;
		$targetLocales = $data->response->data->targetLocales;

		//$Smartling->elliot_user_id = $users_Id;
		$Smartling->project_name = $Project_name;
		$Smartling->project_id = $projectId;
		$Smartling->project_type = $projectTypeCode;
		$Smartling->project_type_value = $projectTypeDisplayValue;
		//$Smartling->target_locale = $targetLocales;
		/* 	$Smartling->secret_key = $userSecret; */
		echo '<pre>';
		print_r($Smartling);
		echo '</pre>';
		$Smartling->save();
	    }
	    //echo $namestore;
	}
	$check_abbrivation = ProductAbbrivation::find()->where(['channel_accquired' => $namestore])->all();
	//echo '<pre>'; print_r($check_abbrivation); die();
	$i = 0;
	$user_id = $users_Id;
	$productArray = array();
	foreach ($check_abbrivation as $data) {

	    $id = $data['id'];
	    $productData = Products::find()->where(['id' => $id])->one();
	    //	echo '<pre>'; print_r($productData); echo '</pre>'; 
	    $id = $id;
	    $name = @$productData->product_name;
	    $description = @$productData->description;
	    $condition = @$productData->condition;
	    $gender = @$productData->gender;
	    $stock_level = @$productData->stock_level;
	    $product_status = @$productData->product_status;
	    $productArray[$i]['id'] = $id;
	    $productArray[$i]['name'] = $name;
	    $productArray[$i]['description'] = $description;
	    $productArray[$i]['condition'] = $condition;
	    $productArray[$i]['gender'] = $gender;
	    $productArray[$i]['stock_level'] = $stock_level;
	    $productArray[$i]['product_status'] = $product_status;
	    $i++;
	}
	//echo '<pre>'; print_r($productArray); echo '</pre>';
	$jsonProductData = json_encode($productArray);
	$wordcount = str_word_count($jsonProductData);
	echo $fileName = $namestore . '--id--' . $user_id . '.json';
	$pathfile = '/var/www/html/img/uploaded_files/' . $fileName;
	$myfile = fopen($pathfile, "w");
	if (file_exists($pathfile)) {
	    fwrite($myfile, $jsonProductData);
	} else {
	    fwrite($pathfile, $jsonProductData);
	}

	for ($i = 0; $i < 4; $i++) {
	    if ($i == 0) {
		$setting_key = 'Enable_Smartling';
		$fulfillmentID = $enable;
	    } else if ($i == 1) {
		$setting_key = 'Enable_Categories';
		$fulfillmentID = $catenable;
	    } else if ($i == 2) {

		$setting_key = 'Price';
		$fulfillmentID = $wordcount;
	    } else {
		$setting_key = 'Language';
		$fulfillmentID = $language;
	    }
	    $channelsetting = Channelsetting::find()->where(['channel_id' => $channelID, 'user_id' => $users_Id, 'setting_key' => $setting_key])->one();
	    if (!empty($channelsetting)) {
		$Channelsetting = $channelsetting;
	    } else {
		$Channelsetting = new Channelsetting();
	    }

	    $Channelsetting->channel_id = $channelID;
	    $Channelsetting->channel_name = $namestore;
	    $Channelsetting->setting_key = $setting_key;
	    $Channelsetting->user_id = $users_Id;
	    $Channelsetting->setting_value = $fulfillmentID;
	    $Channelsetting->created_at = date('Y-m-d H:i:s');
	    if ($Channelsetting->save(false)) {
		echo 'success';
	    } else {
		echo 'error';
	    }
	}
    }

    public function actionJobsetting() {
	echo '<pre>';
	print_r($_POST);
	echo '</pre>';
    }

}
