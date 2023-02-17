<?php

namespace frontend\controllers;

use common\models\UserConnection;
use common\models\Fulfillment;
use Yii;
use frontend\models\search\OrderSearch;
use frontend\components\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Customer;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Order;

use common\models\Stores;

use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\CurrencySymbol;
use common\models\Products;
use frontend\components\Helpers;
use kartik\mpdf\Pdf;

/**
 * OrdersController implements the CRUD actions for Orders model.
 */
class OrderController extends BaseController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'invoices', 'createorderhook', 'bigorder', 'connected', 'inactive-orders'],
            'rules' => [
                [
                'actions' => ['signup', 'createorderhook', 'bigorder'],
                'allow' => true,
                'roles' => ['?'],
                ],
                [
                'actions' => [
                    'logout', 'index', 'view', 'create', 'update', 'invoices', 'createorderhook', 'bigorder', 'connected',
                    'inactive-orders'],
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

    public function actionInvoices() {
	    return $this->render('invoice');
    }

    public function actionConnectedOrder() {

        $currentUserId = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $currentUserId = Yii::$app->user->identity->parent_id;
        }
        $user_connection_id = $_GET['u'];
        $userConnection = UserConnection::findOne(['id'=>$user_connection_id, 'user_id'=>$currentUserId]);
        return $this->render('connected', [
            'label' => $userConnection->getPublicName(),
            'connection_id' => $userConnection->id,
        ]);
    }

    public function actionInactiveOrder() {
        $currentUserId = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $currentUserId = Yii::$app->user->identity->parent_id;
        }
        $user_connection_id = $_GET['u'];
        $userConnection = UserConnection::findOne(['id'=>$user_connection_id, 'user_id'=>$currentUserId]);

        return $this->render('inactiveorders', [
            'label' => $userConnection->getPublicName(),
            'connection_id' => $userConnection->id,
        ]);
    }

    public function actionOrderAllAjax(){

        $user_id = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        $connection_query = "";
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
            $user_permission = Yii::$app->user->identity->getPermission();
            $connected_channels = UserConnection::find()->where(['user_id' => $user_id])->available()->all();
            $permission_channels = explode(", ", $user_permission->channel_permission);
            $first = true;
            if(!empty($connected_channels)) {
                $connection_query = " AND (";
                foreach ($connected_channels as $connected_channel) {
                    if (in_array($connected_channel->connection_id, $permission_channels)) {
                        if ($first)
                            $connection_query = $connection_query . "`order`.user_connection_id = {$connected_channel->id}";
                        else
                            $connection_query = "OR" . $connection_query . "`order`.user_connection_id = {$connected_channel->id}";
                        $first = false;
                    }
                }
                $connection_query = $connection_query . ") ";
            }
        }

        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $orderby_str = 'first_name';
        if ($orderby == 1) {
            $orderby_str = 'connection_order_id';
        }elseif ($orderby == 2) {
            $orderby_str = 'full_name';
        }elseif ($orderby == 3) {
            $orderby_str = 'ch_img';
        } elseif ($orderby == 4) {
            $orderby_str = 'total_amount';

        } elseif ($orderby == 5) {
            $orderby_str = 'order_date';
        } elseif ($orderby == 5) {
            $orderby_str = 'country';
        }else {
            $orderby_str = 'status';
        }
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';

        $conversion_rate = 1;
        $currency_symbol = "$";
        if(isset(Yii::$app->user->identity->currency)){
            $user_currency = Yii::$app->user->identity->currency;
            $conversion_rate = CurrencyConversion::getDbConversionRate($user_currency);
            $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user_currency)])->select(['id', 'symbol'])->asArray()->one();
            if (isset($selected_currency) and !empty($selected_currency)) {
                $currency_symbol = $selected_currency['symbol'];
            }
        }

        $connection = \Yii::$app->db;
        if ($search != '') {
            $search = "And (connection_order_id like '%{$search}%' OR total_amount like '%{$search}%' OR ((SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`) like '%{$search}%'))";
        }
        $query = "SELECT 
                        `order`.*,
                        (SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`)  AS full_name,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img                    
                    FROM
                        `order`
                    WHERE  `order`.`user_id` = {$user_id} {$connection_query} AND `order`.visible = 'active'  $search ORDER BY $orderby_str {$asc} limit {$start}, {$length}";
        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(id) as total_count         
                    FROM
                        `order`
                    WHERE `order`.`user_id` = {$user_id} {$connection_query} AND `order`.visible = 'active' $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];
            $sigleData['0'] = "<div class='be-checkbox'><input name='inactiveorder_check' id='ck{$index}' value='{$single['id']}' name='ck{$index}' type='checkbox' data-parsley-multiple='groups' value='bar' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='order_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/order/view?id={$single['id']}'>{$single['connection_order_id']}</a>";
            $sigleData['2'] = "<a href ='/people/view?id={$single['customer_id']}'>{$single['full_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['3'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";
            $clv = $single['total_amount'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['4'] = $currency_symbol . $clv;
            $sigleData['5'] = $single['order_date'];
            $sigleData['6'] = $single['ship_country'];

            $order_status = $single['status'];
            $order_status_class = '';
            if ($order_status == 'Completed') {
                $order_status_class = 'label-success';
            }

            if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') {
                $order_status_class = 'label-danger';
            }

            if ($order_status == 'In Transit' || $order_status == 'On Hold') {
                $order_status_class = 'label-default';
            }

            if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold') {
                $order_status_class = 'label-warning';
            }
            if ($order_status == 'Shipped' || $order_status == 'Partially Shipped') {
                $order_status_class = 'label-primary';
            }
            $sigleData['7'] = "<span class='label  {$order_status_class}'>{$order_status}</span>";
            $result[] = $sigleData;
        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionOrderConnectedAjax(){
        $connection_id = $_GET['connection_id'];
        $user_id = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $orderby_str = 'first_name';
        if ($orderby == 1) {
            $orderby_str = 'connection_order_id';
        }elseif ($orderby == 2) {
            $orderby_str = 'full_name';
        }elseif ($orderby == 3) {
            $orderby_str = 'ch_img';
        } elseif ($orderby == 4) {
            $orderby_str = 'total_amount';

        } elseif ($orderby == 5) {
            $orderby_str = 'order_date';
        } elseif ($orderby == 5) {
            $orderby_str = 'country';
        }else {
            $orderby_str = 'status';
        }
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';

        $conversion_rate = 1;
        $currency_symbol = "$";
        if(isset(Yii::$app->user->identity->currency)){
            $user_currency = Yii::$app->user->identity->currency;
            $conversion_rate = CurrencyConversion::getDbConversionRate($user_currency);
            $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user_currency)])->select(['id', 'symbol'])->asArray()->one();
            if (isset($selected_currency) and !empty($selected_currency)) {
                $currency_symbol = $selected_currency['symbol'];
            }
        }

        $connection = \Yii::$app->db;
        if ($search != '') {
            $search = "And (connection_order_id like '%{$search}%' OR total_amount like '%{$search}%' OR ((SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`) like '%{$search}%'))";
        }
        $query = "SELECT 
                        `order`.*,
                        (SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`)  AS full_name,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img                    
                    FROM
                        `order`
                    WHERE `order`.`user_id` = {$user_id} AND `order`.visible = 'active' AND `order`.user_connection_id ={$connection_id} $search ORDER BY $orderby_str {$asc} limit {$start}, {$length}";
        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(id) as total_count         
                    FROM
                        `order`
                    WHERE `order`.`user_id` = {$user_id} AND `order`.visible = 'active' AND `order`.user_connection_id ={$connection_id} $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];
            $sigleData['0'] = "<div class='be-checkbox'><input name='inactiveorder_check' id='ck{$index}' value='{$single['id']}' name='ck{$index}' type='checkbox' data-parsley-multiple='groups' value='bar' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='order_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/order/view?id={$single['id']}'>{$single['connection_order_id']}</a>";
            $sigleData['2'] = "<a href ='/people/view?id={$single['customer_id']}'>{$single['full_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['3'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";
            $clv = $single['total_amount'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['4'] = $currency_symbol . $clv;
            $sigleData['5'] = $single['order_date'];
            $sigleData['6'] = $single['ship_country'];

            $order_status = $single['status'];
            $order_status_class = '';
            if ($order_status == 'Completed') {
                $order_status_class = 'label-success';
            }

            if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') {
                $order_status_class = 'label-danger';
            }

            if ($order_status == 'In Transit' || $order_status == 'On Hold') {
                $order_status_class = 'label-default';
            }

            if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold') {
                $order_status_class = 'label-warning';
            }
            if ($order_status == 'Shipped' || $order_status == 'Partially Shipped') {
                $order_status_class = 'label-primary';
            }
            $sigleData['7'] = "<span class='label  {$order_status_class}'>{$order_status}</span>";
            $result[] = $sigleData;
        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionOrderInactiveAjax(){
        $connection_id = $_GET['connection_id'];
        $user_id = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $orderby_str = 'first_name';
        if ($orderby == 1) {
            $orderby_str = 'connection_order_id';
        }elseif ($orderby == 2) {
            $orderby_str = 'full_name';
        }elseif ($orderby == 3) {
            $orderby_str = 'ch_img';
        } elseif ($orderby == 4) {
            $orderby_str = 'total_amount';

        } elseif ($orderby == 5) {
            $orderby_str = 'order_date';
        } elseif ($orderby == 5) {
            $orderby_str = 'country';
        }else {
            $orderby_str = 'status';
        }
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';

        $conversion_rate = 1;
        $currency_symbol = "$";
        if(isset(Yii::$app->user->identity->currency)){
            $user_currency = Yii::$app->user->identity->currency;
            $conversion_rate = CurrencyConversion::getDbConversionRate($user_currency);
            $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user_currency)])->select(['id', 'symbol'])->asArray()->one();
            if (isset($selected_currency) and !empty($selected_currency)) {
                $currency_symbol = $selected_currency['symbol'];
            }
        }

        $connection = \Yii::$app->db;
        if ($search != '') {
            $search = "And (connection_order_id like '%{$search}%' OR total_amount like '%{$search}%' OR ((SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`) like '%{$search}%'))";
        }
        $query = "SELECT 
                        `order`.*,
                        (SELECT CONCAT(`customer`.`first_name`, ' ', `customer`.`last_name`) FROM `customer` WHERE `order`.`customer_id` = `customer`.`id`)  AS full_name,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = `order`.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img                    
                    FROM
                        `order`
                    WHERE `order`.`user_id` = {$user_id} AND `order`.visible = 'in_active' AND `order`.user_connection_id ={$connection_id} $search ORDER BY $orderby_str {$asc} limit {$start}, {$length}";
        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(id) as total_count         
                    FROM
                        `order`
                    WHERE `order`.`user_id` = {$user_id} AND `order`.visible = 'active' AND `order`.user_connection_id ={$connection_id} $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];
            $sigleData['0'] = "<div class='be-checkbox'><input name='inactiveorder_check' id='ck{$index}' value='{$single['id']}' name='ck{$index}' type='checkbox' data-parsley-multiple='groups' value='bar' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='order_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/order/view?id={$single['id']}'>{$single['connection_order_id']}</a>";
            $sigleData['2'] = "<a href ='/people/view?id={$single['customer_id']}'>{$single['full_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['3'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";
            $clv = $single['total_amount'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['4'] = $currency_symbol . $clv;
            $sigleData['5'] = $single['order_date'];
            $sigleData['6'] = $single['ship_country'];

            $order_status = $single['status'];
            $order_status_class = '';
            if ($order_status == 'Completed') {
                $order_status_class = 'label-success';
            }

            if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') {
                $order_status_class = 'label-danger';
            }

            if ($order_status == 'In Transit' || $order_status == 'On Hold') {
                $order_status_class = 'label-default';
            }

            if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold') {
                $order_status_class = 'label-warning';
            }
            if ($order_status == 'Shipped' || $order_status == 'Partially Shipped') {
                $order_status_class = 'label-primary';
            }
            $sigleData['7'] = "<span class='label  {$order_status_class}'>{$order_status}</span>";
            $result[] = $sigleData;
        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate() {
        $model = new Orders(Yii::$app->user->identity->id);
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $model = new Orders(Yii::$app->user->identity->parent_id);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->order_ID]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->order_ID]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id) {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id) {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAreachartonorders() {
        if (!empty($_POST['data'])) {
            $post = $_POST['data'];
        }
        $currentmonth = date('m');
        $currentyear = date('Y');
        $currentwday = date('N');
        $currentday = date('d');
        $connected_connection = UserConnection::find()->where(['user_id' => Yii::$app->user->identity->id])->available()->all();
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $connected_connection = UserConnection::find()->where(['user_id' => Yii::$app->user->identity->parent_id])->available()->all();
        }
        $arr = array();
        $tmpquery = "";
        if (!empty($connected_connection)) {
            foreach ($connected_connection as $user_connection) {
                if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
                    $user_permission = Yii::$app->user->identity->getPermission();
                    $permission_channels = explode(", ", $user_permission->channel_permission);
                    if (!in_array($user_connection->connection_id, $permission_channels)) {
                        continue;
                    }
                }
                $fin_name = $user_connection->getPublicName();
                /*		 * *********************** FOR TODAY************************ */
                if ($post == 'ordercharttoday' || $post == 'ordercharttodaymob') {
                    $current_date = date('Y-m-d', time());
                    $j = 0;
                    for ($i = 1; $i <= 24; $i++) {
                        if ($i == 1) {
                            $current_date_hour = date('Y-m-d h:i:s', time());
                        } else {
                            $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                        }
                        $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                        $previous_hour1 = date('h:i:s', strtotime('-' . $i . ' hour'));
                        $date_check = date('Y-m-d', strtotime($previous_hour));
                        if ($date_check == $current_date) {
                            $orders_data = \Yii::$app->db->createCommand("select * from `order` WHERE user_connection_id={$user_connection->id}  AND order_date BETWEEN ('{$previous_hour}') AND ('{$current_date_hour}') AND date(order_date)='{$current_date}'");
                            $orders_count = count($orders_data->queryAll());
                            $today[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $previous_hour1);
                        }
                        $j++;
                    }
                }
                /*		 * *********************** FOR WEEKLY************************ */
                if ($post == 'orderchartweek' || $post == 'orderchartweekmob') {
                    for ($j = 1; $j <=7; $j++):
                        if($j>$currentwday)
                            break;
                        $tmp_day = $currentday - $currentwday + $j;
                        $search_date = date('Y-m-d', strtotime("$currentyear-$currentmonth-$tmp_day"));
                        $disp_date = date('l', strtotime("$currentyear-$currentmonth-$tmp_day"));
                        $orders_data = \Yii::$app->db->createCommand("select * from `order` WHERE user_connection_id={$user_connection->id}  AND order_date LIKE '%{$search_date}%'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }

                /*		 * *********************** FOR MONTHLY************************ */
                if ($post == 'orderchartmonth' || $post == 'orderchartmonthmob') {
                    for ($j = 1; $j <=31; $j++):
                        if($j>$currentday)
                            break;
                        $search_date = date('Y-m-d', strtotime("$currentyear-$currentmonth-$j"));
                        $orders_data = \Yii::$app->db->createCommand("select * from `order` WHERE user_connection_id={$user_connection->id}  AND order_date LIKE '%{$search_date}%'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $j);
                    endfor;
                }
                /*		 * *********************** FOR Quarterly************************ */
                if ($post == 'orderchartQuater' || $post == 'orderchartQuatermob') {

                    $quart = floor(($currentmonth-1) / 3);
                    for ($j = 1; $j <=3; $j++):
                        $month_str = $quart * 3 + $j;
                        if($month_str>$currentmonth){
                            break;
                        }
                        $disp_date = date('M', strtotime("$currentyear-$month_str-1"));
                        $orders_data = \Yii::$app->db->createCommand("select * from `order` WHERE user_connection_id={$user_connection->id}  AND month(order_date) = $month_str AND year(order_date)='{$currentyear}'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }

                /*		 * *********************** FOR YEARLY************************ */
                if ($post == 'orderchartyear' || $post == 'orderchartyearmob') {
                    for ($j = 1; $j <=12; $j++):
                        if($j>$currentmonth){
                            break;
                        }
                        $disp_date = date('M', strtotime("$currentyear-$j-1"));
                        $orders_data = \Yii::$app->db->createCommand("select * from `order` WHERE user_connection_id={$user_connection->id}  AND month(order_date) = $j AND year(order_date)='{$currentyear}'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }
                /*		 * *********************** FOR Annual************************ */
                if ($post == 'orderchartannual' || $post == 'orderchartannualmob') {
                    $customer = \Yii::$app->db->createCommand("select YEAR(order_date) as lastyear from `order` WHERE user_connection_id={$user_connection->id} order by order_date")->queryOne();
                    if(!empty($customer)) {
                        for ($j = $customer['lastyear']; $j <= $currentyear; $j++):
                            $connection = \Yii::$app->db;
                            $orders_data = $connection->createCommand("select id from `order` WHERE user_connection_id={$user_connection->id}  AND year(order_date)='{$j}'");
                            $orders_count = count($orders_data->queryAll());
                            $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $j);
                        endfor;
                    }
                }
            }
        }

        if (!empty($today)) {
            $arr = array_reverse($today);
        }

        $array1 = array();
        foreach ($arr as $a):
            /* Check if key exists or not */
            $dates = $a['date'];
            if (array_key_exists($dates, $array1)) {
            $array1[$dates][] = array(
                'name' => $a['name'],
                'user' => $a['user'],
            );
            } else {
            $array1[$dates][] = array(
                'name' => $a['name'],
                'user' => $a['user'],
            );
            }
        endforeach;

        $data_val_2 = array();
        foreach ($array1 as $_data => $value) {
            $data_val = array();
            foreach ($value as $_value) {
            $data_val['period'] = $_data;
            //$data_val[$_value['name']]=$_value['name'];
            $data_val[$_value['name']] = $_value['user'];
            }
            $data_val_2[] = $data_val;
        }

        $keyname = array();
        $keyname_2 = array();
        if (!empty($data_val)) {
            foreach ($data_val as $key => $val) {
            if ($key == 'period') {

            } else {
                $keyname[] = $key;
            }
            }
        }
        $keyname_2 = $keyname;

        /*
         * array to store hex color
         */
        $colorarr = array(
            0 => '#0091ea', #e04a72
            1 => '#00b0ff', #83ed1a
            2 => '#40c4ff',
            3 => '#80d8ff',
            4 => '#01579b',
            5 => '#0277bd',
            6 => '#039be5',
            7 => '#03a9f4',
            8 => '#b3e5fc',
            9 => '#81d4fa',
            10 => '#29b6f6',
            11 => '#e1f5fe',
            12 => '#b5af79',
            13 => '#914f84',
            14 => '#ce6d87',
            15 => '#e04a72',
        );
        $randcolor = array();
        $randcolor_2 = array();
        $showcolorhtml = '';
        $c = 0;
        $cc = 0;
        foreach ($keyname as $color) {
            // $randcolor[] = '#' . random_color();
            $randcolor[] = $colorarr[$c++];
            //$strname = explode(' ', $color);
            $showcolorhtml .= '<li><span data-color="main-chart-color" class="BigCommerce connected_store_name" style="background-color: ' . $colorarr[$cc++] . ';"></span>' . $color . '</li>';
        }

        $randcolor_2 = $randcolor;
        $data = array('data' => json_encode($data_val_2), 'ykeyslabels' => json_encode($keyname_2), 'linecolors' => json_encode($randcolor_2), 'showcolorhtml' => $showcolorhtml);
        return \yii\helpers\Json::encode($data);
    }

    /*
     * ajax hit from new_custom_elliot.js to delete multiple select data from orders
     */
    public function actionAjaxOrdersDelete() {
	//echo '<pre>';print_r($_POST);die;
        if (!isset($_POST['orderIds']) || count($_POST['orderIds']) == 0) {
            return FALSE;
        } else {
            $product_ids = $_POST['orderIds'];
            foreach ($product_ids as $_product_id) {

            $delete_product = Orders::updateAll(array('order_visible_status' => 'in_active'), 'order_ID = ' . $_product_id);
            }
            return TRUE;
        }
        }

    public function actionAjaxInactiveOrdersDeletion() {
        // echo '<pre>';
        // print_r($_POST);
        // die('ayaa');
        if (!isset($_POST['order_id']) || count($_POST['order_id']) == 0) {
            return false;
        } else {
            $ordersID = $_POST['order_id'];
            foreach ($ordersID as $orders) {
            $undo_delete = Orders::updateAll(array('order_visible_status' => 'active'), 'order_ID = ' . $orders);
            }
            return true;
        }
    }

    public function actionSavepdf($id) {

        //  $customer_data = CustomerUser::find()->Where(['customer_ID' => $id])->one();
        $content = $this->renderPartial('view', [
            'model' => $this->findModel($id),
            //  'Customer' => $customer_data,
        ]);

        // setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'mode' => Pdf::MODE_UTF8,
            // set mPDF properties on the fly
            'options' => ['title' => 'Elliot Invoice'],
            // call mPDF methods on the fly
            'methods' => [
            'SetHeader' => ['Elliot'],
            'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

}
