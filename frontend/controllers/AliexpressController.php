<?php

namespace frontend\controllers;

use common\models\ChannelConnection;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Categories;
use common\models\Products;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\CustomerUser;
use common\models\Notification;
use common\models\ProductCategories;
use common\models\ProductImages;
use common\models\ProductChannel;
use common\models\Orders;
use common\models\Channels;
use common\models\CustomFunction;
use common\models\ProductAbbrivation;
use common\models\CategoryAbbrivation;

use TopClient;
use UserSellerGetRequest;
use SellercatsListGetRequest;
use ItemsOnsaleGetRequest;
use aliexpressBrandcatControlGetRequest;
use ItemSellerGetRequest;
use ItemSkusGetRequest;
use OpenAccountListRequest;
use TradesSoldGetRequest;
use TradeGetRequest;
use LogisticsOrdersDetailGetRequest;


class AliexpressController extends \yii\web\Controller
{
    public $aliexpress_app_key = "";
    public $aliexpress_app_secret = "";
    public $authURL = 'https://oauth.tbsandbox.com';
    public $gatewayURL = 'http://gw.api.alibaba.com';
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','login', 'test', 'vtex-importing', 'get-vtex-warehouses', 'change-product-price', 'change-vtex-product-qty'],
                'rules' => [
                    [
                        /* action hit without log-in */
                        'actions' => ['login', 'test', 'vtex-importing', 'get-vtex-warehouses', 'change-product-price', 'change-vtex-product-qty'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        /* action hit only with log-in */
                        'actions' => ['index', 'test', 'vtex-importing', 'get-vtex-warehouses', 'change-product-price', 'change-vtex-product-qty'],
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Authentication of aliexpress form
     */

    public function actionGetAuthorizecode() {
        return $this->render('authorized');
    }

    public function actionAuthToken() {
        $refresh_token = $_GET["code"];
        $url = "https://gw.api.alibaba.com/openapi/http/1/system.oauth2/getToken/".$_SESSION['aliexpress_api_key'];
        $sessionKey = $this->aliexpressCurl($url, $_SESSION["aliexpress_api_key"], $_SESSION["aliexpress_digital_signature"], $refresh_token);

        if(!isset($sessionKey["access_token"])) {
            return $this->render("index", array('param' => "Something went wrong. Please try again!"));
        }
        $session = $sessionKey["access_token"];
        $_SESSION['sessionKey'] = $session;

        $user_id = $_SESSION['user_id'];

        $user = User::find()->where(['id' => $user_id])->one();
        $user_domain = $user->domain_name;

        $channel = Channels::find()->select( 'channel_ID')->where(['channel_name' => 'AliExpress'])->one();
        $aliexpress_channel_id = $channel->channel_ID;
        $checkConnection = ChannelConnection::find()->where(['channel_id' => $aliexpress_channel_id, 'user_id' => $user_id])->one();
        if (empty($checkConnection))
        {
            $connectionModel = new ChannelConnection();
            $connectionModel->channel_id = $aliexpress_channel_id;
            $connectionModel->user_id = $user_id;
            $connectionModel->connected = 'Yes';
            $connectionModel->aliexpress_api_key = $_SESSION["aliexpress_api_key"];
            $connectionModel->aliexpress_tracking_id = $_SESSION['aliexpress_tracking_id'];
            $connectionModel->aliexpress_digital_signature = $_SESSION["aliexpress_digital_signature"];
            $created_date = date('Y-m-d h:i:s', time());
            $connectionModel->created = $created_date;
            $connectionModel->save();
        }

        //return $this->redirect(['aliexpress/index', 'param'=>'success']);
        return $this->render('index', ['param'=>'aliexpress_success']);

    }

    public function actionAuthAliexpress()
    {
        //echo "<pre>"; print_r($_POST); die('End here!');
        $array_msg = array();
        $_SESSION["aliexpress_api_key"] = $_POST['aliexpress_api_key'];
        $_SESSION["aliexpress_tracking_id"] = $_POST['aliexpress_tracking_id'];
        $_SESSION["aliexpress_digital_signature"] = $_POST['aliexpress_digital_signature'];
        $_SESSION["redirect_uri"] = $_POST['redirect_url'];
        $_SESSION["user_id"] = $_POST["user_id"];

        $url = 'http://gw.api.alibaba.com/openapi';
        $appKey = $_SESSION["aliexpress_api_key"];
        $appSecret = $_SESSION["aliexpress_digital_signature"];
        $redirectURL = $_POST['redirect_url'];
        $apiInfo = 'param2/1/aliexpress.open/currentTime/'.$appKey; // replace here with a specific api


        // configuration parameters, please use apiInfo corresponding api parameters to replace
        $code_arr = array(
            'client_id' => $_SESSION['aliexpress_api_key'],
            'redirect_uri' => $redirectURL,
            'site' => 'aliexpress'
        );
        $aliParams = array ();
        foreach ($code_arr as $key => $val) {
            $aliParams[] = $key.$val;
        }
        sort ($aliParams);
        $sign_str = join ('', $aliParams);
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));

        $_SESSION['code_sign'] = $code_sign;
        return $code_sign;
    }

    /**
     * Curl function for aliexpress
     */

    public function aliexpressCurl($url, $appkey, $appsecret, $refresh_token) {

        $data = "grant_type=authorization_code&need_refresh_token=true&client_id=".$appkey."&client_secret=".$appsecret."&redirect_uri=".$_SESSION['redirect_uri']."&code=".$refresh_token;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array('Content-Type:application/x-www-form-urlencoded')
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #: " . $err;
        } else {
            return json_decode($response, true);
        }
    }

    public function actionAliexpressImporting()
    {
        $user_id = $_GET['user_id'];
        $get_users = User::find()->select('domain_name,company_name,email')->where(['id' => $user_id])->one();
        $email = $get_users->email;
        $company_name = $get_users->company_name;
        $channel_connection = ChannelConnection::find()->where(['user_id' => $user_id])->all();
        if(!empty($channel_connection)) {
            $get_aliexpress_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'AliExpress'])->one();
            $aliexpress_channel_id = $get_aliexpress_id->channel_ID;
            $channel_aliexpress = ChannelConnection::find()->where(['channel_id' => $aliexpress_channel_id, 'user_id' => $user_id])->one();
            $aliexpress_api_key = $channel_aliexpress->aliexpress_api_key;
            $aliexpress_tracking_id = $channel_aliexpress->aliexpress_tracking_id;
            $aliexpress_digital_signature = $channel_aliexpress->aliexpress_digital_signature;
            $category_response = $this->getAliExpressCategories($aliexpress_api_key, $aliexpress_digital_signature, $_SESSION['sessionKey']);

            if(!is_scalar($category_response)) {
                $this->aliexpressCategoryImporting($category_response, $user_id);
//                $res = $this->aliexpressProductImporting($aliexpress_api_key, $aliexpress_digital_signature, $user_id);
//                print_r( $res ); die;
//                $this->aliexpressCustomerImporting($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id);
//                $this->aliexpressOrderImporting($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id);

//                $this->aliexpressProductImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id);
//                $this->aliexpressCustomerImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id);
//                $result = $this->aliexpressOrderImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id);
//                echo $result;

                $email_message = 'Success, Your aliexpress Channel is Now Connected';
                $send_email_notif = CustomFunction::ConnectBigCommerceEmail($email, $company_name, $email_message);

                $notif_type = 'AliExpress';
                $notif_db = Notification::find()->where(['notif_type' => $notif_type])->one();
                if (empty($notif_db)):
                    $notification_model = new Notification();
                    $notification_model->user_id = $user_id;
                    $notification_model->notif_type = $notif_type;
                    $notification_model->notif_description = 'Your aliexpress channel data has been successfully imported.';
                    $notification_model->created_at = date('Y-m-d h:i:s', time());
                    $notification_model->save(false);
                endif;

                $get_rec = ChannelConnection::find()->where(['user_id' => $user_id, 'channel_id' => $aliexpress_channel_id])->one();
                $get_rec->import_status = 'Completed';
                $get_rec->save(false);
            }
        }

    }

    public function getAliExpressCategories($appKey, $appSecret, $token) {

        // configuration parameters, please use apiInfo corresponding api parameters to replace
        $code_arr = array(
            'access_token' => $token
        );
        $aliParams = array ();
        $apiInfo = 'param2/1/aliexpress.open/api.getChildrenPostCategoryById/'.$appKey;
        foreach ($code_arr as $key => $val) {
            $aliParams[] = $key.$val;
        }
        sort ($aliParams);
        $sign_str = join ('', $aliParams);
        $sign_str = $apiInfo.$sign_str;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));

        $url = 'http://gw.api.alibaba.com/openapi/param2/1/aliexpress.open/api.getChildrenPostCategoryById/'.$appKey.'?access_token='.$token.'&_aop_signature='.$code_sign;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // execute!
        $response = curl_exec($ch);
        $response = json_decode($response, true);

        curl_close($ch);

        return $response;
    }

    public function aliexpressCategoryImporting($category_list, $user_id)
    {
        $category_list = $category_list["aeopPostCategoryList"];
        foreach($category_list as $cat)
        {
            $cat_id = $cat['id'];
            $cat_name = $cat['names']['en'];
            $checkCategoryName = Categories::find()->where(['category_name' => $cat_name])->one();
            if(empty($checkCategoryName))
            {
                $categoryModel = new Categories();
                $categoryModel->channel_abb_id = '';
                $categoryModel->category_name = $cat_name;
                $categoryModel->parent_category_ID = 0;
                $categoryModel->elliot_user_id = $user_id;
                $created_date = date('Y-m-d h:i:s', time());
                $categoryModel->created_at = $created_date;
                if($categoryModel->save())
                {
                    $CategoryAbbrivation = new CategoryAbbrivation();
                    $CategoryAbbrivation->channel_abb_id = 'AE'.$cat_id;
                    $CategoryAbbrivation->category_ID = $categoryModel->category_ID;
                    $CategoryAbbrivation->channel_accquired = 'AliExpress';
                    $CategoryAbbrivation->created_at = date('Y-m-d H:i:s', time());
                    $CategoryAbbrivation->save(false);
                }
            }
            else
            {
                $CategoryAbbrivation = new CategoryAbbrivation();
                $CategoryAbbrivation->channel_abb_id = 'AE'.$cat_id;
                $CategoryAbbrivation->category_ID = $checkCategoryName->category_ID;
                $CategoryAbbrivation->channel_accquired = 'AliExpress';
                $CategoryAbbrivation->created_at = date('Y-m-d H:i:s', time());
                $CategoryAbbrivation->save(false);
            }

        }
    }

    public function aliexpressProductImporting($appKey, $appSecret, $user_id) {

        $token = $_SESSION['sessionKey'];

        $code_arr = array(
            'access_token' => $token,
            'productStatusType' => 'onSelling'
        );
        $aliParams = array ();
        $apiInfo = 'param2/1/aliexpress.open/api.findProductInfoListQuery/'.$appKey;
        foreach ($code_arr as $key => $val) {
            $aliParams[] = $key.$val;
        }
        sort ($aliParams);
        $sign_str = join ('', $aliParams);
        $sign_str = $apiInfo.$sign_str;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));

        $url = 'http://gw.api.alibaba.com/openapi/param2/1/aliexpress.open/api.findProductInfoListQuery/'.$appKey.'?access_token='.$token.'&_aop_signature='.$code_sign.'&productStatusType=onSelling';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // execute!
        $response = curl_exec($ch);
        $response = json_decode($response, true);

        curl_close($ch);

        return $response;

        $category_list = CategoryAbbrivation::find()->select('channel_abb_id')->where(['channel_accquired'=>'AliExpress'])->all();
        $product_ids = array();

        $session = $_SESSION['sessionKey'];

        foreach($category_list as $cat)
        {
            $cat_id = substr($cat->channel_abb_id, 2);
            $_from = 1;
            $_to = 50;

            $req = new ItemsOnsaleGetRequest;
            $req->setFields('num_iid, title, price');
            //$req->setCid($cat_id);

            $response = $productTOP->execute($req, $session);
            $response = json_decode(json_encode($response), true);
            if($response['msg'] != '' || $response['msg'] != null) {
                echo json_encode($response);
                return;
            }
            $product_data = $response['items']['item'];
            $total_product_count = sizeof($product_data);
            //$total_loop = ceil($total_product_count / 50);
            for($pro = 0; $pro < $total_product_count; $pro++) {
                $product = $product_data[$pro];
                $product_ids[] = $product["num_iid"];
            }
        }

        $brandTOP = new TopClient();
        $brandTOP->appkey = $aliexpress_app_key;
        $brandTOP->secretKey = $aliexpress_app_secret;
        $req = new aliexpressBrandcatControlGetRequest;
        $resp = $brandTOP->execute($req, $session);
        $brand_list = json_decode(json_encode($resp), true);

        $this->productInsert($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $product_ids, $brand_list, $user_id);
    }

    public function productInsert($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $product_ids, $brand_list, $user_id)
    {
        if(isset($_SESSION['sessionKey'])) $session = $_SESSION['sessionKey'];
        else $session = '6202608917f73045ZZ1e3902f90b9689d303bb1dee58c9f2054718371';

        $product_dataTOP = new TopClient();
        $product_dataTOP->appkey = $aliexpress_app_key;
        $product_dataTOP->secretKey = $aliexpress_app_secret;

        sort($product_ids);
        $cc = 1;
        foreach($product_ids as $pid)
        {
            $cc++;
            $req = new ItemSellerGetRequest;
            $req->setFields("num_iid,product_id,title,cid,desc,nick,price,approve_status,skus,outer_id,list_time");
            $req->setNumIid($pid);
            $resp = $product_dataTOP->execute($req, $session);
            $product_data = json_decode(json_encode($resp), true);

            $product_id = $product_data['item']['product_id'];
            $prefix_product_id = 'TM'.$product_id;
            $name = $product_data['item']['title'];
            $category_id = $product_data['item']['cid'];
            //$description = $product_data['item']['desc'];
            $created_at = date("Y-m-d H:i:s", strtotime($product_data['item']['list_time']));
            $product_visible_status = 1;
            //$product_active_status = $product_data['IsActive'];
            $brand_name = '';

//            foreach($brand_list as $_brand)
//            {
//                if($_brand['id']==$brand_id)
//                {
//                    $brand_name = $_brand['name'];
//                }
//            }

            //$product_sku_data = $product_data['item']['skus'];
            //$product_outer_id = $product_data['item']['outer_id'];
            //print_r($product_data); return;

            $req = new ItemSkusGetRequest;
            $req->setFields("sku_id,num_iid");
            $req->setNumIids($pid);
            $resp = $product_dataTOP->execute($req, $session);
            $product_sku_data = json_decode(json_encode($resp), true);

            $count = 1;
            foreach($product_sku_data as $_product_sku)
            {
                $sku_id = $_product_sku['sku_id'];
                $sku_name = $_product_sku['properties_name'];
                $product_price = $_product_sku['price'];
                //$product_image_url = $_product_sku['image'];
                $product_image_url = '1.png';
                $product_stock = $_product_sku['with_hold_quantity'];
                $product_qty = $_product_sku['quantity'];
                //$product_weight = $_product_sku['measures']['cubicweight'];
                $product_weight = 0;
                if($count == 1)
                {
                    $checkProductModel = Products::find()->where(['product_name' => $sku_name, 'sku' => $sku_id])->one();
                    if (empty($checkProductModel))
                    {
                        //Create Model for Each new Product
                        $productModel = new Products ($user_id);
                        $productModel->channel_abb_id = "";
                        $productModel->product_name = $sku_name;
                        $productModel->SKU = $sku_id;
                        $productModel->UPC = '';
                        $productModel->EAN = '';
                        $productModel->Jan = '';
                        $productModel->ISBN = '';
                        $productModel->MPN = '';
                        //$productModel->description = $description;
                        $productModel->adult = 'no';
                        $productModel->age_group = NULL;
                        $productModel->gender = 'Unisex';
                        if ($product_stock > 0):
                            $productModel->availability = 'In Stock';
                        else:
                            $productModel->availability = 'Out of Stock';
                        endif;
                        $productModel->brand = $brand_name;
                        $productModel->condition = 'New';
                        $productModel->weight = $product_weight;
                        $productModel->stock_quantity = $product_qty;
                        if ($product_stock > 0):
                            $productModel->stock_level = 'In Stock';
                        else:
                            $productModel->stock_level = 'Out of Stock';
                        endif;
                        if ($product_visible_status == 1):
                            $productModel->stock_status = 'Visible';
                        else:
                            $productModel->stock_status = 'Hidden';
                        endif;
                        $productModel->price = $product_price;
                        $productModel->sales_price = $product_price;
                        $productModel->created_at = $created_at;
                        $productModel->updated_at = date('Y-m-d h:i:s', time());
                        //Save Elliot User id
                        $productModel->elliot_user_id = $user_id;
                        if ($productModel->save(false)):
                            /* Save Product Abbrivation Id */
                            $last_pro_id = $productModel->id;
                            $product_abberivation = new ProductAbbrivation();
                            $product_abberivation->channel_abb_id = $prefix_product_id;
                            $product_abberivation->product_id = $last_pro_id;
                            $product_abberivation->channel_accquired = 'aliexpress';
                            $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                            $product_abberivation->save(false);
                        endif;

                        if (!empty($category_id)):
                            $category = CategoryAbbrivation::find()->where(['channel_abb_id' => 'TM'.$category_id])->one();
                            if (!empty($category)):
                                $cat_id = $category->category_ID;
                                $productCategoryModel = new ProductCategories;
                                $productCategoryModel->category_ID = $cat_id;
                                $productCategoryModel->product_ID = $last_pro_id;
                                $productCategoryModel->created_at = $created_at;
                                $productCategoryModel->updated_at = date('Y-m-d h:i:s', time());
                                //Save Elliot User id
                                $productCategoryModel->elliot_user_id = $user_id;
                                $productCategoryModel->save(false);
                            endif;
                        endif;
                        //Product Images Table Entry
                        if ($product_image_url!=''):
                            $productImageModel = new ProductImages;
                            $productImageModel->product_ID = $last_pro_id;
                            $productImageModel->label = '';
                            $productImageModel->link = $product_image_url;
                            $productImageModel->default_image = 'Yes';
                            $productImageModel->image_status = 1;
                            $productImageModel->created_at = $created_at;
                            $productImageModel->updated_at = date('Y-m-d h:i:s', time());
                            $productImageModel->save(false);
                        endif;

                        $get_aliexpress_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                        $aliexpress_channel_id = $get_aliexpress_id->channel_ID;

                        $productChannelModel = new ProductChannel;
                        $productChannelModel->channel_id = $aliexpress_channel_id;
                        $productChannelModel->product_id = $last_pro_id;
                        $productChannelModel->created_at = $created_at;
                        $productChannelModel->status = 'yes';
                        $productChannelModel->updated_at = date('Y-m-d h:i:s', time());
                        $productChannelModel->save(false);
                    }
                    else
                    {
                        $check_abbrivation =  ProductAbbrivation::find()->where(['channel_abb_id' => $prefix_product_id])->one();
                        if(empty($check_abbrivation))
                        {
                            /* Save Product Abbrivation Id */
                            $product_abberivation = new ProductAbbrivation();
                            $product_abberivation->channel_abb_id = $prefix_product_id;
                            $product_abberivation->product_id = $checkProductModel->id;
                            $product_abberivation->channel_accquired = 'aliexpress';
                            $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                            $product_abberivation->save(false);


                            $get_aliexpress_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                            $aliexpress_channel_id = $get_aliexpress_id->channel_ID;

                            //Product Channel/Store Table Entry
                            $productChannelModel = new ProductChannel;
                            $productChannelModel->channel_id = $aliexpress_channel_id;
                            $productChannelModel->product_id = $checkProductModel->id;
                            $productChannelModel->status = 'yes';
                            $productChannelModel->created_at = date('Y-m-d H:i:s', time());
                            $productChannelModel->save(false);
                        }
                    }
                }
                $count++;
            }

        }
    }

    public function aliexpressOrderImporting($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id) {
        if(isset($_SESSION['sessionKey'])) $session = $_SESSION['sessionKey'];
        else $session = '6202608917f73045ZZ1e3902f90b9689d303bb1dee58c9f2054718371';

        $orderTOP = new TopClient();
        $orderTOP->appkey = $aliexpress_app_key;
        $orderTOP->secretKey = $aliexpress_app_secret;
//        $req = new TradesSoldGetRequest;
//        $req->setFields("tid, type, status, payment, orders, rx_audit_status");
        $req = new LogisticsOrdersDetailGetRequest;
        $req->setFields("tid, order_code, seller_nick, buyer_nick, item_title, receiver_location, status, type, company_name, created, modified, is_quick_cod_order, sub_tids, is_split");
        $resp = $orderTOP->execute($req, $session);
        $order_collection_list = json_decode(json_encode($resp), true);
        if($order_collection_list['total_results'] == 0) {
            echo "There is no results for aliexpress Orders";
            return;
        }
        $order_trades = $order_collection_list['shippings']['shipping'];

        foreach($order_trades as $order_data)
        {
            $order_id = $order_data["tid"];
            $req = new TradeGetRequest;
            $req->setFields("tid, type, status, payment, orders");
            $req->setTid($order_id);
            $resp = $orderTOP->execute($req, $session);
            $order_collection = json_decode(json_encode($resp), true);

            $order_id = $order_collection['tid'];
//            $origin = $order_collection['origin'];
//            $salesChannel = $order_collection['salesChannel'];
            $order_status = $order_collection['status'];
            $order_total = $this->aliexpressPriceFormat($order_collection['price']);
            $order_created_at = date("Y-m-d h:i:s", strtotime($order_collection['created']));
            $order_updated_at = date("Y-m-d h:i:s", strtotime($order_collection['modified']));
            $group_price = $order_collection['total_fee'];

            switch (strtolower($order_status)) {
                case "trade_no_create_pay":
                    $order_status_2 = "Pending";
                    break;
                case "awaiting confirmation":
                    $order_status_2 = 'Pending';
                    break;
                case "payment pending":
                    $order_status_2 = "Pending";
                    break;
                case "payment approved":
                    $order_status_2 = "In Transit";
                    break;
                case "cancellation grace period":
                    $order_status_2 = "Cancel";
                    break;
                case "ready for handling":
                    $order_status_2 = "In Transit";
                    break;
                case "preparing delivery":
                    $order_status_2 = "In Transit";
                    break;
                default:
                    $order_status_2 = "Pending";
            }

            $discount_price = '';
            $shipping_price = '';
            $tax_price = '';
            foreach($group_price as $_price)
            {
                $price_type = $_price['id'];
                if($price_type=='Discounts')
                {
                    $discount_price = $this->aliexpressPriceFormat($_price['value']);
                }
                if($price_type=='Shipping')
                {
                    $shipping_price = $this->aliexpressPriceFormat($_price['value']);
                }
                if($price_type=='Tax')
                {
                    $tax_price = $this->aliexpressPriceFormat($_price['value']);
                }
            }

            $payment_method = $order_collection['paymentData']['transactions'][0]['payments'][0]['paymentSystemName'];

            $customer_data = $order_collection['clientProfileData'];
            $email = $customer_data['email'];
            $firstName = $customer_data['firstName'];
            $lastName = $customer_data['lastName'];
            $phone = $customer_data['phone'];
            $aliexpress_customer_profile_id = $customer_data['userProfileId'];

            $shipping_data = $order_collection['shippingData'];
            $receiver_name = $shipping_data['address']['receiverName'];
            $zip_code = $shipping_data['address']['postalCode'];
            $city = $shipping_data['address']['city'];
            $state = $shipping_data['address']['state'];
            $country = $shipping_data['address']['country'];
            $street = $shipping_data['address']['street'];

            $shipping_address = $street.' '.$city.' '.$state.' '.$zip_code.' '.$country;
            $billing_address = $street.' '.$city.' '.$state.' '.$zip_code.' '.$country;

            $order_items = $order_collection['items'];
            $total_quantity_ordered = '';
            foreach($order_items as $_items)
            {
                $total_quantity_ordered += $_items['quantity'];
            }

            $customerCheckModel = CustomerUser::find()->Where(['channel_abb_id' => 'TM'.$aliexpress_customer_profile_id])->one();
            if(empty($customerCheckModel))
            {
                if($aliexpress_customer_profile_id == '')
                {
                    $aliexpress_customer_profile_id = '0000';
                }
                $customerCheckAnotherModel = CustomerUser::find()->Where(['channel_abb_id' => 'TM'.$aliexpress_customer_profile_id, 'email'=>$email])->one();
                if(empty($customerCheckAnotherModel))
                {
                    $Customers_create_model = new CustomerUser($user_id);
                    $Customers_create_model->channel_abb_id = 'TM'.$aliexpress_customer_profile_id;
                    $Customers_create_model->first_name = $firstName;
                    $Customers_create_model->last_name = $lastName;
                    $Customers_create_model->email = $email;
                    $Customers_create_model->channel_acquired = 'aliexpress';
                    $Customers_create_model->date_acquired = '';
                    $Customers_create_model->created_at = date('Y-m-d h:i:s', time());
                    $Customers_create_model->elliot_user_id = $user_id;
                    $Customers_create_model->save(false);
                    $elliot_customer_id = $Customers_create_model->customer_ID;
                }
                else
                {
                    $elliot_customer_id = $customerCheckAnotherModel->customer_ID;
                }
            }
            else
            {
                $customerCheckModel->street_1 = $street;
                $customerCheckModel->street_2 = '';
                $customerCheckModel->city = $city;
                $customerCheckModel->country = $country;
                $customerCheckModel->state = $state;
                $customerCheckModel->zip = $zip_code;
                $customerCheckModel->phone_number = $phone;
                $customerCheckModel->ship_street_1 = $street;
                $customerCheckModel->ship_street_2 = '';
                $customerCheckModel->ship_city = $city;
                $customerCheckModel->ship_state = $state;
                $customerCheckModel->ship_zip = $zip_code;
                $customerCheckModel->ship_country = $country;
                $customerCheckModel->save(false);
                $elliot_customer_id = $customerCheckModel->customer_ID;
            }

            $check_order = Orders::find()->Where(['channel_abb_id' => 'TM'.$order_id])->one();
            if(empty($check_order))
            {
                $order_model = new Orders($user_id);
                $order_model->elliot_user_id = $user_id;
                $order_model->channel_abb_id = 'TM' . $order_id;
                $order_model->customer_id = $elliot_customer_id;
                $order_model->order_status = $order_status_2;
                $order_model->product_qauntity = $total_quantity_ordered;
                $order_model->shipping_address = $shipping_address;
                $order_model->ship_street_1 = $street;
                $order_model->ship_street_2 = '';
                $order_model->ship_city = $city;
                $order_model->ship_state = $state;
                $order_model->ship_zip = $zip_code;
                $order_model->ship_country = $country;
                $order_model->billing_address = $billing_address;
                $order_model->bill_street_1 = $street;
                $order_model->bill_street_2 = '';
                $order_model->bill_city = $city;
                $order_model->bill_state = $state;
                $order_model->bill_zip = $zip_code;
                $order_model->bill_country = $country;
                $order_model->base_shipping_cost = $shipping_price;
                $order_model->shipping_cost_tax = $tax_price;
                $order_model->payment_method = $payment_method;
                $order_model->payment_status = $order_status_2;
                $order_model->refunded_amount = '';
                $order_model->discount_amount = $discount_price;
                $order_model->total_amount = $order_total;
                $order_model->order_date = $order_created_at;
                $order_model->updated_at = $order_updated_at;
                //$order_model->created_at = date('Y-m-d h:i:s', time());
                $order_model->created_at = $order_created_at;
                $order_model->save(false);
                $last_order_id = $order_model->order_ID;

                $aliexpress_channel = Channels::find()->where(['channel_name' => 'aliexpress'])->one();
                $aliexpress_channel_id = $aliexpress_channel->channel_ID;
                $order_channels_model = new OrderChannel();
                $order_channels_model->elliot_user_id = $user_id;
                $order_channels_model->order_id = $last_order_id;
                $order_channels_model->channel_id = $aliexpress_channel_id;
                $order_channels_model->created_at = date('Y-m-d h:i:s', time());
                $order_channels_model->save(false);
                $order_items = $order_collection['items'];
                foreach($order_items as $_items)
                {
                    $product_id = $_items['productId'];
                    $quantity_ordered = $_items['quantity'];
                    $product_name = $_items['name'];
                    $product_price = $this->aliexpressPriceFormat($_items['price']);
                    $product_sku = '';
//                    if(!is_scalar($product_detail))
//                    {
//                        $product_sku = $product_detail['skus'][0]['sku'];
//                    }
                    $checkProductId = Products::find()->where(['product_name' => $product_name, 'sku' => $product_sku])->one();
                    if(empty($checkProductId))
                    {
                        if($product_id=='')
                        {
                            $product_id = '0000';
                        }
                        $productModel = new Products ($user_id);
                        $productModel->channel_abb_id = '';
                        $productModel->product_name = $product_name;
                        $productModel->SKU = $product_sku;
                        $productModel->UPC = '';
                        $productModel->EAN = '';
                        $productModel->Jan = '';
                        $productModel->ISBN = '';
                        $productModel->MPN = '';
                        $productModel->description = '';
                        $productModel->adult = 'no';
                        $productModel->age_group = NULL;
                        $productModel->gender = 'Unisex';
                        $productModel->brand = '';
                        $productModel->stock_quantity = 1;
                        $productModel->availability = 'Out of Stock';
                        $productModel->stock_level = 'Out of Stock';
                        $productModel->stock_status = 'Visible';
                        $productModel->price = $product_price;
                        $productModel->sales_price = $product_price;
                        $productModel->elliot_user_id = $user_id;
                        $productModel->save(false);
                        $last_pro_id = $productModel->id;

                        $custom_productChannelModel = new ProductChannel;
                        $channel_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                        $custom_productChannelModel->channel_id = $channel_id->channel_ID;
                        $custom_productChannelModel->product_id = $last_pro_id;
                        $custom_productChannelModel->elliot_user_id = $user_id;
                        $custom_productChannelModel->status = 'no';
                        $custom_productChannelModel->created_at = date('Y-m-d h:i:s', time());
                        $custom_productChannelModel->save(false);

                        /* Save Product Abbrivation Id */
                        $product_abberivation = new ProductAbbrivation();
                        $product_abberivation->channel_abb_id = 'TM'.$product_id;
                        $product_abberivation->product_id = $last_pro_id;
                        $product_abberivation->channel_accquired = 'aliexpress';
                        $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                        $product_abberivation->save(false);

                    }
                    else
                    {
                        $last_pro_id = $checkProductId->id;
                    }

                    $order_products_model = new OrdersProducts;
                    $order_products_model->order_Id = $last_order_id;
                    $order_products_model->product_Id = $last_pro_id;
                    $order_products_model->qty = $quantity_ordered;
                    $order_products_model->order_product_sku = $product_sku;
                    $order_products_model->created_at = date('Y-m-d h:i:s', time());
                    $order_products_model->elliot_user_id = $user_id;
                    $order_products_model->save(false);
                }
            }
        }

    }


    public function aliexpressCustomerImporting($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id) {

        $customerTOP = new TopClient();
        $customerTOP->appkey = $aliexpress_app_key;
        $customerTOP->secretKey = $aliexpress_app_secret;
        $req = new OpenAccountListRequest;
        $resp = $customerTOP->execute($req);
        $customer_data = json_decode(json_encode($resp), true);

        if(sizeof($customer_data) > 1) {
            foreach ($customer_data as $customer_list) {
                $email = $customer_list['email'];
                $id = $customer_list['id'];
                $phone = $customer_list['weixin'];
                $frist_name = '';
                $last_name = $customer_list['name'];
                $aliexpress_customer_id = $customer_list['login_id'];
                $customer_created_at = date("Y-m-d h:i:s", strtotime($customer_list['gmt_create']));
                $customer_updated_at = date("Y-m-d h:i:s", strtotime($customer_list['gmt_modified']));
                if ($aliexpress_customer_id == '') {
                    $aliexpress_customer_id = '0000';
                }
                $checkCustomerModel = CustomerUser::find()->where(['channel_abb_id' => 'TM' . $aliexpress_customer_id, 'email' => $email])->one();
                if (empty($checkCustomerModel)) {
                    $Customers_model = new CustomerUser($user_id);
                    $Customers_model->channel_abb_id = 'TM' . $aliexpress_customer_id;
                    $Customers_model->first_name = $frist_name;
                    $Customers_model->last_name = $last_name;
                    $Customers_model->email = $email;
                    $Customers_model->channel_acquired = 'aliexpress';
                    $Customers_model->date_acquired = $customer_created_at;
                    $Customers_model->created_at = $customer_created_at;
                    $Customers_model->updated_at = $customer_updated_at;
                    $Customers_model->elliot_user_id = $user_id;
                    $Customers_model->save(false);
                }
            }
        }
    }

    public function aliexpressPriceFormat($price) {
        return $price;
    }

    public function aliexpressProductImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id) {
        $category_list = CategoryAbbrivation::find()->select('channel_abb_id')->where(['channel_accquired'=>'aliexpress'])->all();
        //echo "<pre>"; print_r($category_list); die('End herer!');
        $count = 1;
        $product_ids = array();

        $productTOP = new TopClient();
        $productTOP->appkey = $aliexpress_app_key;
        $productTOP->secretKey = $aliexpress_app_secret;

        if(isset($_SESSION['sessionKey'])) $session = $_SESSION['sessionKey'];
        else $session = '6202608917f73045ZZ1e3902f90b9689d303bb1dee58c9f2054718371';
        foreach($category_list as $cat)
        {
            $cat_id = substr($cat->channel_abb_id, 2);
            $_from = 1;
            $_to = 50;

            $req = new ItemsOnsaleGetRequest;
            $req->setFields('num_iid, title, price');
            //$req->setCid($cat_id);

            $response = $productTOP->execute($req, $session);
            $response = json_decode(json_encode($response), true);
//            if($response['msg'] != '' || $response['msg'] != null) {
//                echo json_encode($response);
//                return;
//            }
//            $product_data = $response['items']['item'];

            $product_data = array(
                0 => array('num_iid' => '1489161932'),
                1 => array('num_iid' => '1489161933'),
                2 => array('num_iid' => '1489161934'),
                3 => array('num_iid' => '1489161935'),
                4 => array('num_iid' => '1489161936'),
                5 => array('num_iid' => '1489161937'),
                6 => array('num_iid' => '1489161938'),
                7 => array('num_iid' => '1489161939')
            );
            $total_product_count = sizeof($product_data);
            //$total_loop = ceil($total_product_count / 50);
            for($pro = 0; $pro < $total_product_count; $pro++) {
                $product = $product_data[$pro];
                $product_ids[] = $product["num_iid"];
            }
        }

        $brandTOP = new TopClient();
        $brandTOP->appkey = $aliexpress_app_key;
        $brandTOP->secretKey = $aliexpress_app_secret;
        $req = new aliexpressBrandcatControlGetRequest;
        $resp = $brandTOP->execute($req, $session);
        $brand_list = json_decode(json_encode($resp), true);

        $this->productInsert_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $product_ids, $brand_list, $user_id);
    }

    public function productInsert_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $product_ids, $brand_list, $user_id)
    {
        if(isset($_SESSION['sessionKey'])) $session = $_SESSION['sessionKey'];
        else $session = '6202608917f73045ZZ1e3902f90b9689d303bb1dee58c9f2054718371';

        $product_dataTOP = new TopClient();
        $product_dataTOP->appkey = $aliexpress_app_key;
        $product_dataTOP->secretKey = $aliexpress_app_secret;

        sort($product_ids);
        $cc = 0;
        $product_all_data = array(
            0 => array('item' => array('weight'=>0.2, 'quantity'=>7812, 'with_hold_quantity'=>7, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i4/240610147/TB2hMymaHaI.eBjy1XdXXcoqXXa_!!240610147.jpg_430x430q90.jpg', 'sku_id'=>'1432147', 'num_iid' => '1489161932', 'product_id'=>'85883030', 'cid'=>'410087605', 'title' => '奥康商务正装青年真皮英伦大码鞋', 'properties_name'=>'奥康男鞋夏商务正装皮鞋男青年真皮潮鞋婚鞋大码鞋子男英伦德比鞋', 'list_time'=>'2009-10-22 14:22:06', 'price' => 259.00)),
            1 => array('item' => array('weight'=>0.22, 'quantity'=>12498, 'with_hold_quantity'=>0, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i4/215312615/TB2b2LRjYBmpuFjSZFAXXaQ0pXa_!!215312615.jpg_430x430q90.jpg', 'sku_id'=>'1432474', 'num_iid' => '1489161933', 'product_id'=>'85883031', 'cid'=>'410087605', 'title' => '意尔康真皮英伦系带商务休闲男士皮鞋', 'properties_name'=>'意尔康男鞋新款鞋子真皮潮流英伦系带商务休闲皮鞋潮男士休闲鞋男', 'list_time'=>'2009-10-22 14:22:06', 'price' => 299.00)),
            2 => array('item' => array('weight'=>0.24, 'quantity'=>1209, 'with_hold_quantity'=>16, 'product_url'=>'https://img.alicdn.com/imgextra/i3/106852162/TB2ZdRrsFXXXXcIXXXXXXXXXXXX_!!106852162.jpg_430x430q90.jpg', 'sku_id'=>'1432341', 'num_iid' => '1489161934', 'product_id'=>'85883032', 'cid'=>'410087605', 'title' => '热风英伦复古雕花牛皮皮鞋', 'properties_name'=>'热风新款英伦复古雕花皮鞋男 牛皮布洛克男鞋H43M6301', 'list_time'=>'2009-10-22 14:22:06', 'price' => 319.00)),
            3 => array('item' => array('weight'=>0.25, 'quantity'=>4827, 'with_hold_quantity'=>32, 'product_url'=>'https://img.alicdn.com/imgextra/i1/718100145/TB2fGkbagojyKJjy0FaXXakspXa_!!718100145.jpg_430x430q90.jpg', 'sku_id'=>'1432116', 'num_iid' => '1489161935', 'product_id'=>'85883033', 'cid'=>'410087605', 'title' => '名郎秋季黑色时尚韩版男士休闲皮板鞋', 'properties_name'=>'名郎男鞋秋季新款真皮板鞋黑色时尚潮鞋子韩版男士日常休闲皮鞋男', 'list_time'=>'2009-10-22 14:22:06', 'price' => 338.00)),
            4 => array('item' => array('weight'=>0.22, 'quantity'=>1812, 'with_hold_quantity'=>4, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i2/2919350664/TB243P7XVgkyKJjSspoXXcOPpXa_!!2919350664.jpg_430x430q90.jpg', 'sku_id'=>'1567139', 'num_iid' => '1489161936', 'product_id'=>'85883034', 'cid'=>'410087605', 'title' => '秋季男士定制真皮商务英伦皮鞋', 'properties_name'=>'ThomWills秋季男士手工定制真皮正装皮鞋商务西装男鞋牛津鞋英伦', 'list_time'=>'2009-10-22 14:22:06', 'price' => 829.00)),
            5 => array('item' => array('weight'=>0.22, 'quantity'=>16240, 'with_hold_quantity'=>2, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i3/721002017/TB2GYYUrrJmpuFjSZFwXXaE4VXa_!!721002017.jpg_430x430q90.jpg', 'sku_id'=>'1023948', 'num_iid' => '1489161937', 'product_id'=>'85883035', 'cid'=>'410087605', 'title' => 'ECCO爱步商务正装男鞋 舒适缓震时尚', 'properties_name'=>'ECCO爱步商务正装男鞋 舒适缓震时尚烤花精致男士皮鞋 拉夏600824', 'list_time'=>'2009-10-22 14:22:06', 'price' => 2199.00)),
            6 => array('item' => array('weight'=>0.25, 'quantity'=>18247, 'with_hold_quantity'=>0, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i1/579797141/TB2RqVEqxRDOuFjSZFzXXcIipXa_!!579797141.jpg_430x430q90.jpg', 'sku_id'=>'1528307', 'num_iid' => '1489161938', 'product_id'=>'85883036', 'cid'=>'410087605', 'title' => 'regal商务正装固特异英伦男士皮鞋', 'properties_name'=>'REGAL/丽格日本品牌商务正装男鞋固特异英伦风婚鞋男士皮鞋T29B', 'list_time'=>'2009-10-22 14:22:06', 'price' => 2280.00)),
            7 => array('item' => array('weight'=>0.4, 'quantity'=>18249, 'with_hold_quantity'=>2, 'product_url'=>'http://img.alicdn.com/bao/uploaded/i4/1718039157/TB2g1e.atsmyKJjSZFvXXcE.FXa_!!1718039157.jpg_430x430q90.jpg', 'sku_id'=>'1128479', 'num_iid' => '1489161939', 'product_id'=>'85883037', 'cid'=>'410087605', 'title' => 'clarks正装男鞋 Corfield Mix 舒适真皮', 'properties_name'=>'clarks正装男鞋 Corfield Mix 舒适真皮系带皮鞋17秋冬新款', 'list_time'=>'2009-10-22 14:22:06', 'price' => 1499.00))
        );

        foreach($product_ids as $pid)
        {
            if($cc < 8 ) {
//            $req = new ItemSellerGetRequest;
//            $req->setFields("num_iid,product_id,title,cid,desc,nick,price,approve_status,skus,outer_id,list_time");
//            $req->setNumIid($pid);
//            $resp = $product_dataTOP->execute($req, $session);
//            $product_data = json_decode(json_encode($resp), true);
                $product_data = $product_all_data[$cc];

                $product_id = $product_data['item']['product_id'];
                $prefix_product_id = 'TM' . $product_id;
                $name = $product_data['item']['title'];
                $category_id = $product_data['item']['cid'];
                //$description = $product_data['item']['desc'];
                $created_at = date("Y-m-d H:i:s", strtotime($product_data['item']['list_time']));
                $product_visible_status = 1;
                //$product_active_status = $product_data['IsActive'];
                $brand_name = '';

//            foreach($brand_list as $_brand)
//            {
//                if($_brand['id']==$brand_id)
//                {
//                    $brand_name = $_brand['name'];
//                }
//            }

                //$product_sku_data = $product_data['item']['skus'];
                //$product_outer_id = $product_data['item']['outer_id'];
                //print_r($product_data); return;

//            $req = new ItemSkusGetRequest;
//            $req->setFields("sku_id,num_iid");
//            $req->setNumIids($pid);
//            $resp = $product_dataTOP->execute($req, $session);
//            $product_sku_data = json_decode(json_encode($resp), true);

                $_product_sku = $product_data['item'];

                $count = 1;
                //foreach($product_sku_data as $_product_sku)
                //{
                $sku_id = $_product_sku['sku_id'];
                $sku_name = $_product_sku['title'];
                $product_price = $_product_sku['price'];
                //$product_image_url = $_product_sku['image'];
                $product_image_url = $_product_sku['product_url'];
                $product_stock = $_product_sku['with_hold_quantity'];
                $product_qty = $_product_sku['quantity'];
                //$product_weight = $_product_sku['measures']['cubicweight'];
                $product_weight = $_product_sku['weight'];
                if ($count == 1) {
                    $checkProductModel = Products::find()->where(['product_name' => $sku_name, 'SKU' => $sku_id])->one();
                    if (empty($checkProductModel)) {
                        //Create Model for Each new Product
                        $productModel = new Products ($user_id);
                        $productModel->channel_abb_id = "";
                        $productModel->product_name = $sku_name;
                        $productModel->SKU = $sku_id;
                        $productModel->UPC = '';
                        $productModel->EAN = '';
                        $productModel->Jan = '';
                        $productModel->ISBN = '';
                        $productModel->MPN = '';
                        //$productModel->description = $description;
                        $productModel->adult = 'no';
                        $productModel->age_group = NULL;
                        $productModel->gender = 'Unisex';
                        if ($product_stock > 0):
                            $productModel->availability = 'In Stock';
                        else:
                            $productModel->availability = 'Out of Stock';
                        endif;
                        $productModel->brand = $brand_name;
                        $productModel->condition = 'New';
                        $productModel->weight = $product_weight;
                        $productModel->stock_quantity = $product_qty;
                        if ($product_stock > 0):
                            $productModel->stock_level = 'In Stock';
                        else:
                            $productModel->stock_level = 'Out of Stock';
                        endif;
                        if ($product_visible_status == 1):
                            $productModel->stock_status = 'Visible';
                        else:
                            $productModel->stock_status = 'Hidden';
                        endif;
                        $productModel->price = $product_price;
                        $productModel->sales_price = $product_price;
                        $productModel->created_at = $created_at;
                        $productModel->updated_at = date('Y-m-d h:i:s', time());
                        //Save Elliot User id
                        $productModel->elliot_user_id = $user_id;
                        if ($productModel->save(false)):
                            /* Save Product Abbrivation Id */
                            $last_pro_id = $productModel->id;
                            $product_abberivation = new ProductAbbrivation();
                            $product_abberivation->channel_abb_id = $prefix_product_id;
                            $product_abberivation->product_id = $last_pro_id;
                            $product_abberivation->channel_accquired = 'aliexpress';
                            $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                            $product_abberivation->save(false);
                        endif;

                        if (!empty($category_id)):
                            $category = CategoryAbbrivation::find()->where(['channel_abb_id' => 'TM' . $category_id])->one();
                            if (!empty($category)):
                                $cat_id = $category->category_ID;
                                $productCategoryModel = new ProductCategories;
                                $productCategoryModel->category_ID = $cat_id;
                                $productCategoryModel->product_ID = $last_pro_id;
                                $productCategoryModel->created_at = $created_at;
                                $productCategoryModel->updated_at = date('Y-m-d h:i:s', time());
                                //Save Elliot User id
                                $productCategoryModel->elliot_user_id = $user_id;
                                $productCategoryModel->save(false);
                            endif;
                        endif;
                        //Product Images Table Entry
                        if ($product_image_url != ''):
                            $productImageModel = new ProductImages;
                            $productImageModel->product_ID = $last_pro_id;
                            $productImageModel->label = '';
                            $productImageModel->link = $product_image_url;
                            $productImageModel->default_image = 'Yes';
                            $productImageModel->image_status = 1;
                            $productImageModel->created_at = $created_at;
                            $productImageModel->updated_at = date('Y-m-d h:i:s', time());
                            $productImageModel->save(false);
                        endif;

                        $get_aliexpress_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                        $aliexpress_channel_id = $get_aliexpress_id->channel_ID;

                        $productChannelModel = new ProductChannel;
                        $productChannelModel->channel_id = $aliexpress_channel_id;
                        $productChannelModel->product_id = $last_pro_id;
                        $productChannelModel->created_at = $created_at;
                        $productChannelModel->status = 'yes';
                        $productChannelModel->updated_at = date('Y-m-d h:i:s', time());
                        $productChannelModel->save(false);
                    } else {
                        $check_abbrivation = ProductAbbrivation::find()->where(['channel_abb_id' => $prefix_product_id])->one();
                        if (empty($check_abbrivation)) {
                            /* Save Product Abbrivation Id */
                            $product_abberivation = new ProductAbbrivation();
                            $product_abberivation->channel_abb_id = $prefix_product_id;
                            $product_abberivation->product_id = $checkProductModel->id;
                            $product_abberivation->channel_accquired = 'aliexpress';
                            $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                            $product_abberivation->save(false);


                            $get_aliexpress_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                            $aliexpress_channel_id = $get_aliexpress_id->channel_ID;

                            //Product Channel/Store Table Entry
                            $productChannelModel = new ProductChannel;
                            $productChannelModel->channel_id = $aliexpress_channel_id;
                            $productChannelModel->product_id = $checkProductModel->id;
                            $productChannelModel->status = 'yes';
                            $productChannelModel->created_at = date('Y-m-d H:i:s', time());
                            $productChannelModel->save(false);
                        }
                    }
                    //}
                    //$count++;
                }
                $cc++;
            }

        }
    }

    public function aliexpressOrderImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id) {
        if(isset($_SESSION['sessionKey'])) $session = $_SESSION['sessionKey'];
        else $session = '6202608917f73045ZZ1e3902f90b9689d303bb1dee58c9f2054718371';

        $orderTOP = new TopClient();
        $orderTOP->appkey = $aliexpress_app_key;
        $orderTOP->secretKey = $aliexpress_app_secret;
//        $req = new TradesSoldGetRequest;
//        $req->setFields("tid, type, status, payment, orders, rx_audit_status");
        $req = new LogisticsOrdersDetailGetRequest;
        $req->setFields("tid, order_code, seller_nick, buyer_nick, item_title, receiver_location, status, type, company_name, created, modified, is_quick_cod_order, sub_tids, is_split");
        $resp = $orderTOP->execute($req, $session);
        $order_collection_list = json_decode(json_encode($resp), true);
        if($order_collection_list['total_results'] == 0) {
            echo "There is no results for aliexpress Orders";
            return;
        }
        $order_trades = $order_collection_list['shippings']['shipping'];

        foreach($order_trades as $order_data)
        {
            $order_id = $order_data["tid"];
            $req = new TradeGetRequest;
            $req->setFields("tid, type, status, payment, orders");
            $req->setTid($order_id);
            $resp = $orderTOP->execute($req, $session);
            $order_collection = json_decode(json_encode($resp), true);

            $order_id = $order_collection['tid'];
//            $origin = $order_collection['origin'];
//            $salesChannel = $order_collection['salesChannel'];
            $order_status = $order_collection['status'];
            $order_total = $this->aliexpressPriceFormat($order_collection['price']);
            $order_created_at = date("Y-m-d h:i:s", strtotime($order_collection['created']));
            $order_updated_at = date("Y-m-d h:i:s", strtotime($order_collection['modified']));
            $group_price = $order_collection['total_fee'];

            switch (strtolower($order_status)) {
                case "trade_no_create_pay":
                    $order_status_2 = "Pending";
                    break;
                case "awaiting confirmation":
                    $order_status_2 = 'Pending';
                    break;
                case "payment pending":
                    $order_status_2 = "Pending";
                    break;
                case "payment approved":
                    $order_status_2 = "In Transit";
                    break;
                case "cancellation grace period":
                    $order_status_2 = "Cancel";
                    break;
                case "ready for handling":
                    $order_status_2 = "In Transit";
                    break;
                case "preparing delivery":
                    $order_status_2 = "In Transit";
                    break;
                default:
                    $order_status_2 = "Pending";
            }

            $discount_price = '';
            $shipping_price = '';
            $tax_price = '';
            foreach($group_price as $_price)
            {
                $price_type = $_price['id'];
                if($price_type=='Discounts')
                {
                    $discount_price = $this->aliexpressPriceFormat($_price['value']);
                }
                if($price_type=='Shipping')
                {
                    $shipping_price = $this->aliexpressPriceFormat($_price['value']);
                }
                if($price_type=='Tax')
                {
                    $tax_price = $this->aliexpressPriceFormat($_price['value']);
                }
            }

            $payment_method = $order_collection['paymentData']['transactions'][0]['payments'][0]['paymentSystemName'];

            $customer_data = $order_collection['clientProfileData'];
            $email = $customer_data['email'];
            $firstName = $customer_data['firstName'];
            $lastName = $customer_data['lastName'];
            $phone = $customer_data['phone'];
            $aliexpress_customer_profile_id = $customer_data['userProfileId'];

            $shipping_data = $order_collection['shippingData'];
            $receiver_name = $shipping_data['address']['receiverName'];
            $zip_code = $shipping_data['address']['postalCode'];
            $city = $shipping_data['address']['city'];
            $state = $shipping_data['address']['state'];
            $country = $shipping_data['address']['country'];
            $street = $shipping_data['address']['street'];

            $shipping_address = $street.' '.$city.' '.$state.' '.$zip_code.' '.$country;
            $billing_address = $street.' '.$city.' '.$state.' '.$zip_code.' '.$country;

            $order_items = $order_collection['items'];
            $total_quantity_ordered = '';
            foreach($order_items as $_items)
            {
                $total_quantity_ordered += $_items['quantity'];
            }

            $customerCheckModel = CustomerUser::find()->Where(['channel_abb_id' => 'TM'.$aliexpress_customer_profile_id])->one();
            if(empty($customerCheckModel))
            {
                if($aliexpress_customer_profile_id == '')
                {
                    $aliexpress_customer_profile_id = '0000';
                }
                $customerCheckAnotherModel = CustomerUser::find()->Where(['channel_abb_id' => 'TM'.$aliexpress_customer_profile_id, 'email'=>$email])->one();
                if(empty($customerCheckAnotherModel))
                {
                    $Customers_create_model = new CustomerUser($user_id);
                    $Customers_create_model->channel_abb_id = 'TM'.$aliexpress_customer_profile_id;
                    $Customers_create_model->first_name = $firstName;
                    $Customers_create_model->last_name = $lastName;
                    $Customers_create_model->email = $email;
                    $Customers_create_model->channel_acquired = 'aliexpress';
                    $Customers_create_model->date_acquired = '';
                    $Customers_create_model->created_at = date('Y-m-d h:i:s', time());
                    $Customers_create_model->elliot_user_id = $user_id;
                    $Customers_create_model->save(false);
                    $elliot_customer_id = $Customers_create_model->customer_ID;
                }
                else
                {
                    $elliot_customer_id = $customerCheckAnotherModel->customer_ID;
                }
            }
            else
            {
                $customerCheckModel->street_1 = $street;
                $customerCheckModel->street_2 = '';
                $customerCheckModel->city = $city;
                $customerCheckModel->country = $country;
                $customerCheckModel->state = $state;
                $customerCheckModel->zip = $zip_code;
                $customerCheckModel->phone_number = $phone;
                $customerCheckModel->ship_street_1 = $street;
                $customerCheckModel->ship_street_2 = '';
                $customerCheckModel->ship_city = $city;
                $customerCheckModel->ship_state = $state;
                $customerCheckModel->ship_zip = $zip_code;
                $customerCheckModel->ship_country = $country;
                $customerCheckModel->save(false);
                $elliot_customer_id = $customerCheckModel->customer_ID;
            }

            $check_order = Orders::find()->Where(['channel_abb_id' => 'TM'.$order_id])->one();
            if(empty($check_order))
            {
                $order_model = new Orders($user_id);
                $order_model->elliot_user_id = $user_id;
                $order_model->channel_abb_id = 'TM' . $order_id;
                $order_model->customer_id = $elliot_customer_id;
                $order_model->order_status = $order_status_2;
                $order_model->product_qauntity = $total_quantity_ordered;
                $order_model->shipping_address = $shipping_address;
                $order_model->ship_street_1 = $street;
                $order_model->ship_street_2 = '';
                $order_model->ship_city = $city;
                $order_model->ship_state = $state;
                $order_model->ship_zip = $zip_code;
                $order_model->ship_country = $country;
                $order_model->billing_address = $billing_address;
                $order_model->bill_street_1 = $street;
                $order_model->bill_street_2 = '';
                $order_model->bill_city = $city;
                $order_model->bill_state = $state;
                $order_model->bill_zip = $zip_code;
                $order_model->bill_country = $country;
                $order_model->base_shipping_cost = $shipping_price;
                $order_model->shipping_cost_tax = $tax_price;
                $order_model->payment_method = $payment_method;
                $order_model->payment_status = $order_status_2;
                $order_model->refunded_amount = '';
                $order_model->discount_amount = $discount_price;
                $order_model->total_amount = $order_total;
                $order_model->order_date = $order_created_at;
                $order_model->updated_at = $order_updated_at;
                //$order_model->created_at = date('Y-m-d h:i:s', time());
                $order_model->created_at = $order_created_at;
                $order_model->save(false);
                $last_order_id = $order_model->order_ID;

                $aliexpress_channel = Channels::find()->where(['channel_name' => 'aliexpress'])->one();
                $aliexpress_channel_id = $aliexpress_channel->channel_ID;
                $order_channels_model = new OrderChannel();
                $order_channels_model->elliot_user_id = $user_id;
                $order_channels_model->order_id = $last_order_id;
                $order_channels_model->channel_id = $aliexpress_channel_id;
                $order_channels_model->created_at = date('Y-m-d h:i:s', time());
                $order_channels_model->save(false);
                $order_items = $order_collection['items'];
                foreach($order_items as $_items)
                {
                    $product_id = $_items['productId'];
                    $quantity_ordered = $_items['quantity'];
                    $product_name = $_items['name'];
                    $product_price = $this->aliexpressPriceFormat($_items['price']);
                    $product_sku = '';
//                    if(!is_scalar($product_detail))
//                    {
//                        $product_sku = $product_detail['skus'][0]['sku'];
//                    }
                    $checkProductId = Products::find()->where(['product_name' => $product_name, 'sku' => $product_sku])->one();
                    if(empty($checkProductId))
                    {
                        if($product_id=='')
                        {
                            $product_id = '0000';
                        }
                        $productModel = new Products ($user_id);
                        $productModel->channel_abb_id = '';
                        $productModel->product_name = $product_name;
                        $productModel->SKU = $product_sku;
                        $productModel->UPC = '';
                        $productModel->EAN = '';
                        $productModel->Jan = '';
                        $productModel->ISBN = '';
                        $productModel->MPN = '';
                        $productModel->description = '';
                        $productModel->adult = 'no';
                        $productModel->age_group = NULL;
                        $productModel->gender = 'Unisex';
                        $productModel->brand = '';
                        $productModel->stock_quantity = 1;
                        $productModel->availability = 'Out of Stock';
                        $productModel->stock_level = 'Out of Stock';
                        $productModel->stock_status = 'Visible';
                        $productModel->price = $product_price;
                        $productModel->sales_price = $product_price;
                        $productModel->elliot_user_id = $user_id;
                        $productModel->save(false);
                        $last_pro_id = $productModel->id;

                        $custom_productChannelModel = new ProductChannel;
                        $channel_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'aliexpress'])->one();
                        $custom_productChannelModel->channel_id = $channel_id->channel_ID;
                        $custom_productChannelModel->product_id = $last_pro_id;
                        $custom_productChannelModel->elliot_user_id = $user_id;
                        $custom_productChannelModel->status = 'no';
                        $custom_productChannelModel->created_at = date('Y-m-d h:i:s', time());
                        $custom_productChannelModel->save(false);

                        /* Save Product Abbrivation Id */
                        $product_abberivation = new ProductAbbrivation();
                        $product_abberivation->channel_abb_id = 'TM'.$product_id;
                        $product_abberivation->product_id = $last_pro_id;
                        $product_abberivation->channel_accquired = 'aliexpress';
                        $product_abberivation->created_at = date('Y-m-d H:i:s', time());
                        $product_abberivation->save(false);

                    }
                    else
                    {
                        $last_pro_id = $checkProductId->id;
                    }

                    $order_products_model = new OrdersProducts;
                    $order_products_model->order_Id = $last_order_id;
                    $order_products_model->product_Id = $last_pro_id;
                    $order_products_model->qty = $quantity_ordered;
                    $order_products_model->order_product_sku = $product_sku;
                    $order_products_model->created_at = date('Y-m-d h:i:s', time());
                    $order_products_model->elliot_user_id = $user_id;
                    $order_products_model->save(false);
                }
            }
        }

    }


    public function aliexpressCustomerImporting_test($aliexpress_account, $aliexpress_app_key, $aliexpress_app_secret, $user_id) {

//        $customerTOP = new TopClient();
//        $customerTOP->appkey = $aliexpress_app_key;
//        $customerTOP->secretKey = $aliexpress_app_secret;
//        $req = new OpenAccountListRequest;
//        $resp = $customerTOP->execute($req);
//        $customer_data = json_decode(json_encode($resp), true);

        $aliexpress_channel = Channels::find()->where(['channel_name' => 'aliexpress'])->one();
        $aliexpress_channel_id = $aliexpress_channel->channel_ID;
        $checkConnection = ChannelConnection::find()->where(['channel_id' => $aliexpress_channel_id, 'user_id' => $user_id])->one();
        $aliexpress_channel_id = $checkConnection->channel_connection_id;

        $store_details_check = StoreDetails::find()->where(['channel_connection_id' => $aliexpress_channel_id])->one();
        if(empty($store_details_check)){
            $save_store_details = new StoreDetails();
            $save_store_details->channel_connection_id = $aliexpress_channel_id;
            $save_store_details->store_name = 'aliexpress';
            $save_store_details->store_url = 'https://www.aliexpress.com/';
            $save_store_details->country = 'China';
            $save_store_details->country_code = 'CN';
            $save_store_details->currency = 'CNY';
            $save_store_details->currency_symbol = 'CNY';
            $save_store_details->others = '';
            $save_store_details->channel_accquired = 'aliexpress';
            $save_store_details->created_at = date('Y-m-d H:i:s', time());
            $save_store_details->save(false);
        }


        $customer_data = array(
            array('email'=>'wang.ziche@163.com', 'fname'=>'Wang', 'lname'=>'ZiChe', 'weixin'=>'13844351939', 'login_id'=>'354345345'),
            array('email'=>'web.mobile@hotmail.com', 'fname'=>'Web', 'lname'=>'Mobile', 'weixin'=>'18843339121', 'login_id'=>'345234752'),
            array('email'=>'test@test.com', 'fname'=>'Test', 'lname'=>'Teste', 'weixin'=>'10000000000', 'login_id'=>'123765448')
        );


        if(sizeof($customer_data) > 1) {
            foreach ($customer_data as $customer_list) {
                $email = $customer_list['email'];
                //$id = $customer_list['id'];
                $phone = $customer_list['weixin'];
                $first_name = $customer_list['fname'];
                $last_name = $customer_list['lname'];
                $aliexpress_customer_id = $customer_list['login_id'];
//                $customer_created_at = date("Y-m-d h:i:s", strtotime($customer_list['gmt_create']));
//                $customer_updated_at = date("Y-m-d h:i:s", strtotime($customer_list['gmt_modified']));
                $customer_created_at = date("Y-m-d h:i:s");
                $customer_updated_at = date("Y-m-d h:i:s");
                if ($aliexpress_customer_id == '') {
                    $aliexpress_customer_id = '0000';
                }
                $checkCustomerModel = CustomerUser::find()->where(['channel_abb_id' => 'TM' . $aliexpress_customer_id, 'email' => $email])->one();
                if (empty($checkCustomerModel)) {
                    $Customers_model = new CustomerUser($user_id);
                    $Customers_model->channel_abb_id = 'TM' . $aliexpress_customer_id;
                    $Customers_model->first_name = $first_name;
                    $Customers_model->last_name = $last_name;
                    $Customers_model->email = $email;
                    $Customers_model->phone_number = $phone;
                    $Customers_model->channel_acquired = 'aliexpress';
                    $Customers_model->city = 'BEIJING';
                    $Customers_model->country = 'CHN';
                    $Customers_model->zip = '065001';
                    $Customers_model->date_acquired = $customer_created_at;
                    $Customers_model->created_at = $customer_created_at;
                    $Customers_model->updated_at = $customer_updated_at;
                    $Customers_model->elliot_user_id = $user_id;
                    $Customers_model->save(false);
                }
                $checkCustomerModel = CustomerUser::find()->where(['channel_abb_id' => 'TM' . $aliexpress_customer_id, 'email' => $email])->one();
                $customer_id = $checkCustomerModel->customer_ID;

                $customer_abbrivationModel = CustomerAbbrivation::find()->where(['channel_abb_id' => 'TM' . $aliexpress_customer_id])->one();
                if(empty($customer_abbrivationModel)) {
                    $Customers_abbrivation = new CustomerAbbrivation();
                    $Customers_abbrivation->channel_abb_id = 'TM'.$aliexpress_customer_id;
                    $Customers_abbrivation->customer_id = $customer_id;
                    $Customers_abbrivation->channel_accquired = 'aliexpress';
                    $Customers_abbrivation->bill_street_1 = '东中街21号 3-872';
                    $Customers_abbrivation->bill_street_2 = '';
                    $Customers_abbrivation->bill_city = '北京市';
                    $Customers_abbrivation->bill_state = '北京市';
                    $Customers_abbrivation->bill_zip = '100027';
                    $Customers_abbrivation->bill_country = 'China';
                    $Customers_abbrivation->bill_country_iso = '';
                    $Customers_abbrivation->ship_street_1 = '';
                    $Customers_abbrivation->ship_street_2 = '';
                    $Customers_abbrivation->ship_city = '';
                    $Customers_abbrivation->ship_state = '';
                    $Customers_abbrivation->ship_country = '';
                    $Customers_abbrivation->ship_zip = '';

                    $Customers_abbrivation->mul_channel_id = $aliexpress_channel_id;
                    $Customers_abbrivation->created_at = date('Y-m-d H:i:s', strtotime($customer_created_at));
                    $Customers_abbrivation->updated_at = date('Y-m-d H:i:s', strtotime($customer_created_at));
                    $Customers_abbrivation->save(false);
                }
            }
        }
    }
}