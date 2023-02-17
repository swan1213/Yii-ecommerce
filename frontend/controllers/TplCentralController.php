<?php

namespace frontend\controllers;

ob_start();

//session_start();

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use frontend\components\TplComponent;
use common\commands\SendEmailCommand;
use common\models\Fulfillment;
use common\models\FulfillmentList;
use common\models\Notification;
use frontend\components\ConsoleRunner;

class TplCentralController extends Controller {
    public $authURL = 'https://secure-wms.com/AuthServer/api/Token';
    public $facilityID = 1;
    public $customerID = 105;

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'test'],
                'rules' => [
                    [
                        /* action hit without log-in */
                        'actions' => ['login', 'test'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        /* action hit only with log-in */
                        'actions' => ['index', 'test'],
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
     * Lists all CustomerUser models.
     * @return mixed
     */
    public function beforeAction($action) {
    

	    return parent::beforeAction($action);
    }
                    

    public function actionIndex(){            
        return $this->render('index');
    }

    public static function fullfill(){
        $tplModel = FulfillmentList::find()->select('id')->where(['name' => '3PL Central'])->one();
        $tpl_db_id = $tplModel->id;
        $tplUserConnections = Fulfillment::find()->where(['fulfillment_list_id' => $tpl_db_id])->all();
        foreach ($tplUserConnections as $tplConnection){
            $user_id = $tplConnection->user_id;
            $tpl_key = $tplConnection->connection_info['tpl_key'];
            $tpl_encoded = $tplConnection->connection_info['tpl_encoded'];
            $tplComponent = new TplComponent($tpl_key, $tpl_encoded, $user_id, $tpl_db_id);
            if($tplComponent){
                $tplComponent->sendOrders();
            }
        }
    }

    public function actionAuthTpl(){        
        extract($_POST);              
        if(!isset($tpl_client_id) || !isset($tpl_client_secret) || !isset($tpl_key) || !isset($tpl_encoded)){            
            return json_encode(array("success" => false, "msg" => "Unknown Request!", "data" => []));  
        }
        else{
            $data = array(
                "grant_type" => "client_credentials",
                "tpl" =>  $tpl_key,
                "user_login_id" => Yii::$app->user->identity->id
            );                                                                                                                
                                                                                                                                 
            $data_string = json_encode($data);
            $ch = curl_init('http://secure-wms.com/AuthServer/api/Token');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Length: ' . strlen($data_string),
                "Connection: keep-alive",
                "Content-Type: application/json; charset=utf-8",
                "Accept : application/json",
                "User-Agent : Fiddler",
                "Authorization : Basic " . $tpl_encoded,
                "Accept-Encoding : gzip,deflate,sdch",
                "Accept-Language : en-US,en;q=0.8")                                                                       
            );                          
            $response  = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);               
            if ($err) {
                return json_encode(array("success" => false, "msg" => "Network Error!", "data" => $err));
            } else {
                $response_data = json_decode($response);
                if(isset($response_data->access_token)){
                    $access_token = $response_data->access_token;
                    if(strlen($access_token)>0){
                        $this->connectFulfillment($tpl_key, $tpl_encoded);
                        $importCr = new ConsoleRunner(['file' => '@console/yii']);
                        $importCmd = 'fulfill-worker/tplcentral';
                        $importCr->run($importCmd);
                        return json_encode(array("success" => true, "msg" => "Success!", "data" => $response_data));
                    }else{
                        return json_encode(array("success" => false, "msg" => "Authorization Error!", "data" => []));                       
                    }
                }else{
                    return json_encode(array("success" => false, "msg" => "Invalidation Data!", "data" => []));             
                }             
            }
            exit();       
        }
    }
    
    private function connectFulfillment($tpl_key, $tpl_encoded){
        $user_id = Yii::$app->user->identity->id;
        $fulfillment = FulfillmentList::find()->where(['name' => '3PL Central'])->one();
        $checkConnection = Fulfillment::find()->where(['fulfillment_list_id' => $fulfillment->id, 'user_id' => $user_id])->one();
        if (empty($checkConnection))
        {
            $connectionModel = new Fulfillment();
            $connectionModel->fulfillment_list_id = $fulfillment->id;
            $connectionModel->name = $fulfillment->name;
            $connectionModel->user_id = $user_id;
            $connectionModel->connected = Fulfillment::CONNECTED_YES;
            $connectionModel->fulfillment_link = $fulfillment->link;
            $created_date = date('Y-m-d h:i:s', time());
            $connectionModel->created_at = $created_date;
            $connectionModel->updated_at = $created_date;
            $connectionModel->connection_info = json_encode(array("tpl_key"=>$tpl_key,"tpl_encoded"=>$tpl_encoded));
            $connectionModel->save();
        }

        $email = Yii::$app->user->identity->email;
        $email_message = 'Success, Your 3PL Central is Now Connected';
        Yii::$app->commandBus->handle(new SendEmailCommand([
            'subject' => Yii::t('common', '3PL Central'),
            'view' => '@common/mail/template',
            'to' => $email,
            'params' => [
                'title' => '3PL Central connected',
                'content' => $email_message,
                'server' => env('SEVER_URL')
            ]
        ]));

        //Drop-Down Notification for User
        $notif_type = "3PL Central";
        $notification_model = new Notification();
        $notification_model->user_id = $user_id;
        $notification_model->title = $notif_type;
        $notification_model->message = $email_message;
        $notification_model->created_at = date('Y-m-d h:i:s', time());
        $notification_model->save(false);
    }
}
