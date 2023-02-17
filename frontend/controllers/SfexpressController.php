<?php

namespace frontend\controllers;

use common\commands\SendEmailCommand;
use common\models\ChannelConnection;
use common\models\Channels;
use common\models\Order;
use common\models\StoresConnection;
use Bigcommerce\Api\Connection;
use common\models\ProductImages;
use common\models\Fulfillment;
use common\models\UserConnection;
use frontend\components\SfexpressComponent;
use frontend\controllers\ChannelsController;
use common\models\Categories;
use common\models\Products;
use common\models\Variations;
use common\models\VariationsItemList;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\CustomerUser;
use common\models\Notification;
use common\models\ProductCategories;
use common\models\ProductVariation;
use common\models\ProductChannel;
use common\models\Orders;
use common\models\MerchantProducts;
use common\models\User;
use common\models\GoogleProductCategories;
use common\models\Stores;
use yii\filters\AccessControl;
use Yii;
use common\models\FulfillmentList;
use common\models\CustomFunction;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class SfexpressController extends \yii\web\Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','sforders'],
                'rules' => [
                        [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['logout','index','sforders'],
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
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex() {
        $user_id = Yii::$app->user->identity->id;
        $fulfillment = FulfillmentList::find()->where(['name' => 'SF Express'])->one();
        $connectionModel = Fulfillment::find()->where(['fulfillment_list_id' => $fulfillment->id, 'user_id' => $user_id])->one();
        return $this->render('index',
            ['connectionModel' => $connectionModel]);
    }

    public function actionAuth() {
        extract($_POST);
        if(!isset($sf_express_username) || !isset($sf_express_password)){
            return json_encode(array("success" => false, "msg" => "Unknown Request!", "data" => []));
        }
        else {
            $this->connectSFExpress($sf_express_username, $sf_express_password);
            return json_encode(array("success" => true, "msg" => "Success!", "data" => []));
        }
    }

	public function actionPrice() {
        return $this->render('price');
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
	
	public function actionRoute(){
		$data_string = '{
					"Routes":[{
					"SfWaybillNo":"070035721724",
					"TrackingType":"1"
					}],
						"NetworkCredential":
							{
							"UserName":"90000006",
							"Password":"=!p96PytJkBxJQUg"
							}
				}';
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
          //echo '<pre>'; print_r($ch); echo '</pre>';
          //echo '<pre>'; print_r($orderJsonArrayData); echo '</pre>';
          echo '<pre>'; print_r(json_decode($result)); echo '</pre>';// die('dsfa');
		  $orderData = json_decode($result);
	}
	
    public function actionSave() {
        //echo '<pre>'; print_r($_POST); echo '</pre>'; die('sdf');

        $user_id = Yii::$app->user->identity->id;
        $username = $_POST['username'];
        $password = $_POST['password'];
        $modelFulfillment = new Fulfillment();
        $modelFulfillment->user_id = $user_id;
        $modelFulfillment->name = 'SF Express';
        $modelFulfillment->fulfillment_link = '/sfexpress/sforders';
        $modelFulfillment->fulfillment_list_id = '5';
        $modelFulfillment->key_data = 'username';
        $modelFulfillment->value_data = $username;
        $modelFulfillment->created_at = date('Y-m-d H:i:s');
        $modelFulfillment->save();

        $modelFulfillment = new Fulfillment();
        $modelFulfillment->user_id = $user_id;
        $modelFulfillment->name = 'SF Express';
        $modelFulfillment->fulfillment_link = '/sfexpress/sforders';
        $modelFulfillment->fulfillment_list_id = '5';
        $modelFulfillment->key_data = 'password';
        $modelFulfillment->value_data = $password;
        $modelFulfillment->created_at = date('Y-m-d H:i:s');
        if ($modelFulfillment->save(false)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    public function actionDefault() {

        //echo '<pre>'; print_r($_POST); echo '</pre>'; die('sdf');
		$feedcheck = $_POST['feed'];
			if($feedcheck == 'yes'){
			$user_id = Yii::$app->user->identity->id;
			$username = '90000006';
			$password = '=!p96PytJkBxJQUg';
			$modelFulfillment = new Fulfillment();
			$modelFulfillment->elliot_user_id = $user_id;
			$modelFulfillment->name = 'SF Express';
			$modelFulfillment->fulfillment_link = '/sfexpress/sforders';
			$modelFulfillment->fulfillment_list_id = '5';
			$modelFulfillment->key_data = 'username';
			$modelFulfillment->value_data = $username;
			$modelFulfillment->created_at = date('Y-m-d H:i:s');
			$modelFulfillment->save();

			$modelFulfillment = new Fulfillment();
			$modelFulfillment->elliot_user_id = $user_id;
			$modelFulfillment->name = 'SF Express';
			$modelFulfillment->fulfillment_link = '/sfexpress/sforders';
			$modelFulfillment->fulfillment_list_id = '5';
			$modelFulfillment->key_data = 'password';
			$modelFulfillment->value_data = $password;
			$modelFulfillment->created_at = date('Y-m-d H:i:s');
			if ($modelFulfillment->save(false)) {
				//echo 'success';
				header('location: /sfexpress');
				exit;
				
			} else {
				echo 'error';
			}
		} else{
			$user_id = Yii::$app->user->identity->id;
			$Fulfillment_check = Fulfillment::find()->where(['name' => 'SF Express','elliot_user_id' => $user_id])->all();
			//echo '<per>'; print_r($Fulfillment_check); die();
			if (!empty($Fulfillment_check)) {
				foreach($Fulfillment_check as $data){
					$id = $data->id;
					$Fulfillment_check_del = Fulfillment::find()->where(['name' => 'SF Express','id' => $id])->one();
					$Fulfillment_check_del->delete();
				}
			}
			
		}
    }
	public function actionDeletesf() {
		$key = $_POST['keyid'];
		$user_id = Yii::$app->user->identity->id;
		$Fulfillment_check = Fulfillment::find()->where(['name' => 'SF Express','elliot_user_id' => $user_id])->all();
		//echo '<per>'; print_r($Fulfillment_check); die();
		if (!empty($Fulfillment_check)) {
			foreach($Fulfillment_check as $data){
				$id = $data->id;
				$Fulfillment_check_del = Fulfillment::find()->where(['name' => 'SF Express','id' => $id])->one();
				$Fulfillment_check_del->delete();
			}
		}
	}

    private function connectSFExpress($username, $password){
        $user_id = Yii::$app->user->identity->id;
        $fulfillment = FulfillmentList::find()->where(['name' => 'SF Express'])->one();
        $connectionModel = Fulfillment::find()->where(['fulfillment_list_id' => $fulfillment->id, 'user_id' => $user_id])->one();
        if (empty($connectionModel))
        {
            $connectionModel = new Fulfillment();
        }
        $connectionModel->fulfillment_list_id = $fulfillment->id;
        $connectionModel->name = $fulfillment->name;
        $connectionModel->user_id = $user_id;
        $connectionInfo = [
            'username' => $username,
            'password' => $password
        ];
        $connectionInfoJson = @json_encode($connectionInfo, JSON_UNESCAPED_UNICODE);
        $connectionModel->connection_info = $connectionInfoJson;
        $connectionModel->connected = 1;
        $connectionModel->fulfillment_link = $fulfillment->link;
        $connectionModel->save(false);

        $get_users = User::find()->select('domain, company, email')->where(['id' => $user_id])->one();
        $email = $get_users->email;
        $company_name = $get_users->company;
        $email_message = 'Success, Your SF Express is Now Connected';

        Yii::$app->commandBus->handle(new SendEmailCommand([
            'subject' => $company_name,
            'view' => '@common/mail/template',
            'from' => ['mail@helloiamelliot.com' => 'Elliot'],
            'to' => $email,
            'params' => [
                'title' => $company_name,
                'content' => $email_message,
                'server' => env('SEVER_URL')
            ]
        ]));

        $notify_type = 'SF Express';

        $notification_model = new Notification();
        $notification_model->user_id = $user_id;
        $notification_model->title = $notify_type;
        $notification_model->message = 'Your SF Express data has been successfully imported.';
        $notification_model->created_at = date('Y-m-d h:i:s', time());
        $notification_model->save(false);
    }

    public static function sendOrder($user_connection_id) {
        $user_connection = UserConnection::find()->where(['id' => $user_connection_id])->one();
        if (!empty($user_connection)) {
            $fulfillment = FulfillmentList::find()->where(['name' => 'SF Express'])->one();
            $connectionModel = Fulfillment::find()->where(['fulfillment_list_id' => $fulfillment->id, 'user_id' => $user_connection->user_id])->one();
            if (!empty($connectionModel)) {
                $sfexpress = new SfexpressComponent($connectionModel->connection_info['username'], $connectionModel->connection_info['password']);
                $sfexpress->sendOrder($user_connection_id);
            }
            return true;
        }
        return false;
    }
}
