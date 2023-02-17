<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Orders;
use common\models\CustomerUser;
use common\models\OrdersProducts;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class NetsuitsController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['signup', 'login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    ' delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Disable CSRF Validation
     * @param type $action
     * @return type
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex() {
        $array4 = array();
        // $OrderDATA = new Orders;
        $connection = \Yii::$app->db;
        $orders_data = $connection->createCommand('SELECT * from orders Where order_ID="' . 3 . '"');
        $OrderDATA = $orders_data->queryOne();

        $ordersProducts = OrdersProducts::find()->where(['order_Id' => $OrderDATA['order_ID']])->asArray()->all();
        $customerDATA = CustomerUser::find()->where(['customer_ID' => $OrderDATA['customer_id']])->asArray()->one();
        $OrderDATA['items'] = $ordersProducts;
        $OrderDATA['customerDetails'] = $customerDATA;

        $data = \yii\helpers\Json::encode($OrderDATA);
//        $handle = curl_init('https://rest.na1.netsuite.com/app/site/hosting/restlet.nl?script=1127&deploy=1&module=salesorder');
//        curl_setopt($handle, CURLOPT_POST, true);
//        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
//        curl_exec($handle);
//        curl_close($handle);
        echo '<pre>';
        echo $data;
        die;
    }

}