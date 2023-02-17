<?php

namespace frontend\controllers;

use common\commands\SendEmailCommand;
use common\models\Attribution;
use common\models\ChannelConnection;
use common\models\Channels;
use common\models\Connection;
use common\models\ConnectionAttribution;
use common\models\Fulfillment;
use common\models\FulfillmentList;
use common\models\Mapping;
use common\models\Notification;
use common\models\Order;
use common\models\Product;
use common\models\User;
use common\models\StoresConnection;
use common\models\ProductImages;
use common\models\SmartlingPrice;
use common\models\Categories;
use common\models\Products;
use common\models\Variations;
use common\models\ProductAbbrivation;
use common\models\VariationsItemList;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\CustomerUser;
use common\models\ProductCategories;
use common\models\ProductChannel;
use common\models\Orders;
use common\models\Smartling;
use common\models\MerchantProducts;
use common\models\Channelsetting;
use common\models\GoogleProductCategories;
use common\models\Stores;
use common\models\OrderFullfillment;
use common\models\UserConnection;
use frontend\components\ConsoleRunner;
use yii\filters\AccessControl;
use Smartling\AuthApi;
use common\components\ElliShopifyClient as Shopify;
use yii\filters\VerbFilter;
use Yii;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\FileApi;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use common\models\StoreDetails;
use common\models\CategoryAbbrivation;
use common\models\CustomerAbbrivation;
use frontend\controllers\FulfillmentController;
use frontend\controllers\OrdersController;

class ChannelsettingController extends \yii\web\Controller {

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

        $user_id = Yii::$app->user->identity->id;
        $user_connection_id = '';
        $id = '';
        $type = '';
        $refresh = 0;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
        }
        if (isset($_GET['u'])) {
            $user_connection_id = $_GET['u'];
        }
        if (isset($_GET['refresh'])) {
            $refresh = $_GET['refresh'];
        }
        $user_connection_model = UserConnection::find()->where(['id' => $user_connection_id])->one();
        $connection_id = $user_connection_model->connection_id;

        $elliot_ids = array();
        $store_ids = array();

        $mapping_attrs = Mapping::find()->where(['user_id' => $user_id, 'connection_id' => $connection_id])->all();
        foreach ($mapping_attrs as $mapping_attr) {
            $elliot_ids[] = $mapping_attr->elliot_id;
            $store_ids[] = $mapping_attr->store_id;
        }

        $mapped_elliot_attrs = Attribution::find()
            ->andWhere(['user_id' => $user_id])->orWhere(['default' => '1'])
            ->andWhere(['and', ['in', 'id', $elliot_ids]])->all();
        $mapped_store_attrs = ConnectionAttribution::find()
            ->andWhere(['connection_id' => $connection_id])
            ->andWhere(['and', ['in', 'id', $store_ids]])->all();

        $elliot_attrs = Attribution::find()
            ->andWhere(['user_id' => $user_id])->orWhere(['default' => '1'])
            ->andWhere(['and', ['not in', 'id', $elliot_ids]])->all();
        $store_attrs = ConnectionAttribution::find()
            ->andWhere(['connection_id' => $connection_id])
            ->andWhere(['and', ['not in', 'id', $store_ids]])->all();

	    return $this->render('index',[
	        'id' => $id,
	        'type' => $type,
	        'refresh' => $refresh,
	        'user_connection_id' => $user_connection_id,
            'elliot_attrs' => $elliot_attrs,
            'store_attrs' => $store_attrs,
            'mapped_elliot_attrs' => $mapped_elliot_attrs,
            'mapped_store_attrs' => $mapped_store_attrs,
        ]);
    }

    public function actionTracking() {
        $hawbs = base64_decode($_GET['hawbs']);
        if (!empty($hawbs)) {
            $KEYJsonArrayData = array('UserName' => '90000001', 'Password' => 'b9rVZekQqY2bv6ds',);
            $order_data_json = array(
            'Routes' => array(array('SfWaybillNo' => '070033836247', 'TrackingType' => '1',),),
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
            $err = curl_error($curl);
            echo '<pre>';
            print_r(json_decode($result));
            echo '</pre>';
            die('dsfa');
            return $this->render('track');
        } else {
            return $this->render('index');
        }
    }

    public function actionFulfill_save() {
        $fulfillListID		= $_POST['fulfillListID'];
        $user_connection_id		= $_POST['user_connection_id'];
        $users_Id		= Yii::$app->user->identity->id;
        $user_connection = UserConnection::find()->Where(['id' => $user_connection_id])->one();
        if(empty($user_connection)){
            echo "error";
        }
        else{
            $user_connection->fulfillment_list_id = $fulfillListID;
            $user_connection->save(true, ['fulfillment_list_id']);
            echo 'success';
        }

    }

    public function actionMappingEnable() {

        $enable = $_POST['enable'];
        $user_connection_id = $_POST['user_connection_id'];
        $user_connection = UserConnection::find()->Where(['id' => $user_connection_id])->one();
        if (!empty($user_connection)) {
            if ($enable == 'yes') {
                $user_connection->mapping_status = 1;
            } else {
                $user_connection->mapping_status = 0;
            }
            $user_connection->save(true, ['mapping_status']);
        }
        if ($enable == 'no') {
            Mapping::deleteAll(['connection_id' => $user_connection->connection_id]);
        }

        echo "success";
    }

    public function actionSforders() {
        $user_connection_id = Yii::$app->request->post('user_connection_id');
        $userData = Yii::$app->user->identity;

        $not_work = '';
        if (!isset($userData->userProfile->firstname) || $userData->userProfile->firstname == '' || $userData->userProfile->firstname == 'Empty') {
            $not_work = "First name is required.";
        } elseif (!isset($userData->company) || $userData->company == '' || $userData->company == 'Empty') {
            $not_work = "Company name is required.";
        } elseif (!isset($userData->userProfile->corporate_addr_street1) || $userData->userProfile->corporate_addr_street1 == '' || $userData->userProfile->corporate_addr_street1 == 'Empty') {
            $not_work = "Address is required.";
        } elseif (!isset($userData->userProfile->corporate_addr_state) || $userData->userProfile->corporate_addr_state == '' || $userData->userProfile->corporate_addr_state == 'Empty') {
            $not_work = "State is required.";
        } elseif (!isset($userData->userProfile->corporate_addr_city) || $userData->userProfile->corporate_addr_city == '' || $userData->userProfile->corporate_addr_city == 'Empty') {
            $not_work = "City is required.";
        } elseif (!isset($userData->userProfile->corporate_addr_country) || $userData->userProfile->corporate_addr_country == '' || $userData->userProfile->corporate_addr_country == 'Empty') {
            $not_work = "Country is required.";
        } elseif (!isset($userData->userProfile->corporate_addr_zipcode) || $userData->userProfile->corporate_addr_zipcode == '' || $userData->userProfile->corporate_addr_zipcode == 'Empty') {
            $not_work = "Zipcode is required.";
        } elseif (!isset($userData->userProfile->phoneno) || $userData->userProfile->phoneno == '' || $userData->userProfile->phoneno == 'Empty') {
            $not_work = "Phone number is required.";
        }

        if ($not_work != '') {
            die($not_work);
        }

        $workerSfexpressCr = new ConsoleRunner(['file' => '@console/yii']);
        $workerSfexpressCmd = 'fulfill-worker/sfexpress-send-order '.$user_connection_id;
        $workerSfexpressCr->run($workerSfexpressCmd);
        echo 'success';

    }

    public function actionChanneldisable() {
        $dt = Yii::$app->request->post();
        //echo "<pre>"; print_r($dt); die;
        $_SESSION['access_token'] = '';
        if (isset($dt['user_connection_id'])) {
            $currentUserId = Yii::$app->user->identity->id;
            $user_connection_id = $dt['user_connection_id'];
            UserConnection::disableUserConnction($user_connection_id);
        }
    }

    public function actionMapping() {
        $user_id = Yii::$app->user->identity->id;
        $elliot_id = $_POST['elliot_id'];
        $store_id = $_POST['store_id'];
        $user_connection_id = $_POST['user_connection_id'];
        $user_connection_model = UserConnection::find()->where(['id' => $user_connection_id])->one();

        $mapping_model = new Mapping();
        $mapping_model->user_id = $user_id;
        $mapping_model->elliot_id = $elliot_id;
        $mapping_model->store_id = $store_id;
        $mapping_model->connection_id = $user_connection_model->connection_id;

        if ($mapping_model->save(false)) {
            echo "success";
        }
        else {
            echo "error";
        }
    }

    public function actionMappingDelete() {
        $user_id = Yii::$app->user->identity->id;
        $elliot_id = $_POST['elliot_id'];
        $store_id = $_POST['store_id'];
        $user_connection_id = $_POST['user_connection_id'];

        Mapping::deleteAll(['user_id' => $user_id, 'elliot_id' => $elliot_id, 'store_id' => $store_id]);
        echo "success";
    }

    public function actionMappingFinish() {
        $user_connection_id = $_POST['user_connection_id'];
        $user_connection = UserConnection::find()->where(['id' => $user_connection_id])->one();
        if (!empty($user_connection)) {

            $connection_user = $user_connection->user;
            $user_email = $connection_user->email;
            $user_id = $connection_user->id;
            $channel_name = $user_connection->getPublicName();

            $email_message = 'Your product feed has successfully been updated and is now available for customers shopping' . $channel_name;
            $title = $channel_name .' Product Feed Update - Success';

            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Product Feed Update'),
                'view' => '@common/mail/template',
                'to' => $user_email,
                'params' => [
                    'title' => $title,
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Product Feed Update";

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = $title;
            $notification_model->save(false);

            echo 'success';
        }
        else {
            echo "error";
        }
    }
}
