<?php     
namespace frontend\controllers;

ob_start();

//session_start();

use Yii;                     
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;  
use leinonen\Yii2Algolia\AlgoliaManager;     
use AlgoliaSearch\AlgoliaException;
use common\models\CustomerUser;          
use common\models\ProductImages;          
use common\models\ProductAbbrivation;          
use common\models\ProductChannel;          
use common\models\StoreDetails;          
use common\models\Stores;          
use frontend\controllers\ChannelsController;   
use yii\helpers\Html;      
use yii\base\Exception; 

class GlobalSearchController extends Controller { 

    private $algoliaManager;
    private $img_url = "https://image.tmdb.org/t/p/w154/";
    
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
    
    public function __construct($id, $module, AlgoliaManager $algoliaManager, $config = [])
    {
        $this->algoliaManager = $algoliaManager;
        parent::__construct($id, $module, $config);
    }

    
    public function actionAlgoliaAjax()
    {                                                                                                                                               
        extract($_POST);              
        if(isset($search_key)){
            $array_customers = []; 
            try{
                $index = $this->algoliaManager->initIndex('customer' . Yii::$app->user->identity->id);        
                $query = $search_key;
                $res = $index->search($query, [
                            'attributesToRetrieve' => [
                                    'first_name',
                                    'last_name',   
        //                            'city',   
        //                            'country',   
        //                            'country_iso',   
                                    'customer_ID',              
        //                            'img',                
                                    ]
                            ]);        
                $total_customer_count_for_per = CustomerUser::find()->Where(['elliot_user_id' => Yii::$app->user->identity->id, 'people_visible_status' => 'active'])->count();
                foreach($res['hits'] as $_customer){
                    $customer = [];
                    if(isset($_customer['customer_ID'])){                                                                                                                                            
                        $user_data = CustomerUser::find()->Where(['customer_ID' => $_customer['customer_ID'], 'people_visible_status' => 'active'])->one();       
                        if(empty($user_data))
                            continue;
                        $customer['customer_ID'] = $user_data->customer_ID;
                        $customer['name'] = $user_data->first_name . " " . $user_data->last_name;        
                        $customer['flag'] = strtolower($user_data->country_iso);   
                        if($user_data->img!="")
                            $customer['img'] = $user_data->img;
                        else 
                            $customer['img'] = '/img/avatar-150.png';    
                        $customer['link'] = "/people/view?id=". $user_data->customer_ID;                        
                        $customer['rate'] = ChannelsController::getStartRating($user_data->customer_ID, $user_data->email, $total_customer_count_for_per);           
                                                  
        //            if(isset($_customer['first_name']))
        //                    $customer['name'] = $_customer['first_name'];
        //                else 
        //                    $customer['name'] = '';
        //                if(isset($_customer['first_name']))
        //                    $customer['name'] = $customer['name'] . $_customer['first_name']; 
        //                    
        //                if(isset($_customer['city']))
        //                    $customer['address'] = $_customer['city'];
        //                else 
        //                    $customer['address'] = '';
        //                if(isset($_customer['country']))
        //                    $customer['address'] = $customer['address'] . $_customer['country']; 
        //                
        //                if(isset($_customer['country_iso']))
        //                    $customer['flag'] = strtolower($_customer['country_iso']);
        //                else 
        //                    $customer['flag'] = ''; 
        //                if(isset($_customer['img']))
        //                    $customer['img'] = $_customer['img'];
        //                else 
        //                    $customer['img'] = '/img/avatar-150.png';                                
                        $view = "<a href=\"{$customer['link']}\" style='color:black'> <div class=\"panel panel-default people-cell\" style=\"padding: 8px; margin-bottom: 8px;\">" .
                                    "<div style=\"display: table;width: 100%;\">" .
                                        "<div style=\"display: table-cell;width: 40px;\"><img src=\"{$customer['img']}\" style=\"width: 100%; border-radius:50%;\"></div>" .
                                        "<div style=\"display: table-cell;vertical-align: middle;padding: 0 8px;\">" .
                                            "<div style=\"color: #4285f4;\">{$customer['name']}<span style='margin-left:8px; color:black;'>#{$customer['customer_ID']}</span></div>" .  
                                            "<div style=\"\">" .
                                                "<span class=\"flag-icon flag-icon-{$customer['flag']}\" style='margin-right:8px'></span>" .                                                   
                                                ($customer['rate']>0 ? "<span class=\"mdi mdi-star yellow\"></span>" : "<span class=\"mdi mdi-star black\"></span>" ).
                                                ($customer['rate']>1 ? "<span class=\"mdi mdi-star yellow\"></span>" : "<span class=\"mdi mdi-star black\"></span>" ).
                                                ($customer['rate']>2 ? "<span class=\"mdi mdi-star yellow\"></span>" : "<span class=\"mdi mdi-star black\"></span>" ).
                                                ($customer['rate']>3 ? "<span class=\"mdi mdi-star yellow\"></span>" : "<span class=\"mdi mdi-star black\"></span>" ).
                                                ($customer['rate']>4 ? "<span class=\"mdi mdi-star yellow\"></span>" : "<span class=\"mdi mdi-star black\"></span>" ).   
                                            "</div>" .
                                        "</div>" .
                                    "</div>" .
                                "</div></a>";      
                                                       
                        $array_customers[] = $view;
                    }                                                    
                } 
            }  
            catch(AlgoliaException $e){} 
            catch(Exception $e){}
             
            $array_products = [];
            try{
                $index = $this->algoliaManager->initIndex('product' . Yii::$app->user->identity->id);        
                $res = $index->search($query, [
                            'attributesToRetrieve' => [
                                    'product_name',
                                    'SKU',   
                                    'price',   
                                    'id',                   
                                    ]
                            ]);             
                foreach($res['hits'] as $_product){
                    $product = [];
                    if(isset($_product['id'])){  
                        $product['id'] = $_product['id'];    
                        if(isset($_product['product_name']))
                            $product['name'] = strtolower($_product['product_name']);
                        else 
                            $product['name'] = '';    
                        if(isset($_product['price']))
                            $product['price'] = "$" . strtolower($_product['price']);
                        else 
                            $product['price'] = "$" . '0';    
                        if(isset($_product['SKU']))
                            $product['SKU'] = $_product['SKU'];
                        else 
                            $product['SKU'] = '';                                                               
                        $data = ProductImages::find('link')->Where(['product_ID' => $_product['id']])->one();  
                        if(!empty($data)){
                            $product['img'] = $data->link;
                        }
                        else{
                            $product['img'] = '';
                        }
                        
                        $Mulstore_id = array(); 
                        $Mulchannel_id = array();
                        $get_product_base = ProductAbbrivation::find()->select(['mul_store_id', 'mul_channel_id', 'channel_accquired'])->where(['product_id' => $_product['objectID']])->all();
                        foreach ($get_product_base as $val) {
                            $Mulstore_id[] = $val->mul_store_id;
                            $Mulchannel_id[] = $val->mul_channel_id;
                        }
                        $names = [];

                        $productChannelImage = ProductChannel::getProductChannelImage($_product['id']);
                        $channelImages = '';

                        // append all images
                        foreach ($productChannelImage as $key => $value):
                            $imageUrl = $value;
                            $Mulstore_id=array_unique($Mulstore_id);
                            if (isset($Mulstore_id) and ! empty($Mulstore_id)) {
                            //means the multiple connection is with store
                            foreach ($Mulstore_id as $store) {

                                $check = StoreDetails::find()->where(['store_connection_id' => $store])->one();
                                if (!empty($check) and isset($check)) {
                                //echo $check->channel_accquired;
                                if (strtolower($key) == strtolower($check->channel_accquired)) {
                                    $names[] = $check->channel_accquired . ' ' . $check->country_code;
                                }
                                }
                            }
                            } else {
                            $names[] = strtoupper($key);
                            }
                            if ($key == 'ShopifyPlus') {
                            $img_height = '13';
                            } else {
                            $img_height = '50';
                            }
                            $tooltip = implode(', ', $names);
                            $channelImages = $channelImages . Html::img($imageUrl, ['alt' => 'Channel Image', 'width' => '50', 'height' => $img_height, 'class' => 'ch_img', 'data-toggle' => "tooltip", 'data-placement' => "right", 'title' => $tooltip, 'data-original-title' => '', 'test_sttr' => '']);
                            $names = array();
                        endforeach;
                        $product['ch_img'] = $channelImages;
                        $view = "<div class='panel panel-default product-cell' style='padding: 8px; margin-bottom: 8px;'>
                            <div style='display: table;width: 100%;'>
                                <div style='display: table-cell;width: 50px;'><img src='{$product['img']}' style='width: 100%;'></div>
                                <div style='display: table-cell;vertical-align: middle;padding: 0 8px;'>
                                    <div style='color: #4285f4;'>{$product['name']}<span style='margin-left:8px; color:black'>#{$product['id']}</span>     </div>
                                     <div style=''>                            
                                        <span>{$product['price']}</span>      
                                        <span style='margin-left:8px;'>{$product['ch_img']}</span>    
                                        <span style='margin-left:8px;'>{$product['SKU']}</span>                                                                                                                                             
                                     </div>
                                </div>
                            </div>   
                        </div>";
                        $array_products[] = $view;
                    }
                } 
            }  
            catch(AlgoliaException $e){} 
            catch(Exception $e){} 
                                     
              
            $array_orders = [];   
        	try{                                     
                $index = $this->algoliaManager->initIndex('order' . Yii::$app->user->identity->id);        
                $res = $index->search($query, [
                            'attributesToRetrieve' => [   
                                    'channel_abb_id',    
                                    'channel_accquired',    
                                    'total_amount',   
                                    'order_ID',                   
                                    'order_status',                   
                                    ]
                            ]);          
                foreach($res['hits'] as $_order){
                    $product = [];
                    if(isset($_order['order_ID'])){  
                        $odrder = [];
                        $odrder['order_ID'] = $_order['order_ID'];
                        if(isset($_order['channel_abb_id']))
                            $odrder['channel_abb_id'] = explode('_', strtolower($_order['channel_abb_id']))[0];
                        else 
                            $odrder['channel_abb_id'] = '';   
                                                                

                        
                        if(isset($_order['channel_accquired']))
                            $odrder['channel_accquired'] = $_order['channel_accquired'];
                        else 
                            $odrder['channel_accquired'] = '';  
                        $odrder['channel_img'] = ChannelsController::getStoreChannelImageByName($odrder['channel_accquired']);
                        
                        if(isset($_order['total_amount']))
                            $odrder['total_amount'] = number_format((float) $_order['total_amount'], 2, '.', ','); 
                        else 
                            $odrder['total_amount'] = '0';  
                                                                                                   

                        $user = Yii::$app->user->identity;
                        $conversion_rate = 1;               
                        if (isset($user->currency)) {
                            $conversion_rate = Stores::getDbConversionRate($user->currency);
                            $odrder['total_amount'] = $odrder['total_amount'] * $conversion_rate;           
                        }
                        $odrder['total_amount'] = "$" . $odrder['total_amount'];
                        if(isset($_order['order_status']))
                            $odrder['order_status'] = $_order['order_status']; 
                        else 
                            $odrder['order_status'] = '';  
                                                                            
                        $label = '';
                        if ($odrder['order_status'] == 'Completed') :
                            $label = 'label-success';
                        endif;

                        if ($odrder['order_status'] == 'Returned' || $odrder['order_status'] == 'Refunded' || $odrder['order_status'] == 'Cancel' || $order['order_status'] == 'Partially Refunded') :
                            $label = 'label-danger';
                        endif;

                        if ($odrder['order_status'] == 'In Transit'):
                            $label = 'label-primary';
                        endif;

                        if ($odrder['order_status'] == 'Awaiting Fulfillment' || $odrder['order_status'] == 'Awaiting Shipment' || $odrder['order_status'] == 'Incomplete' || $odrder['order_status'] == 'waiting-for-shipment' || $odrder['order_status'] == 'Pending' || $odrder['order_status'] == 'Awaiting Payment' || $odrder['order_status'] == 'On Hold'):
                            $label = 'label-warning';
                        endif;
                        if ($odrder['order_status'] == 'Shipped' || $order['order_status'] == 'Partially Shipped'):
                            $label = 'label-primary';
                        endif;
                        $view = "<a href='/order/view/{$odrder['order_ID']}'>
                                <div class='panel panel-default order-cell' style='padding: 8px; margin-bottom: 8px;'>                          
                                    <div style='display: table;width: 100%;'>                                                                       
                                        <div style='display: table-cell;width: 50px;'>{$odrder['channel_img']}</div>   
                                        <div style='color: #4285f4;'>{$odrder['channel_abb_id']}<span style='margin-left:8px; color:black'>#{$odrder['order_ID']}</span>     </div>   
                                        <div style='display: table-cell;width: 80px; text-align: left;'>{$odrder['total_amount']}</div>                                                  
                                        <div style='display: table-cell;width: 80px; text-align: right;'><span class='label  {$label}'> {$odrder['order_status']} </span></div>           
                                    </div>   
                                </div></a>";
                        $array_orders[] = $view;
                    }
                }  
        	}  
        	catch(AlgoliaException $e){} 
        	catch(Exception $e){} 
                                     
                                      
                                            
            return json_encode(array("customers" => $array_customers, "products" => $array_products, "orders" => $array_orders));  
        }   
        else{            
            return json_encode(array("customers" => [], "products" => [], "orders" => []));  
        }
    }
    
    
    public function actionIndex(){            
        return $this->render('index');
    }
}  
?>
