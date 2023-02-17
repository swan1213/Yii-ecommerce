<?php

namespace frontend\controllers;

use common\models\City;
use common\models\Connection;
use common\models\Country;
use common\models\State;
use common\models\CurrencyConversion;
use common\models\CustomerAddress;
use common\models\Order;
use common\models\UserConnection;
use Yii;
use common\models\Customer;
use common\models\ChannelConnection;
use common\models\Channels;
use common\models\User;
use frontend\models\search\CustomerSearch;
use common\models\CustomerAbbrivation;
use common\models\Stores;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Countries;
use common\models\States;
use common\models\Cities;
use common\models\StoresConnection;
use common\models\StoreDetails;
use Bigcommerce\Api\Client as Bigcommerce;
use common\models\Orders;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\Products;
use common\models\Categories;
use common\models\CategoryAbbrivation;
use common\models\ProductCategories;
use common\models\ProductChannel;
use common\models\ProductAbbrivation;
use common\models\ProductImages;
use common\models\ProductVariation;
use common\models\VariationsSet;
use common\models\Variations;
use common\models\VariationsItemList;
use common\models\MerchantProducts;
use common\models\CurrencySymbol;
use frontend\components\Helpers;
use Automattic\WooCommerce\Client as Woocommerce;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use SoapClient;
use yii\db\Query;
use yii\db\ActiveQuery;
use yii\db\QueryBuilder;
use yii\data\ActiveDataProvider;
use frontend\controllers\StoresController as StoresController;
use frontend\controllers\ChannelsController as ChannelsController;

/**
 * CustomerUserController implements the CRUD actions for CustomerUser model.
 */
class PeopleController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'update', 'savecustomer', 'create-customer-hook', 'orderbigcommerce', 'orderhookcreate',
                    'producthooksbg', 'skuupdategb', 'productskuhooksbg',
                    'categoryhooksbg', 'magento-product-create', 'magento-order-create',
                    'magento-customer-create', 'magento-customer-address-update', 'connected-customer',
                    'inactive-customers', 'orderbigcommercecreate', 'orderbigcommerceupdate'],
                'rules' => [
                        [
                        'actions' => ['signup', 'create-customer-hook', 'orderbigcommerce', 'orderhookcreate', 'producthooksbg',
                            'skuupdategb', 'productskuhooksbg', 'categoryhooksbg', 'magento-product-create', 'magento-order-create',
                            'magento-customer-create', 'magento-customer-address-update', 'orderbigcommercecreate', 'orderbigcommerceupdate'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['logout', 'index', 'view', 'create', 'update', 'savecustomer', 'create-customer-hook', 'orderbigcommerce',
                            'orderhookcreate', 'producthooksbg',
                            'skuupdategb', 'productskuhooksbg', 'categoryhooksbg',
                            'sku', 'magento-product-create', 'magento-order-create',
                            'magento-customer-create', 'magento-customer-address-update', 'connected-customer', 'inactive-customers',
                            'orderbigcommercecreate', 'orderbigcommerceupdate'],
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

        $this->enableCsrfValidation = false;


        return parent::beforeAction($action);
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionCustomerajax()
    {
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
                            $connection_query = $connection_query . "A.user_connection_id = {$connected_channel->id}";
                        else
                            $connection_query = "OR" . $connection_query . "A.user_connection_id = {$connected_channel->id}";
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

        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';
        $orderby_str = '';
        if ($orderby == 1) {
            $orderby_str = "ORDER BY user_name {$asc}";
        }elseif ($orderby == 2) {
            $orderby_str = "ORDER BY ch_img {$asc}";
        }elseif ($orderby == 3) {
            $orderby_str = "ORDER BY address {$asc}";
        } elseif ($orderby == 4) {
        } elseif ($orderby == 5) {
            $orderby_str = "ORDER BY order_sum {$asc}";
        } elseif ($orderby == 6) {
            $orderby_str = "ORDER BY order_count {$asc}";
        } else {
            $orderby_str = '';
        }

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
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%' OR (CONCAT(B.`street_1`, ' ', B.`street_2`, ' ', B.`city`, ' ', B.`city`, ' ', B.`state`, ' ', B.`zip`, ' ', B.`country`)) like '%{$search}%')";
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%'";
            $search = " AND CONCAT(A.first_name, ' ', A.`last_name`) LIKE '%{$search}%'";
        }
        $query = "SELECT 
                        concat(A.first_name, ' ', A.`last_name`) as user_name, 
                        A.id,
                        A.user_id,
                        A.email,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img,      
                        (SELECT COUNT(id) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_count,
                        (SELECT SUM(total_amount) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_sum,
                        (SELECT (CONCAT( B.`city`, ', ', B.`country`)) FROM `customer_address` as B where A.`id`=B.customer_id LIMIT 1) AS address,
                        (SELECT B.`country_iso` FROM `customer_address` AS B WHERE A.`id`=B.customer_id LIMIT 1) AS country_code
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} {$connection_query} AND A.visible = 'Yes' $search $orderby_str limit {$start}, {$length}";
        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(A.id) as total_count     
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} {$connection_query} AND A.visible = 'Yes' $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        $total_customer_5_star = ceil($count / 100 * 10);
        $ranking_model = $connection->createCommand('SELECT `orderSum`.* FROM `customer` Left JOIN
                    (SELECT `customer_id`, SUM(total_amount) as order_total FROM `order` GROUP BY `customer_id`)
                    `orderSum` ON orderSum.customer_id = customer.id  ORDER BY order_total DESC limit 0, '.$total_customer_5_star);
        $ranking_data = $ranking_model->queryAll();
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];


            $sigleData['0'] = "<div class='be-checkbox'><input name='people_check' id='ck{$index}' value='{$single['id']}' type='checkbox' data-parsley-multiple='groups' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='people_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/people/view?id={$single['id']}'>{$single['user_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['2'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";

            //*******code for country flags starts here******
            $address = $single['address'];
            if($address[0]==",")
                $address = str_replace(", ", "", $address);
            $country_codess = strtolower($single['country_code']);
            if ($country_codess == 'usa') {
                $country_codess = 'us';
            }
            $address = str_replace("USA", "United States", $address);
//            $address = str_replace(", US", ", United States", $address);
//            if ($address == 'US') {
//                $address = "United States";
//            }
            $sigleData['3'] = "<span class='flag-icon flag-icon-{$country_codess}'></span><span class='country_names'>$address</span>";
            $star_rate = 1;
            if($single['email']!=''){
                $star_rate=2;
            }
            if($single['order_count']>=1 && $single['order_count']<=10){
                $star_rate=3;
            }
            if($single['order_count']>10){
                $star_rate=4;
            }

            foreach($ranking_data as $_clv_data){
                if($_clv_data['customer_id']==$single['id']){
                    $order_total = $_clv_data['order_total'];
                    if($order_total>=5){
                        $star_rate=5;
                    }
                    break;
                }
            }
            $sigleData['4'] = ($star_rate > 0 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 1 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 2 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 3 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 4 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>");

            $clv = $single['order_sum'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['5'] = $currency_symbol . $clv;
            $sigleData['6'] = $single['order_count'];
            $result[] = $sigleData;

        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionView($id) {
        $data = $this->findModel($id);
        $user_id=Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $total_cus = Customer::find()->Where(['user_id' => $user_id, 'visible' => Customer::VISIBLE_YES])->count();

        $star_rate=ChannelsController::getStartRating($data->id,$data->email,$total_cus);
	
        return $this->render('view', [
                    'model' => $data,
                    'starRate' => $star_rate,
        ]);
    }

    public function actionConnectedCustomer() {
        $currentUserId = Yii::$app->user->identity->id;;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $currentUserId = Yii::$app->user->identity->parent_id;
        }
        $user_connection_id = $_GET['u'];
        $userConnection = UserConnection::findOne(['id'=>$user_connection_id, 'user_id'=>$currentUserId]);
        return $this->render('connectedcustomer', [
            'label' => $userConnection->getPublicName(),
            'connection_id' => $userConnection->id,
        ]);
    }

    public function actionConnnectedCustomerajax()
    {
        $user_id = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $connection_id = $_GET['connection_id'];
        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';
        $orderby_str = '';
        if ($orderby == 1) {
            $orderby_str = "ORDER BY user_name {$asc}";
        }elseif ($orderby == 2) {
            $orderby_str = "ORDER BY ch_img {$asc}";
        }elseif ($orderby == 3) {
            $orderby_str = "ORDER BY address {$asc}";
        } elseif ($orderby == 4) {
        } elseif ($orderby == 5) {
            $orderby_str = "ORDER BY order_sum {$asc}";
        } elseif ($orderby == 6) {
            $orderby_str = "ORDER BY order_count {$asc}";
        } else {
            $orderby_str = '';
        }

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
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%' OR (CONCAT(B.`street_1`, ' ', B.`street_2`, ' ', B.`city`, ' ', B.`city`, ' ', B.`state`, ' ', B.`zip`, ' ', B.`country`)) like '%{$search}%')";
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%'";
            $search = " AND CONCAT(A.first_name, ' ', A.`last_name`) LIKE '%{$search}%'";
        }
        $query = "SELECT 
                        concat(A.first_name, ' ', A.`last_name`) as user_name, 
                        A.id,
                        A.user_id,
                        A.email,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img,      
                        (SELECT COUNT(id) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_count,
                        (SELECT SUM(total_amount) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_sum,
                        (SELECT (CONCAT( B.`city`, ', ', B.`country`)) FROM `customer_address` as B where A.`id`=B.customer_id LIMIT 1) AS address,
                        (SELECT B.`country_iso` FROM `customer_address` AS B WHERE A.`id`=B.customer_id LIMIT 1) AS country_code
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} AND A.visible = 'Yes' And user_connection_id = {$connection_id} $search $orderby_str limit {$start}, {$length}";
        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(A.id) as total_count     
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} AND A.visible = 'Yes' And user_connection_id = {$connection_id} $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        $total_customer_5_star = ceil($count / 100 * 10);
        $ranking_model = $connection->createCommand('SELECT `orderSum`.* FROM `customer` Left JOIN
                    (SELECT `customer_id`, SUM(total_amount) as order_total FROM `order` GROUP BY `customer_id`)
                    `orderSum` ON orderSum.customer_id = customer.id  ORDER BY order_total DESC limit 0, '.$total_customer_5_star);
        $ranking_data = $ranking_model->queryAll();
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];


            $sigleData['0'] = "<div class='be-checkbox'><input name='people_check' id='ck{$index}' value='{$single['id']}' type='checkbox' data-parsley-multiple='groups' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='people_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/people/view?id={$single['id']}'>{$single['user_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['2'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";

            //*******code for country flags starts here******
            $address = $single['address'];
            if($address[0]==",")
                $address = str_replace(", ", "", $address);
            $country_codess = strtolower($single['country_code']);
            if ($country_codess == 'usa') {
                $country_codess = 'us';
            }
            $address = str_replace("USA", "United States", $address);
//            $address = str_replace(", US", "United States", $address);
//            if ($address == 'US') {
//                $address = "United States";
//            }
            $sigleData['3'] = "<span class='flag-icon flag-icon-{$country_codess}'></span><span class='country_names'>$address</span>";
            $star_rate = 1;
            if($single['email']!=''){
                $star_rate=2;
            }
            if($single['order_count']>=1 && $single['order_count']<=10){
                $star_rate=3;
            }
            if($single['order_count']>10){
                $star_rate=4;
            }

            foreach($ranking_data as $_clv_data){
                if($_clv_data['customer_id']==$single['id']){
                    $order_total = $_clv_data['order_total'];
                    if($order_total>=5){
                        $star_rate=5;
                    }
                    break;
                }
            }
            $sigleData['4'] = ($star_rate > 0 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 1 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 2 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 3 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 4 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>");

            $clv = $single['order_sum'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['5'] = $currency_symbol . $clv;
            $sigleData['6'] = $single['order_count'];
            $result[] = $sigleData;

        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionInactiveCustomers() {
        $currentUserId = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $currentUserId = Yii::$app->user->identity->parent_id;
        }
        $user_connection_id = $_GET['u'];
        $userConnection = UserConnection::findOne(['id'=>$user_connection_id, 'user_id'=>$currentUserId]);

        return $this->render('inactivecustomer', [
            'label' => $userConnection->getPublicName(),
            'connection_id' => $userConnection->id,
        ]);
    }

    public function actionInactiveCustomerajax()
    {
        $user_id = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $connection_id = $_GET['connection_id'];
        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';
        $orderby_str = '';
        if ($orderby == 1) {
            $orderby_str = "ORDER BY user_name {$asc}";
        }elseif ($orderby == 2) {
            $orderby_str = "ORDER BY ch_img {$asc}";
        }elseif ($orderby == 3) {
            $orderby_str = "ORDER BY address {$asc}";
        } elseif ($orderby == 4) {
        } elseif ($orderby == 5) {
            $orderby_str = "ORDER BY order_sum {$asc}";
        } elseif ($orderby == 6) {
            $orderby_str = "ORDER BY order_count {$asc}";
        } else {
            $orderby_str = '';
        }

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
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%' OR (CONCAT(B.`street_1`, ' ', B.`street_2`, ' ', B.`city`, ' ', B.`city`, ' ', B.`state`, ' ', B.`zip`, ' ', B.`country`)) like '%{$search}%')";
//            $search = "And (A.first_name like '%{$search}%' OR A.last_name like '%{$search}%'";
            $search = " AND CONCAT(A.first_name, ' ', A.`last_name`) LIKE '%{$search}%'";
        }
        $query = "SELECT 
                        concat(A.first_name, ' ', A.`last_name`) as user_name, 
                        A.id,
                        A.user_id,
                        A.email,
                        (SELECT `connection`.`image_url` FROM  `connection`, `user_connection` WHERE `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id LIMIT 1) AS ch_img,
                        (SELECT `connection_parent`.`image_url` FROM  `connection_parent`, `connection`, `user_connection` WHERE `connection`.`parent_id`>0 AND `user_connection`.`connection_id` = `connection`.`id` AND `user_connection`.`id` = A.user_connection_id AND `connection_parent`.id = `connection`.`parent_id` LIMIT 1) AS ch_parent_img,      
                        (SELECT COUNT(id) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_count,
                        (SELECT SUM(total_amount) FROM `order` WHERE `order`.`customer_id` = A.`id`) AS order_sum,
                        (SELECT (CONCAT( B.`city`, ', ', B.`country`)) FROM `customer_address` as B where A.`id`=B.customer_id LIMIT 1) AS address,
                        (SELECT B.`country_iso` FROM `customer_address` AS B WHERE A.`id`=B.customer_id LIMIT 1) AS country_code
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} AND A.visible = 'No' And user_connection_id = {$connection_id} $search $orderby_str {$asc} limit {$start}, {$length}";

        $model = $connection->createCommand($query);
        $clv_data = $model->queryAll();
        $result = [];
        $index = 0;
        $query = "SELECT count(A.id) as total_count     
                    FROM 
                        `customer` AS A 
                    WHERE A.user_id = {$user_id} AND A.visible = 'No' And user_connection_id = {$connection_id} $search";
        $count_model = $connection->createCommand($query)->queryOne();
        $count = $count_model['total_count'];
        $total_customer_5_star = ceil($count / 100 * 10);
        $ranking_model = $connection->createCommand('SELECT `orderSum`.* FROM `customer` Left JOIN
                    (SELECT `customer_id`, SUM(total_amount) as order_total FROM `order` GROUP BY `customer_id`)
                    `orderSum` ON orderSum.customer_id = customer.id  ORDER BY order_total DESC limit 0, '.$total_customer_5_star);
        $ranking_data = $ranking_model->queryAll();
        foreach ($clv_data as $single){
            $index++;
            $sigleData = [];


            $sigleData['0'] = "<div class='be-checkbox'><input name='people_check' id='ck{$index}' value='{$single['id']}' type='checkbox' data-parsley-multiple='groups' data-parsley-mincheck='2' data-parsley-errors-container='#error-container1' class='people_row_check'><label class='getId' for='ck{$index}'></label></div>";
            $sigleData['1'] = "<a href ='/people/view?id={$single['id']}'>{$single['user_name']}</a>";
            $ch_img = $single['ch_img'] ? $single['ch_img'] : $single['ch_parent_img'];
            $sigleData['2'] = "<div class='channel_scrolling'><img class='ch_img' src='{$ch_img}' width='50' height='50'><lable class='magento-store-name'></lable></div>";

            //*******code for country flags starts here******
            $address = $single['address'];
            if($address[0]==",")
                $address = str_replace(", ", "", $address);
            $country_codess = strtolower($single['country_code']);
            if ($country_codess == 'usa') {
                $country_codess = 'us';
            }
            $address = str_replace("USA", "United States", $address);
//            $address = str_replace(", US", "United States", $address);
//            if ($address == 'US') {
//                $address = "United States";
//            }
            $sigleData['3'] = "<span class='flag-icon flag-icon-{$country_codess}'></span><span class='country_names'>$address</span>";
            $star_rate = 1;
            if($single['email']!=''){
                $star_rate=2;
            }
            if($single['order_count']>=1 && $single['order_count']<=10){
                $star_rate=3;
            }
            if($single['order_count']>10){
                $star_rate=4;
            }

            foreach($ranking_data as $_clv_data){
                if($_clv_data['customer_id']==$single['id']){
                    $order_total = $_clv_data['order_total'];
                    if($order_total>=5){
                        $star_rate=5;
                    }
                    break;
                }
            }
            $sigleData['4'] = ($star_rate > 0 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 1 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 2 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 3 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>") .
                ($star_rate > 4 ? "<span class='mdi mdi-star yellow'></span>" : "<span class='mdi mdi-star black'></span>");

            $clv = $single['order_sum'];
            //$clv = $clv * $conversion_rate;
            $clv = number_format((float) $clv, 2, '.', '');
            $sigleData['5'] = $currency_symbol . $clv;
            $sigleData['6'] = $single['order_count'];
            $result[] = $sigleData;

        }
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $result);
        echo json_encode($response_arr);
    }

    public function actionCreate() {
        $model = new Customer();
        $currentUserId = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $currentUserId = Yii::$app->user->identity->parent_id;
        }
        //$connected_channels = UserConnection::find()->where(['connected' => UserConnection::CONNECTED_YES])->andWhere(['user_id' => $currentUserId])->available()->all();
        $connected_channels = UserConnection::find()->where(['user_id' => $currentUserId])->available()->all();
        $user_connection_id = [];
        foreach ($connected_channels as $user_connection){
            $user_connection_id[] = $user_connection->connection_id;
        }
        if (Yii::$app->request->post()){
            if(isset($_POST['channel_accquired'])){
                $arr_channel_accquired = $_POST['channel_accquired'];
                foreach ($arr_channel_accquired as $channel_accquired){
                    $newModel = new Customer();
                    $newModel->user_id = $currentUserId;
                    $newModel->first_name = $_POST['cu-first-name'];
                    $newModel->last_name = $_POST['cu-last-name'];
                    $newModel->email = $_POST['cu-email'];
                    $newModel->dob = $_POST['cu-DOB'];
                    $newModel->gender = $_POST['cu-Gender'];
                    $newModel->phone = $_POST['cu-Phone-Number'];
                    $newModel->visible = Customer::VISIBLE_YES;
                    $newModel->user_connection_id = $channel_accquired;
                    $newModel->save(false);
                    $newAddress = new CustomerAddress();
                    $newAddress->customer_id = $newModel->id;
                    $newAddress->first_name = $newModel->first_name;
                    $newAddress->last_name = $newModel->last_name;
                    $newAddress->company = "";
                    $newAddress->country = $_POST['cu-ship-Country'];
                    $country_iso = Country::find()->where(['name' => $_POST['cu-ship-Country']])->one();
                    $newAddress->country_iso = $country_iso->sortname;
                    $newAddress->street_1 = $_POST['cu-ship-Street-1'];
                    $newAddress->street_2 = $_POST['cu-ship-Street-2'];
                    $state_name = State::find()->where(['id' => $_POST['cu-State']])->one();
                    $newAddress->state = $state_name->name;
                    $newAddress->city = $_POST['cu-ship-City'];
                    $newAddress->zip = $_POST['cu-ship-Zip'];
                    $newAddress->phone = $_POST['cu-Phone-Number'];
                    $newAddress->address_type = "Default";
                    $newAddress->save(false);
                    Yii::$app->session->setFlash('success', 'Success! Customer has been created.');
                    return $this->redirect(['/people']);
                }
            }
            else{
                Yii::$app->session->setFlash('error', 'Please select the channel!');
                return $this->render('create', [
                    'model' => $model,
                    'user_connections' => $user_connection_id
                ]);
            }
        }
        else{
            return $this->render('create', [
                'model' => $model,
                'user_connections' => $user_connection_id
            ]);
        }
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            //Update Big Commerce Customer//
            $update_customer_bigcommerce = Stores::bigcommerce_update_customer();

            return $this->redirect(['view', 'id' => $model->customer_ID]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    public function actionSavecustomer() {
        $request = Yii::$app->request;
        $userid = Yii::$app->user->identity->id;
        $user_level = Yii::$app->user->identity->level;
        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $userid = Yii::$app->user->identity->parent_id;
        }

        $post_data = Yii::$app->request->post();

        if (Yii::$app->request->post()) {

            $customer_id = Yii::$app->request->post('customer_id');
            $customerdata = Customer::find()->Where(['id' => $customer_id])->one();

            $first_name = Yii::$app->request->post('customer_first_name');
            $last_name = Yii::$app->request->post('customer_last_name');
            $email = Yii::$app->request->post('customer_email_add');
            $phone = Yii::$app->request->post('customer_Phone_no');
            $ship1 = Yii::$app->request->post('ship_street1');
            $ship2 = Yii::$app->request->post('ship_street2');
            $ship_city = Yii::$app->request->post('ship_city');
            $ship_state = Yii::$app->request->post('ship_state');
            $ship_zip = Yii::$app->request->post('ship_zip');
            $ship_country = Yii::$app->request->post('ship_country');
            $customerdata->first_name = $first_name;
            $customerdata->last_name = $last_name;
            $customerdata->email = $email;
            if(Yii::$app->request->post('customer_dob')!="Empty")
                $customerdata->dob = Yii::$app->request->post('customer_dob');
            $customerdata->gender = Yii::$app->request->post('gender');
            $customerdata->phone = $phone;


            if ($customerdata->save(false)) {
                /* Function call For save Billing And Shipping Save */
                $saveCustomerAbb = $this->saveCustomerAbb($customer_id, $post_data);

                $response = ['status' => 'success', 'data' => 'customer info saved'];
                Yii::$app->session->setFlash('success', 'Success! customer info has been updated.');
            } else {

                $response = ['status' => 'error', 'data' => 'customer info not saved'];
                Yii::$app->session->setFlash('danger', 'Error! customer info has not been updated.');
            }
        }
        echo json_encode($response);
        exit;
    }

    public function saveCustomerAbb($customer_id, $post_data) {

        $customerabb = CustomerAddress::find()->Where(['customer_id' => $customer_id])->all();
        /* For billing */
        $connected_bill_street = isset($post_data['bill_street1']) ? $post_data['bill_street1'] : '';
        $connected_bill_street2 = isset($post_data['bill_street2']) ? $post_data['bill_street2'] : '';
        $connected_bill_city = isset($post_data['bill_city']) ? $post_data['bill_city'] : '';
        $connected_bill_state = isset($post_data['bill_state']) ? $post_data['bill_state'] : '';
        $connected_bill_zip = isset($post_data['bill_zip']) ? $post_data['bill_zip'] : '';
        $connected_bill_country = isset($post_data['bill_country']) ? $post_data['bill_country'] : '';

        $connected_ship_street = array_values($post_data['ship_street1'])[0];
        $connected_ship_street2 = array_values($post_data['ship_street2'])[0];
        $connected_ship_city = array_values($post_data['ship_city'])[0];
        $connected_ship_state = array_values($post_data['ship_state'])[0];
        $connected_ship_zip = array_values($post_data['ship_zip'])[0];
        $connected_ship_country = array_values($post_data['ship_country'])[0];

        foreach ($customerabb as $customer_abb_data) {
            $channel_name = $customer_abb_data->customer->userConnection->getPublicName();
            $channel_abb_id = $customer_abb_data->customer->connection_customerId;
            $function = $this->finalSaveCustomerAbb($channel_name, $customer_id, $channel_abb_id, $connected_bill_street, $connected_bill_street2, $connected_bill_city, $connected_bill_state, $connected_bill_zip, $connected_bill_country, $connected_ship_street, $connected_ship_street2, $connected_ship_city, $connected_ship_state, $connected_ship_zip, $connected_ship_country);
        }
        /* End foreach */
    }

    public function finalSaveCustomerAbb($channel_name, $customer_id, $channel_abb_id, $connected_bill_street, $connected_bill_street2, $connected_bill_city, $connected_bill_state, $connected_bill_zip, $connected_bill_country, $connected_ship_street, $connected_ship_street2, $connected_ship_city, $connected_ship_state, $connected_ship_zip, $connected_ship_country) {

//        $final_ship_street = $final_ship_street2 = $final_ship_city = $final_ship_state = $final_ship_zip = $final_ship_country = '';
//        $final_bill_street = $final_bill_street2 = $final_bill_city = $final_bill_state = $final_bill_zip = $final_bill_country = '';
//        if (!empty($connected_bill_street)) {
//            foreach ($connected_bill_street as $bill_street_key => $bill_street_val) {
//                if ($bill_street_key == $channel_name) {
//                    $final_bill_street = $bill_street_val;
//                }
//            }
//        }
//
//        if (!empty($connected_bill_street2)) {
//            foreach ($connected_bill_street2 as $bill_street2_key => $bill_street2_val) {
//                if ($bill_street2_key == $channel_name) {
//                    $final_bill_street2 = $bill_street2_val;
//                }
//            }
//        }
//
//        if (!empty($connected_bill_city)) {
//            foreach ($connected_bill_city as $bill_city_key => $bill_city_val) {
//                if ($bill_city_key == $channel_name) {
//                    $final_bill_city = $bill_city_val;
//                }
//            }
//        }
//
//        if (!empty($connected_bill_state)) {
//            foreach ($connected_bill_state as $bill_state_key => $bill_state_val) {
//                if ($bill_state_key == $channel_name) {
//                    $final_bill_state = $bill_state_val;
//                }
//            }
//        }
//
//        if (!empty($connected_bill_zip)) {
//            foreach ($connected_bill_zip as $bill_zip_key => $bill_zip_val) {
//                if ($bill_zip_key == $channel_name) {
//                    $final_bill_zip = $bill_zip_val;
//                }
//            }
//        }
//
//        if (!empty($connected_bill_country)) {
//            foreach ($connected_bill_country as $bill_country_key => $bill_country_val) {
//                if ($bill_country_key == $channel_name) {
//                    $final_bill_country = $bill_country_val;
//                }
//            }
//        }
//
//        /* For shipping */
//
//        if (!empty($connected_ship_street)) {
//            foreach ($connected_ship_street as $ship_street_key => $ship_street_val) {
//                if ($ship_street_key == $channel_name) {
//                    $final_ship_street = $ship_street_val;
//                }
//            }
//        }
//
//
//        if (!empty($connected_ship_street2)) {
//            foreach ($connected_ship_street2 as $ship_street2_key => $ship_street2_val) {
//                if ($ship_street2_key == $channel_name) {
//                    $final_ship_street2 = $ship_street2_val;
//                }
//            }
//        }
//
//        if (!empty($connected_ship_city)) {
//            foreach ($connected_ship_city as $ship_city_key => $ship_city_val) {
//                if ($ship_city_key == $channel_name) {
//                    $final_ship_city = $ship_city_val;
//                }
//            }
//        }
//
//        if (!empty($connected_ship_state)) {
//            foreach ($connected_ship_state as $ship_state_key => $ship_state_val) {
//                if ($ship_state_key == $channel_name) {
//                    $final_ship_state = $ship_state_val;
//                }
//            }
//        }
//
//        if (!empty($connected_ship_zip)) {
//            foreach ($connected_ship_zip as $ship_zip_key => $ship_zip_val) {
//                if ($ship_zip_key == $channel_name) {
//                    $final_ship_zip = $ship_zip_val;
//                }
//            }
//        }
//
//        if (!empty($connected_ship_country)) {
//            foreach ($connected_ship_country as $ship_country_key => $ship_country_val) {
//                if ($ship_country_key == $channel_name) {
//                    $final_ship_country = $ship_country_val;
//                }
//            }
//        }


        $save_customer_abb_data = CustomerAddress::find()->Where(['customer_id' => $customer_id])->one();

        if (!empty($save_customer_abb_data)) {
            $save_customer_abb_data->street_1 = $connected_ship_street;
            $save_customer_abb_data->street_2 = $connected_ship_street2;
            $save_customer_abb_data->city = $connected_ship_city;
            $save_customer_abb_data->state = $connected_ship_state;
            $save_customer_abb_data->zip = $connected_ship_zip;
            $save_customer_abb_data->country = $connected_ship_country;

            $save_customer_abb_data->save(false);
        }
    }

    public function actionGetCountry() {
        $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : $_REQUEST['term'];
        $sql = "SELECT name FROM countries WHERE name LIKE '{$query}%'";
        $countries = Countries::findBySql($sql)->all();
        $cat_array = array();
        foreach ($countries as $cat) {
            $cat_array[] = $cat->name;
        }
        //RETURN JSON ARRAY
        return json_encode($cat_array);
    }

    public function actionGetCities() {
        $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : $_REQUEST['term'];
        $sql = "SELECT name FROM cities WHERE name LIKE '{$query}%'";
        $cities = Cities::findBySql($sql)->all();
        $cat_array = array();
        foreach ($cities as $cat) {
            $cat_array[] = $cat->name;
        }
        //RETURN JSON ARRAY
        return json_encode($cat_array);
    }

    public function actionGetStates() {
        $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : $_REQUEST['term'];
        $sql = "SELECT name FROM states WHERE name LIKE '{$query}%'";
        $states = States::findBySql($sql)->all();
        $cat_array = array();
        foreach ($states as $cat) {
            $cat_array[] = $cat->name;
        }
        //RETURN JSON ARRAY
        return json_encode($cat_array);
    }

    public function actionDelete($id) {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id) {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAreachartonpeople() {
        if (!empty($_POST['data'])) {
            $post = $_POST['data'];
        }
        $currentmonth = date('m');
        $currentyear = date('Y');
        $current_date = date('Y-m-d', time());
        $currentwday = date('N');
        $currentday = date('d');
        $connectedAll = UserConnection::find()->where(['user_id' => Yii::$app->user->identity->id])->available()->all();
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $connectedAll = UserConnection::find()->where(['user_id' => Yii::$app->user->identity->parent_id])->available()->all();
        }
        $arr = array();
        if (!empty($connectedAll)) {
            $country = '';
            foreach ($connectedAll as $connectedstore) {

                if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
                    $user_permission = Yii::$app->user->identity->getPermission();
                    $permission_channels = explode(", ", $user_permission->channel_permission);
                    if (!in_array($connectedstore->connection_id, $permission_channels)) {
                        continue;
                    }
                }
                $fin_name = $connectedstore->getPublicName();
                /*                 * *********************** FOR TODAY************************ */
                if ($post == 'areacharttoday' || $post == 'areacharttodaymob') {

                    $j = 0;
                    for ($i = 1; $i <= 24; $i++):
                        if ($i == 1) {
                            $current_date_hour = date('Y-m-d h:i:s', time());
                        } else {
                            $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                        }
                        $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                        $previous_hour1 = date('h:i:s', strtotime('-' . $i . ' hour'));
                        $date_check = date('Y-m-d', strtotime($previous_hour));
                        if ($date_check == $current_date):
                            $connection = \Yii::$app->db;
                            $orders_data = $connection->createCommand("select id from customer WHERE user_connection_id={$connectedstore->id}  AND customer_created BETWEEN ('{$previous_hour }') AND ('{$current_date_hour}') AND date(customer_created)='{$current_date}'");
                            $orders_count = count($orders_data->queryAll());
                            $today[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $previous_hour1);
                        // $arr=array_reverse($arr);
                        endif;
                        $j++;
                    endfor;
                }
                /*                 * *********************** FOR WEEKLY************************ */
                if ($post == 'areachartweek' || $post == 'areachartweekmob') {
                    for ($j = 1; $j <=7; $j++):
                        if($j>$currentwday)
                            break;
                        $tmp_day = $currentday - $currentwday + $j;
                        $search_date = date('Y-m-d', strtotime("$currentyear-$currentmonth-$tmp_day"));
                        $disp_date = date('l', strtotime("$currentyear-$currentmonth-$tmp_day"));
                        $orders_data = \Yii::$app->db->createCommand("select id from `customer` WHERE user_connection_id={$connectedstore->id}  AND customer_created LIKE '%{$search_date}%'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }
                /*                 * *********************** FOR MONTHLY************************ */
                if ($post == 'areachartmonth' || $post == 'areachartmonthmob') {
                    for ($j = 1; $j <=31; $j++):
                        if($j>$currentday)
                            break;
                        $search_date = date('Y-m-d', strtotime("$currentyear-$currentmonth-$j"));
                        $orders_data = \Yii::$app->db->createCommand("select id from `customer` WHERE user_connection_id={$connectedstore->id}  AND customer_created LIKE '%{$search_date}%'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $j);
                    endfor;
                }
                /*                 * *********************** FOR QUARTERLY************************ */
                if ($post == 'areachartQuarter' || $post == 'areachartQuartermob') {
                    $quart = floor(($currentmonth-1) / 3);
                    for ($j = 1; $j <=3; $j++):
                        $month_str = $quart * 3 + $j;
                        if($month_str>$currentmonth){
                            break;
                        }
                        $disp_date = date('M', strtotime("$currentyear-$month_str-1"));
                        $orders_data = \Yii::$app->db->createCommand("select id from `customer` WHERE user_connection_id={$connectedstore->id}  AND month(customer_created) = $month_str AND year(customer_created)='{$currentyear}'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }
                /*                 * *********************** FOR YEARLY************************ */
                if ($post == 'areachartyear' || $post == 'areachartyearmob') {
                    for ($j = 1; $j <=12; $j++):
                        if($j>$currentmonth){
                            break;
                        }
                        $disp_date = date('M', strtotime("$currentyear-$j-1"));
                        $orders_data = \Yii::$app->db->createCommand("select id from `customer` WHERE user_connection_id={$connectedstore->id}  AND month(customer_created) = $j AND year(customer_created)='{$currentyear}'");
                        $orders_count = count($orders_data->queryAll());
                        $arr[] = array('name' => $fin_name, 'user' => $orders_count, 'date' => $disp_date);
                    endfor;
                }
                /*                 * *********************** FOR Annual************************ */
                if ($post == 'areachartAnnual' || $post == 'areachartAnnualmob') {
                    $customer = \Yii::$app->db->createCommand("select YEAR(customer_created) as lastyear from `customer` WHERE user_connection_id={$connectedstore->id} order by customer_created")->queryOne();
                    if(!empty($customer)) {
                        for ($j = $customer['lastyear']; $j <= $currentyear; $j++):
                            $connection = \Yii::$app->db;
                            $orders_data = $connection->createCommand("select id from customer WHERE user_connection_id={$connectedstore->id}  AND year(customer_created)='{$j}'");
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
        $data_val = array();
        foreach ($array1 as $_data => $value) {
            foreach ($value as $_value) {
                $data_val['period'] = $_data;
                //$data_val[$_value['name']]=$_value['name'];
                $data_val[$_value['name'] . " Customers"] = $_value['user'];
            }
            $data_val_2[] = $data_val;
        }

        $keyname = array();
        $keyname_2 = array();
        foreach ($data_val as $key => $val) {
            if ($key == 'period') {
                
            } else {
                $keyname[] = $key;
            }
        }
        $keyname_2 = $keyname;

        /*
         * array to store hex color 
         */
        $colorarr = array(
            0 => '#0091ea',
            1 => '#00b0ff',
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
            $strname = trim(str_replace("Customers", "", $color));
            $showcolorhtml .= '<li><span data-color="main-chart-color" class="BigCommerce connected_store_name" style="background-color: ' . $colorarr[$cc++] . ';"></span>' . $strname . '</li>';
        }

        $randcolor_2 = $randcolor;
        $data = array('data' => json_encode($data_val_2), 'ykeyslabels' => json_encode($keyname_2), 'linecolors' => json_encode($randcolor_2), 'showcolorhtml' => $showcolorhtml);
        return \yii\helpers\Json::encode($data);
    }

    public function actionCreatecountryajax() {

        if (!empty($_POST['country_name'])) {

            $country = $_POST['country_name'];
            $country_query = Country::find()->where(['name' => $country])->one();
            $countryID = $country_query->id;

            $states_query = State::find()->where(['country_id' => $countryID])->all();
            foreach ($states_query as $states) {
                echo '<option value="' . $states->id . '">' . $states->name . '</option>';
            }
        }

        if (!empty($_POST['state_ID'])) {
            $state_id = $_POST['state_ID'];

            $city_query = City::find()->where(['state_id' => $state_id])->all();
            foreach ($city_query as $cities) {
                echo '<option value="' . $cities->name . '">' . $cities->name . '</option>';
            }
        }


        if (!empty($_POST['ship_countryName'])) {
            $ship_country = $_POST['ship_countryName'];
            $shipcountry_query = Country::find()->where(['name' => $ship_country])->one();
            $shipCountryID = $shipcountry_query->id;

            $states_query = State::find()->where(['country_id' => $shipCountryID])->all();
            foreach ($states_query as $ship_states) {
                echo '<option value="' . $ship_states->id . '">' . $ship_states->name . '</option>';
            }
        }

        if (!empty($_POST['ship_StateID'])) {

            $ship_stateID = $_POST['ship_StateID'];

            $shipcity_query = City::find()->where(['state_id' => $ship_stateID])->all();
            foreach ($shipcity_query as $shipcity) {
                echo '<option value="' . $shipcity->name . '">' . $shipcity->name . '</option>';
            }
        }
    }

    public function getCustomerStoreImageLogo($customer_id) {
        $checkcustomer = CustomerAbbrivation::find()->Where(['customer_id' => $customer_id])->all();
        $image_data = '';
        $check = 0;
        if (!empty($checkcustomer)) {
            $created_date_array = array();
            foreach ($checkcustomer as $_customer) {
                $channel_name = $_customer->channel_accquired;
                if (count($checkcustomer) == 1) {
                    $image_data = $this->getStoreChannelImageByName($channel_name);
                    return $image_data;
                } else {
                    $check = 1;
                    $store_data = Stores::find()->where(['store_name' => $channel_name])->one();
                    if (!empty($store_data)) {
                        $store_id = $store_data->store_id;
                        $store_connection = StoresConnection::find()->where(['store_id' => $store_id])->one();
                        if (!empty($store_connection)) {
                            $created_date_array[$channel_name] = strtotime($store_connection->created);
                        }
                    } else {
                        $channel_data = Channels::find()->where(['channel_name' => $channel_name])->one();
                        if (!empty($channel_data)) {
                            $channel_data = $channel_data->channel_ID;
                            $channel_connection = ChannelConnection::find()->where(['channel_id' => $channel_data])->one();
                            if (!empty($channel_connection)) {
                                $created_date_array[$channel_name] = strtotime($channel_connection->created);
                            }
                        }
                    }
                }
            }
            if ($check == 1) {
                asort($created_date_array);
                if (count($created_date_array) > 0) {
                    $newArray = array_keys($created_date_array);
                    $_channel_name = $newArray[0];
                    $image_data = $this->getStoreChannelImageByName($_channel_name);
                    return $image_data;
                }
            }
        }
    }

}
