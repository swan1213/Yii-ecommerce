<?php
namespace frontend\controllers;
ob_start();
use common\models\ChannelConnection;
use common\models\Channels;
use common\models\StoresConnection;
use Bigcommerce\Api\Connection;
use common\models\ProductImages;
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
use common\models\Stores;
use Yii;

use Automattic\WooCommerce\Google\Client1 as google;

class GoogleController extends \yii\web\Controller
{
    public function actionIndex()
    {
		 $id = Yii::$app->user->identity->id;
		$channel = Channels::find()->Where(['channel_name'=>'Google shopping'])->one();
		$channel_id = $channel['channel_ID'];
		$channel_connection = ChannelConnection::find()->Where(['user_id'=>$id,'channel_id'=>$channel_id])->one();
		if(!empty($channel_connection)){
			$google_merchant_id = $channel_connection['google_merchant_id'];
		/*	//echo '<pre>'; print_r($channel_connection); echo '</pre>';
			$google_client_id = $channel['google_client_id'];
			$google_client_secret = $channel['google_client_secret'];
			$google_merchant_id = $channel['google_merchant_id'];
			 $this->actionGoogle($google_client_id,$google_client_secret,$google_merchant_id); */
			  require_once $_SERVER['DOCUMENT_ROOT'].'/backend/web/googleApi/php/vendor/autoload.php';
			$client = new \Google_Client();
			 if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

				$client->setAccessToken($_SESSION['access_token']);
				$service = new \Google_Service_ShoppingContent($client);
				$parameters = array('maxResults' => 250);
				$products = $service->products->listProducts($google_merchant_id, $parameters);
				$count = 0;
				//echo '<pre>'; print_r($products->getResources()); die('dsaf');
				$GoogleApiArray = $products->getResources();
				$this->datasave($GoogleApiArray);
				

			} else{
				 $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
				 $url = explode('google',$actual_link);
				 $actual_link = $url[0].'google';
				  $redirect_uri = $actual_link . '/oauth2callback/';
				 header('location: ' . $redirect_uri);
			}
			 return $this->render('index');
		}
		else{
        return $this->render('index');
		}
    }
		public function actionSave(){
		//echo '<pre>'; print_r($_POST); echo '</pre>'; 
            $user_id = Yii::$app->user->identity->id;
	    $channel_big = Channels::find()->where(['channel_name' => 'Google shopping'])->one();
	
                if (!empty($channel_big)) {
                    $channel_id=$channel_big->channel_ID;
                }
				$clientID = $_POST['clientID'];
				$secretKey = $_POST['secretKey'];
				$merchantID = $_POST['merchantID'];
				//$url = $this->actionOauth2callback($clientID,$secretKey,$merchantID); 
				$Google_connection = new ChannelConnection();
				$Google_connection->google_client_id = $_POST['clientID'];
				$Google_connection->google_client_secret = $_POST['secretKey'];
				$Google_connection->google_merchant_id = $_POST['merchantID'];
						$Google_connection->channel_id=$channel_big->channel_ID;
						$Google_connection->user_id=$user_id;
						$Google_connection->created=date('Y-m-d H:i:s');
				if($Google_connection->save(false)){
				echo 'success';
				} else{
					echo 'error';
				}
	}
	
	    public function actionGoogle()
    {
		
                require_once $_SERVER['DOCUMENT_ROOT'].'/backend/web/googleApi/php/vendor/autoload.php';
$client = new \Google_Client();

$client->setApplicationName('Sample Content API application');
$client->setClientId('110121437169995257253');
$client->setClientSecret('fpUEuTN1VJmzNkkJLSBWGFy2');
$client->addScope('https://www.googleapis.com/auth/content');
$client->setScopes('https://www.googleapis.com/auth/content');
$client->addScope('https://www.googleapis.com/auth/drive.metadata.readonly');
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

  $client->setAccessToken($_SESSION['access_token']);
 
  
   $service = new \Google_Service_ShoppingContent($client);
 $parameters = array('maxResults' => 250);
    $products =
        $service->products->listProducts('117189394', $parameters);
    $count = 0;
    
      foreach ($products->getResources() as $product) {
       echo '<pre>'; printf("%s %s\n", $product->getId(), $product->getTitle()); echo '</pre>';
      }

} else {

 $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
 $url = explode('google',$actual_link);
 $actual_link = $url[0].'google';
  $redirect_uri = $actual_link . '/oauth2callback/';
 header('location: ' . $redirect_uri);
  die;
}
			 
			 
			 
			 
			 
			 
			 
			 
	  
    }
	public function actionOauth2callback(){
		//session_start();
		 require_once $_SERVER['DOCUMENT_ROOT'].'/backend/web/googleApi/php/vendor/autoload.php';
$client = new \Google_Client();
$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
  $redirect_uri = $actual_link . '/oauth2callback/';
  
   $id = Yii::$app->user->identity->id;
		$channel = Channels::find()->Where(['channel_name'=>'Google shopping'])->one();
		$channel_id = $channel['channel_ID'];
		$channel_connection = ChannelConnection::find()->Where(['user_id'=>$id,'channel_id'=>$channel_id])->one();
		//echo '<pre>'; print_r($channel_connection); echo '</pre>'; die('dsfa');
		if(!empty($channel_connection)){
			
			$google_client_id = $channel_connection['google_client_id'];
			$google_client_secret = $channel_connection['google_client_secret'];
			$google_merchant_id = $channel_connection['google_merchant_id'];
			 //$this->actionGoogle($google_client_id,$google_client_secret,$google_merchant_id);
		
  
//$client->setAuthConfigFile('client_secrets.json');
$client->setClientId($google_client_id);

$client->setClientSecret($google_client_secret);
 $url = explode('oauth2callback',$actual_link);
 $actual_link1 = $url[0].'oauth2callback/';
  //$redirect_uri = $actual_link . '/oauth2callback/';

$client->setRedirectUri($actual_link1);

$client->setApplicationName('Sample Content API application');
$client->addScope('https://www.googleapis.com/auth/content');
$client->setScopes('https://www.googleapis.com/auth/content');
$client->addScope('https://www.googleapis.com/auth/drive.metadata.readonly');

if (! isset($_GET['code'])) {
	
  $auth_url = $client->createAuthUrl(); 
 header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
 exit;
 //echo filter_var($auth_url, FILTER_SANITIZE_URL);
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken(); //echo $url[0]; die('dsf');
  
  header('Location: ' . filter_var( $url[0], FILTER_SANITIZE_URL));
  exit;
}

	}

}

public function datasave($data){
	echo '<pre>'; print_r($data); echo '</pre>';
	 $user_id = Yii::$app->user->identity->id;
				 foreach ($data as $product) {
					
					 $pro_id =  'GG_'.$product->getId();
					 $title =  $product->getTitle();
					 $variants_sku = $product->getId();
					 $variants_barcode = 'null';
					 $p_mpn = $product->getMpn();
					 $description = $product->getDescription();
					 $adult = $product->getAdult();
					 $agegroup = $product->getAgeGroup();
					 $gender = $product->getGender();
					 $brand = $product->getBrand();
					 $unit_​​pricing_​​measure = $product->getUnitPricingMeasure();
					 $availability = $product->getAvailability();
					 $price = $product['price']['value'];
					 $sale_​​price = $product['customAttributes']['4']['value'];
					 $variants_created_at = $product->getAvailabilityDate();
					 $variants_updated_at = $product->getExpirationDate();
					 $google_​​product_​​category = $product->getGoogleProductCategory();
					 $imagelink = $product->getImageLink();
					 $mobile_​​link = $product->getMobileLink();
					 /*  */
					//die('dsf');
					 $checkVarModel = Products::find()->where(['channel_abb_id' => $pro_id])->one();
					 if(empty($checkVarModel)){
						  $productModel = new Products($user_id);
                            $productModel->channel_abb_id = $pro_id;
                            $productModel->product_name = $title;
                            $productModel->SKU = $variants_sku;
                            $productModel->UPC = $variants_barcode;
                           
						   
                            $productModel->EAN = 'null';
                            $productModel->Jan = 'null';
                            $productModel->ISBN = 'null';
						   
						   
						   
                            $productModel->MPN = $p_mpn;
                            $productModel->description = $description;
                            $productModel->adult = $adult;
                            $productModel->age_group = $agegroup;
                            $productModel->gender = $gender;
                            $productModel->brand = $brand;
                            $productModel->weight = $unit_​​pricing_​​measure;
                            $productModel->stock_quantity = '';
							$productModel->availability = $availability;
                            $productModel->stock_status = 'Visible';
                           
                            $productModel->price = $price;
                            $productModel->sales_price = $sale_​​price;
                            $productModel->created_at = date('Y-m-d h:i:s', strtotime($variants_created_at));
                            $productModel->updated_at = date('Y-m-d h:i:s', strtotime($variants_updated_at));
                            $productModel->elliot_user_id = $user_id;
                            $productModel->save(false);
                            $last_pro_id = $productModel->id;
                            $custom_productChannelModel = new ProductChannel;
                            //$store_id = Stores::find()->select('store_id')->where(['store_name' => 'Shopify'])->one();
							$channel = Channels::find()->Where(['channel_name'=>'Google shopping'])->one();
							$channel_id = $channel['channel_ID'];
                            $custom_productChannelModel->store_id = '';
                            $custom_productChannelModel->channel_id = $channel_id;
                            $custom_productChannelModel->product_id = $last_pro_id;
                            $custom_productChannelModel->elliot_user_id = $user_id;
                            $custom_productChannelModel->created_at = date('Y-m-d h:i:s', time());
                            $custom_productChannelModel->save(false); 
							
							
                                    //Create Model for Each new Product Category
                                    $productCategoryModel = new ProductCategories;
                                    $ProductImages = new ProductImages;
                                    $productCategoryModel->category_ID = $google_​​product_​​category;
                                    $productCategoryModel->product_ID = $last_pro_id;
                                    $productCategoryModel->created_at = date('Y-m-d h:i:s', time());
                                
                                    //Save Elliot User id
                                    $productCategoryModel->elliot_user_id = $user_id;
                                    $productCategoryModel->save(false);
									
									$ProductImages = new ProductImages;
									$ProductImages->elliot_user_id = $user_id;
                                    $ProductImages->product_ID = $last_pro_id;
                                    $ProductImages->default_image = $imagelink;
                                    $ProductImages->alternative_image = $mobile_​​link;
                                    $ProductImages->priority = '1';
                                    $ProductImages->created_at = date('Y-m-d h:i:s', time());
									$ProductImages->save(false);
					 }
					 else{
						 
					 }
				  // echo '<pre>'; printf("%s %s\n", $product->getId(), $product->getTitle()); echo '</pre>';
				  }
}

}
