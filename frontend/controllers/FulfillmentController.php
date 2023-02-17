<?php

//

namespace frontend\controllers;

use common\models\Country;
use common\models\Crontask;
use common\models\Customer;
use common\models\FulfillmentList;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductImage;
use frontend\components\ConsoleRunner;
use frontend\components\ShipstationClient;
use frontend\models\ShiphawkConnectionForm;

use Yii;
use common\models\Fulfillment;
use frontend\models\search\FulfillmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * FulfillmentController implements the CRUD actions for Fulfillment model.
 */
class FulfillmentController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'update', 'shipstation', 'shiphero', 'carriers', 'software'],
                'rules' => [
                        [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['logout', 'index', 'view', 'create', 'update', 'shipstation', 'shiphero', 'auth-shipstation', 'carriers', 'software'],
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
     * Lists all Fulfillment models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new FulfillmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionShipstation() {

        $userId = Yii::$app->user->identity->id;
        $shipStation_Connection = Fulfillment::findOne(['user_id' => $userId, 'name' => 'ShipStation']);

        if ( !empty($shipStation_Connection) ) {

            return $this->render('goto_shipstation');

        }

        return $this->render('shipstation', [
            'userId' => $userId,
            'checkConnection' => $shipStation_Connection
        ]);
    }

    public function actionAuthShipstation() {

        $response = [
            'error' => 'Unknown Data!!!',
            'success' => false,
        ];
        $postData = Yii::$app->request->post();

        if ( empty($postData) ){
            return json_encode($response);
        }

        $shipstation_key = $postData['shipstation_key'];
        $shipstation_secret = $postData['shipstation_secret'];
        $user_id = $postData['user_id'];

        $shipStationClientInfo = [
            'apiKey' => $shipstation_key,
            'apiSecret' => $shipstation_secret,
        ];
        $shipStationClientInfoJson = @json_encode($shipStationClientInfo, JSON_UNESCAPED_UNICODE);

        $checkConnection = Fulfillment::findOne([
            'user_id' => $user_id,
            'connection_info' => $shipStationClientInfoJson,
            'name' => 'ShipStation'
        ]);

        if ( !empty($checkConnection) ) {
            $response['error'] = "This ShipStation Credential was already integrated with Elliot. Please use another API details to integrate with Elliot.";
            return json_encode($response);
        }

        $shipStationClient = new ShipstationClient($shipStationClientInfo);

        $shipstation_response = $shipStationClient->call('get', 'carriers');

        if ( $this->isJSON($shipstation_response) ) {

            if(isset($response['Message'])){

                $array_msg['error'] = $response['Message'];
                return json_encode($array_msg);

            } else {

                $fulfillmentList = FulfillmentList::findOne(['name' => 'ShipStation']);

                $fulfillment = new Fulfillment();

                $fulfillment->user_id = $user_id;
                $fulfillment->connection_info = $shipStationClientInfoJson;
                $fulfillment->name = "ShipStation";
                $fulfillment->fulfillment_list_id = $fulfillmentList->id;
                $fulfillment->connected = Fulfillment::CONNECTED_YES;
                $fulfillment->fulfillment_link = $fulfillmentList->link;

                if ( $fulfillment->save() ) {

                    $user_fulfillment_id = $fulfillment->id;

                    $workerShipstationCr = new ConsoleRunner(['file' => '@console/yii']);
                    $workerShipstationCmd = 'fulfill-worker/shipstation-send-order '.$user_fulfillment_id;
                    $workerShipstationCr->run($workerShipstationCmd);

                    $task = new Crontask();
                    $task->name = 'Shiphero';
                    $task->action = 'fulfill-worker/shipstation-send-order';
                    $param = [$user_fulfillment_id, false];
                    $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                    $task->completed = Crontask::COMPLETED_YES;
                    $task->save(false);

                    $response['success'] = true;
                    $response['error'] = '';
                    return json_encode($response);

                }

                $response['error'] = 'Database Connection Error!';
                return json_encode($response);

            }

        }

        $response['error'] = "Your API Key and API Secret is not correct. It's giving Unauthorized access. Please check and try again.";
        return json_encode($response);

    }

    public function actionShiphero() {

        $userId = Yii::$app->user->identity->id;
        $shipStation_Connection = Fulfillment::findOne(['user_id' => $userId, 'name' => 'Shiphero']);

        return $this->render('shiphero', [
            'userId' => $userId,
            'checkConnection' => $shipStation_Connection
        ]);
    }

    public function actionAuthShiphero() {

        $response = [
            'error' => 'Unknown Data!!!',
            'success' => false,
        ];
        $postData = Yii::$app->request->post();

        if ( empty($postData) ){
            return json_encode($response);
        }

        $shiphero_key = $postData['shiphero_key'];
        $shiphero_secret = $postData['shiphero_secret'];
        $user_id = $postData['user_id'];

        $shipheroClientInfo = [
            'apiKey' => $shiphero_key,
            'apiSecret' => $shiphero_secret,
        ];
        $shipheroClientInfoJson = @json_encode($shipheroClientInfo, JSON_UNESCAPED_UNICODE);

        $fulfillmentList = FulfillmentList::findOne(['name' => 'Shiphero']);

        $fulfillment = Fulfillment::findOne(['name' => 'Shiphero', 'user_id' => $user_id]);

        if (empty($fulfillment))
            $fulfillment = new Fulfillment();

        $fulfillment->user_id = $user_id;
        $fulfillment->connection_info = $shipheroClientInfoJson;
        $fulfillment->name = "Shiphero";
        $fulfillment->fulfillment_list_id = $fulfillmentList->id;
        $fulfillment->connected = Fulfillment::CONNECTED_YES;
        $fulfillment->fulfillment_link = $fulfillmentList->link;

        if ( $fulfillment->save() ) {

            $user_fulfillment_id = $fulfillment->id;

            $workerShipheroCr = new ConsoleRunner(['file' => '@console/yii']);
            $workerShipheroCmd = 'fulfill-worker/shiphero-send-order '.$user_fulfillment_id;
            $workerShipheroCr->run($workerShipheroCmd);

            $response['success'] = true;
            $response['error'] = '';
            return json_encode($response);

        }

        $response['error'] = 'Database Connection Error!';
        return json_encode($response);

    }

    /**
     * Displays a single Fulfillment model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Fulfillment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Fulfillment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Fulfillment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    //ShipStation Instruction Page
    public function actionInstruction() {

        return $this->render('instruction');
    }

    /**
     * Deletes an existing Fulfillment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Fulfillment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Fulfillment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Fulfillment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionTablerate() {


        return $this->render('tablerate');
    }

    public function actionCarriers() {

        return $this->render('carriers');
    }

    public function actionSoftware() {
        return $this->render('software');
    }

    protected function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function actionShiphawk() {
        $is_connect_fulfillment = false;
        $user_id = Yii::$app->user->identity->id;
        $fullfillment_list_row = FulfillmentList::find()->where(['name' => 'Shiphawk'])->one();
        if(empty($fullfillment_list_row)) {// if there is no Shiphawk in the FulfillmentList table
            return $this->redirect(['/fulfillment/software']);
        }

        // check if this connection is still existed in Fulfillment table
        $fulfillment_row = Fulfillment::find()->where([
            'fulfillment_list_id' => $fullfillment_list_row->id, 
            'user_id' => $user_id
        ])->one();

        if(!empty($fulfillment_row) and $fulfillment_row->connected == Fulfillment::CONNECTED_YES) {
            $is_connect_fulfillment = true;
        }

        $model = new ShiphawkConnectionForm();

        // either the page is initially displayed or there is some validation error
        return $this->render('shiphawk', [
            'model' => $model,
            'is_connect_fulfillment' => $is_connect_fulfillment
        ]);
    }

    public function actionShiphawkAuthorize() {
        $user_id = Yii::$app->user->identity->id;
        $model = new ShiphawkConnectionForm();


        if ($model->load(Yii::$app->request->post()) and $model->validate()) {
            $post_variable = Yii::$app->request->post('ShiphawkConnectionForm');
            $product_key = $post_variable['product_key'];

            try {
                $fullfillment_list_row = FulfillmentList::find()->where(['name' => 'Shiphawk'])->one();
                if(empty($fullfillment_list_row)) {// if there is no Shiphawk in the FulfillmentList table
                    throw new \Exception('Invalid fulfillment');
                }

                // try the connect
                $addresses_api = 'https://shiphawk.com/api/v4/webhooks/events';
                $header = array(
                    'X-Api-Key: '.$product_key
                );

                $response = \frontend\components\CustomFunction::curlHttp($addresses_api, null, 'GET', $header);
                $json_response = json_decode($response, true);

                if(isset($json_response['error'])) {
                    throw new \Exception($json_response['error']);
                }

                //save the connection details
                if(empty($fulfillment_row)) {
                    $fulfillment_row = new Fulfillment();
                }

                $fulfillment_row->fulfillment_list_id = $fullfillment_list_row->id;
                $fulfillment_row->name = $fullfillment_list_row->name;
                $fulfillment_row->user_id = $user_id;
                $fulfillment_row->connected = Fulfillment::CONNECTED_YES;
                $fulfillment_row->fulfillment_link = $fullfillment_list_row->link;
                $fulfillment_row->created_at = date('Y-m-d h:i:s', time());
                $fulfillment_row->updated_at = date('Y-m-d h:i:s', time());
                $fulfillment_row->connection_info = json_encode(
                    array("product_key" => $product_key)
                );
                $fulfillment_row->save();

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'fulfill-worker/shiphawk '.$user_id;
                $importCmd .= ' ';
                $importCmd .= $fullfillment_list_row->id;
                $importCr->run($importCmd);

                echo json_encode([
                    'success' => true,
                    'message' => 'Your Shiphawk Fulfillment has been connected successfully. Updating has started and you will be notified once it is completed.'
                ]);
            } catch(\Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid form data.'
            ]);
        }
    }
}
