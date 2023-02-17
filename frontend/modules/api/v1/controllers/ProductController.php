<?php

namespace frontend\modules\api\v1\controllers;


use common\models\User;
use common\models\UserConnection;
use frontend\modules\api\v1\resources\ProductInventory;
use Yii;
use yii\helpers\Json;
use yii\rest\ActiveController;
use frontend\modules\api\v1\resources\Product;
use yii\web\HttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;


class ProductController extends ActiveController
{
    public $modelClass = 'frontend\modules\api\v1\resources\Product';
    public $inventoryModelClass = 'frontend\modules\api\v1\resources\ProductInventory';

    private $limit = 50;
    private $offset = 0;
    private $createdAtMin = null;
    private $createdAtMax = null;
    private $updatedAtMin = null;
    private $updatedAtMax = null;

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
                HttpBearerAuth::className(),
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
            'inventory' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->inventoryModelClass,
                'prepareDataProvider' => [$this, 'prepareInventoryProvider']
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

        if ( isset($RequestData['createdAtMin']) && !empty($RequestData['createdAtMin']) ){
            $this->createdAtMin = $RequestData['createdAtMin'];
        }
        if ( isset($RequestData['createdAtMax']) && !empty($RequestData['createdAtMax']) ){
            $this->createdAtMax = $RequestData['createdAtMax'];
        }

        if ( isset($RequestData['updatedAtMin']) && !empty($RequestData['updatedAtMin']) ){
            $this->updatedAtMin = $RequestData['updatedAtMin'];
        }
        if ( isset($RequestData['updatedAtMax']) && !empty($RequestData['updatedAtMax']) ){
            $this->updatedAtMax = $RequestData['updatedAtMax'];
        }

        $response = Product::find()
            ->where(['user_id' => $currentUserId])
            ->Activated()
            ->CreatedAtMinWhere($this->createdAtMin)
            ->CreatedAtMaxWhere($this->createdAtMax)
            ->UpdatedAtMinWhere($this->updatedAtMin)
            ->UpdatedAtMaxWhere($this->updatedAtMax)
            ->limit($this->limit)
            ->offset($this->offset)
            ->all();

        return $response;
    }

    public function actionCount(){

        $currentUserId = Yii::$app->user->identity->id;
        $RequestData = Yii::$app->request->get();

        if ( isset($RequestData['createdAtMin']) && !empty($RequestData['createdAtMin']) ){
            $this->createdAtMin = $RequestData['createdAtMin'];
        }
        if ( isset($RequestData['createdAtMax']) && !empty($RequestData['createdAtMax']) ){
            $this->createdAtMax = $RequestData['createdAtMax'];
        }

        if ( isset($RequestData['updatedAtMin']) && !empty($RequestData['updatedAtMin']) ){
            $this->updatedAtMin = $RequestData['updatedAtMin'];
        }
        if ( isset($RequestData['updatedAtMax']) && !empty($RequestData['updatedAtMax']) ){
            $this->updatedAtMax = $RequestData['updatedAtMax'];
        }

        $resultCount = Product::find()
            ->where(['user_id' => $currentUserId])
            ->Activated()
            ->CreatedAtMinWhere($this->createdAtMin)
            ->CreatedAtMaxWhere($this->createdAtMax)
            ->UpdatedAtMinWhere($this->updatedAtMin)
            ->UpdatedAtMaxWhere($this->updatedAtMax)
            ->count();

        $response = [
            'count' => $resultCount
        ];

        return $response;
    }

    public function prepareInventoryProvider()
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

        if ( isset($RequestData['createdAtMin']) && !empty($RequestData['createdAtMin']) ){
            $this->createdAtMin = $RequestData['createdAtMin'];
        }
        if ( isset($RequestData['createdAtMax']) && !empty($RequestData['createdAtMax']) ){
            $this->createdAtMax = $RequestData['createdAtMax'];
        }

        if ( isset($RequestData['updatedAtMin']) && !empty($RequestData['updatedAtMin']) ){
            $this->updatedAtMin = $RequestData['updatedAtMin'];
        }
        if ( isset($RequestData['updatedAtMax']) && !empty($RequestData['updatedAtMax']) ){
            $this->updatedAtMax = $RequestData['updatedAtMax'];
        }

        $response = ProductInventory::find()
            ->where(['user_id' => $currentUserId])
            ->Activated()
            ->CreatedAtMinWhere($this->createdAtMin)
            ->CreatedAtMaxWhere($this->createdAtMax)
            ->UpdatedAtMinWhere($this->updatedAtMin)
            ->UpdatedAtMaxWhere($this->updatedAtMax)
            ->limit($this->limit)
            ->offset($this->offset)
            ->all();

        return $response;
    }

    public function actionInventoryUpdate(){
        $currentUserId = Yii::$app->user->identity->id;
        $requestData = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "errors" => "Unknown Product Id"
        ];


        $requestData['user_id'] = $currentUserId;

        if ( isset($requestData['sku']) && !empty($requestData['sku']) ) {
            unset($requestData['sku']);
        }

        if ( isset($requestData['uniqueId']) && !empty($requestData['uniqueId']) ){
            $requestData['id'] = intval($requestData['uniqueId']);
        } else {
            $response['errors'] = "Unique Id should be not empty.";
            return $response;
        }

        $pUpdateInventoryData = [
            'ProductInventory' => $requestData,
        ];

        $pInventory = $this->findInventoryModel($requestData['id']);

        if ( !empty($pInventory) ){

            $pInventory->load($pUpdateInventoryData);

            if ( $pInventory->validate() ){

                if ( $pInventory->save() ){

                    $pInventoryId = $pInventory->id;

                    return $this->findInventoryModel($pInventoryId);
                }


            } else {

                $response['errors'] = $pInventory->errors;

            }

        }


        return $response;
    }

    public function actionCreate(){

        $currentUserId = Yii::$app->user->identity->id;
        $requestData = Yii::$app->request->bodyParams;

        $userDefaultConnectionId = User::getDefaultConnection($currentUserId);

        $userDefaultConnection = UserConnection::findOne(['id' => $userDefaultConnectionId]);

        $currency = $userDefaultConnection->userConnectionDetails->currency;
        $country_code = $userDefaultConnection->userConnectionDetails->country_code;

        $requestData['user_id'] = $currentUserId;
        $requestData['permanent_hidden'] = Product::PRODUCT_PERMANENT_NO;
        $requestData['currency'] = $currency;
        $requestData['country_code'] = $country_code;

        $pModelData = [
            'Product' => $requestData,
        ];

        $response = [
            "success" => false,
            "errors" => "Unknown Error"
        ];

        $pModel = new Product();

        $pModel->load($pModelData);

        if ($pModel->validate()) {
            // all inputs are valid

            if ( $pModel->save() ){

                $pModelId = $pModel->id;

                return $this->findModel($pModelId);
            }

        } else {
            // validation failed: $errors is an array containing error messages
            $response['errors'] = $pModel->errors;
        }

        return $response;
    }

    public function actionDelete(){

        $requestData = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "message" => "Product id should be not empty!"
        ];

        if ( isset($requestData['id']) && !empty($requestData['id']) ){
            $del_pId = $requestData['id'];
        } else {
            return $response;
        }

        $pModel = $this->findModel($del_pId);

        if ($pModel->delete()) {

            $response['success'] = true;
            $response['message'] = "";

        } else {
            // validation failed: $errors is an array containing error messages
            $response['success'] = false;
            $response['message'] = $pModel->errors;
        }

        return $response;
    }


    public function findModel($id)
    {
        $currentUserId = Yii::$app->user->identity->id;
        $model = Product::find()
            ->where(['user_id' => $currentUserId])
            ->Activated()
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model){
            throw new HttpException(404);
        }

        return $model;
    }

    public function findInventoryModel($id)
    {
        $currentUserId = Yii::$app->user->identity->id;
        $model = ProductInventory::find()
            ->where(['id' => (int)$id])
            ->andWhere(['user_id' => $currentUserId])
            ->Activated()
            ->one();
        if (!$model){
            throw new HttpException(404);
        }

        return $model;
    }

}