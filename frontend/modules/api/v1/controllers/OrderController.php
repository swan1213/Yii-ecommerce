<?php

namespace frontend\modules\api\v1\controllers;

use frontend\modules\api\v1\resources\OrderStatus;
use Yii;
use yii\rest\ActiveController;
use frontend\modules\api\v1\resources\Order;
use yii\web\HttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;


class OrderController extends ActiveController
{
    public $modelClass = 'frontend\modules\api\v1\resources\Order';

    private $limit = 50;
    private $offset = 0;
    private $orderDateMin = null;
    private $orderDateMax = null;
    private $orderUpdatedMin = null;
    private $orderUpdatedMax = null;

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                //HttpBasicAuth::className(),
                HttpBearerAuth::className(),
                //QueryParamAuth::className(),
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ],
        ];

    }

    public function prepareDataProvider()
    {

        $RequestData = Yii::$app->request->get();
        $currentUserId = Yii::$app->user->identity->id;

        if ( isset($RequestData['limit']) && !empty($RequestData['limit']) && $RequestData['limit'] > 0 ){
            $this->limit = $RequestData['limit'];
            if ($this->limit > 200)
                $this->limit = 200;
        }

        if ( isset($RequestData['page']) && !empty($RequestData['page']) && $RequestData['page'] > 0 ){
            $this->offset = ($RequestData['page'] - 1) * $this->limit;
        }

        if ( isset($RequestData['orderDateMin']) && !empty($RequestData['orderDateMin']) ){
            $this->orderDateMin = $RequestData['orderDateMin'];
        }
        if ( isset($RequestData['orderDateMax']) && !empty($RequestData['orderDateMax']) ){
            $this->orderDateMax = $RequestData['orderDateMax'];
        }

        if ( isset($RequestData['orderUpdatedMin']) && !empty($RequestData['orderUpdatedMin']) ){
            $this->orderUpdatedMin = $RequestData['orderUpdatedMin'];
        }
        if ( isset($RequestData['orderUpdatedMax']) && !empty($RequestData['orderUpdatedMax']) ){
            $this->orderUpdatedMax = $RequestData['orderUpdatedMax'];
        }


        $response = Order::find()
            ->where(['user_id' => $currentUserId])
            ->CreatedAtMinWhere($this->orderDateMin)
            ->CreatedAtMaxWhere($this->orderDateMax)
            ->UpdatedAtMinWhere($this->orderUpdatedMin)
            ->UpdatedAtMaxWhere($this->orderUpdatedMax)
            ->limit($this->limit)
            ->offset($this->offset)
            ->all();

        return $response;
    }

    public function actionPrintawb(){

        $RequestData = Yii::$app->request->get();
        $currentUserId = Yii::$app->user->identity->id;

        if ( isset($RequestData['order_id']) && !empty($RequestData['order_id']) && $RequestData['order_id'] > 0 ){
            $order_id = $RequestData['order_id'];
            $data = Order::find()
                ->where([
                    'id' => $order_id,
                    'user_id' => $currentUserId
                ])
                ->one();
            $response = [
                'success' => true,
                'data' => $data
            ];
        }
        else {
            $response = [
                'success' => false,
                'msg' => 'order_id required'
            ];
        }

        return $response;
    }

    public function actionCount(){

        $currentUserId = Yii::$app->user->identity->id;
        $RequestData = Yii::$app->request->get();

        if ( isset($RequestData['orderDateMin']) && !empty($RequestData['orderDateMin']) ){
            $this->orderDateMin = $RequestData['orderDateMin'];
        }
        if ( isset($RequestData['orderDateMax']) && !empty($RequestData['orderDateMax']) ){
            $this->orderDateMax = $RequestData['orderDateMax'];
        }

        if ( isset($RequestData['orderUpdatedMin']) && !empty($RequestData['orderUpdatedMin']) ){
            $this->orderUpdatedMin = $RequestData['orderUpdatedMin'];
        }
        if ( isset($RequestData['orderUpdatedMax']) && !empty($RequestData['orderUpdatedMax']) ) {
            $this->orderUpdatedMax = $RequestData['orderUpdatedMax'];
        }

        $resultCount = Order::find()
            ->where(['user_id' => $currentUserId])
            ->CreatedAtMinWhere($this->orderDateMin)
            ->CreatedAtMaxWhere($this->orderDateMax)
            ->UpdatedAtMinWhere($this->orderUpdatedMin)
            ->UpdatedAtMaxWhere($this->orderUpdatedMax)
            ->count();

        $response = [
            'count' => $resultCount
        ];

        return $response;
    }

    public function actionStatus()
    {

        $RequestData = Yii::$app->request->get();
        $currentUserId = Yii::$app->user->identity->id;

        if ( isset($RequestData['limit']) && !empty($RequestData['limit']) && $RequestData['limit'] > 0 ){
            $this->limit = $RequestData['limit'];
            if ($this->limit > 200)
                $this->limit = 200;
        }

        if ( isset($RequestData['page']) && !empty($RequestData['page']) && $RequestData['page'] > 0 ){
            $this->offset = ($RequestData['page'] - 1) * $this->limit;
        }

        if ( isset($RequestData['orderDateMin']) && !empty($RequestData['orderDateMin']) ){
            $this->orderDateMin = $RequestData['orderDateMin'];
        }
        if ( isset($RequestData['orderDateMax']) && !empty($RequestData['orderDateMax']) ){
            $this->orderDateMax = $RequestData['orderDateMax'];
        }

        if ( isset($RequestData['orderUpdatedMin']) && !empty($RequestData['orderUpdatedMin']) ){
            $this->orderUpdatedMin = $RequestData['orderUpdatedMin'];
        }
        if ( isset($RequestData['orderUpdatedMax']) && !empty($RequestData['orderUpdatedMax']) ) {
            $this->orderUpdatedMax = $RequestData['orderUpdatedMax'];
        }

        $response = OrderStatus::find()
            ->where(['user_id' => $currentUserId])
            ->CreatedAtMinWhere($this->orderDateMin)
            ->CreatedAtMaxWhere($this->orderDateMax)
            ->UpdatedAtMinWhere($this->orderUpdatedMin)
            ->UpdatedAtMaxWhere($this->orderUpdatedMax)
            ->limit($this->limit)
            ->offset($this->offset)
            ->all();

        return $response;
    }

    public function actionStatusUpdate(){

        $currentUserId = Yii::$app->user->identity->id;
        $requestData = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "errors" => "Unknown Order Id"
        ];


        $requestData['user_id'] = $currentUserId;

        if ( isset($requestData['id']) && !empty($requestData['id']) ){
            $requestData['id'] = intval($requestData['id']);
        } else {
            $response['errors'] = "Order Id should be not empty.";
            return $response;
        }

        $orderStatusData = [
            'OrderStatus' => $requestData,
        ];

        $order_model = $this->findOrderStatusModel($requestData['id']);

        if ( !empty($order_model) ){

            $order_model->load($orderStatusData);

            if ( $order_model->validate() ){

                if ( $order_model->save() ){

                    $order_id = $order_model->id;

                    return $this->findOrderStatusModel($order_id);
                }


            } else {

                $response['errors'] = $order_model->errors;

            }

        }


        return $response;

    }

    public function actionDelete(){

        $requestData = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "message" => "Order id should be not empty!"
        ];

        if ( isset($requestData['id']) && !empty($requestData['id']) ){
            $del_oId = $requestData['id'];
        } else {
            return $response;
        }

        $order_model = $this->findModel($del_oId);

        if ($order_model->delete()) {

            $response['success'] = true;
            $response['message'] = "";

        } else {
            // validation failed: $errors is an array containing error messages
            $response['success'] = false;
            $response['message'] = $order_model->errors;
        }

        return $response;
    }

    public function findModel($id)
    {
        $currentUserId = Yii::$app->user->identity->id;
        $model = Order::find()
            ->where(['user_id' => $currentUserId])
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model){
            throw new HttpException(404);
        }

        return $model;
    }

    public function findOrderStatusModel($id)
    {
        $currentUserId = Yii::$app->user->identity->id;
        $model = OrderStatus::find()
            ->where(['user_id' => $currentUserId])
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model){
            throw new HttpException(404);
        }

        return $model;
    }


}