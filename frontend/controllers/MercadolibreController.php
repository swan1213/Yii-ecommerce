<?php

namespace frontend\controllers;

use Yii;
use common\models\User;
use common\models\Channels;
use common\models\ChannelsSearch;
use common\models\ChannelConnection;
use common\models\CustomerUser;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\StoresConnection;
use common\models\Stores;
use common\models\Categories;
use common\models\Products;
use common\models\ProductImages;
use common\models\ProductChannel;
use common\models\MerchantProducts;
use common\models\Variations;
use common\models\ProductVariation;
use common\models\VariationsItemList;
use common\models\ProductCategories;
use common\models\Orders;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\Notification;
use frontend\controllers\Meli;
use yii\helpers\Html;

class MercadolibreController extends \yii\web\Controller {

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionAuthMercadolibre() {


        $array_msg = array();
        $mercadolibre_channel_email = $_POST['mercadolibre_channel_email'];
        $mercadolibre_channel_password = $_POST['mercadolibre_channel_password'];
        $mercadolibre_channel_client_id = $_POST['mercadolibre_channel_client_id'];
        $mercadolibre_channel_secret_id = $_POST['mercadolibre_channel_secret_id'];
        $user_id = $_POST['user_id'];

        $user = User::find()->where(['id' => $user_id])->one();
        $user_domain = $user->domain_name;

        $channel_connect = Channels::find()->where(['channel_name' => 'MercadoLibre'])->one();

        $mercado_store_id = $channel_connect->channel_ID;


        $checkConnection = ChannelConnection::find()->where(['channel_id' => $mercado_store_id, 'user_id' => $user_id])->one();

        if (empty($checkConnection)):
            $connectionModel = new ChannelConnection();
            $connectionModel->channel_id = $mercado_store_id;
            $connectionModel->user_id = $user_id;
            $connectionModel->connected = 'Yes';
            $connectionModel->mercado_email = $mercadolibre_channel_email;
            $connectionModel->mercado_password = $mercadolibre_channel_password;
            $connectionModel->mercado_client = $mercadolibre_channel_client_id;
            $connectionModel->mercado_secret = $mercadolibre_channel_secret_id;

            $created_date = date('Y-m-d h:i:s', time());
            $connectionModel->created = $created_date;
            if ($connectionModel->save()):
                //Yii::$app->session->setFlash('success', 'Success! Your shop has been connected successfully. Syncing will start soon.');
                $array_msg['success'] = "Your Mercado Channel has been connected successfully. Syncing will start soon.";
            else:
                $array_msg['error'] = "Error Something went wrong. Please try again";
            endif;
        endif;


        // Yii::$app->session->setFlash('success', 'Success! Your WooCommerce store data has been successfully imported.');
        return json_encode($array_msg);
    }

    public function actionGetAccessToken() {
        $user_id = Yii::$app->user->identity->id;
        $channel_connect = Channels::find()->where(['channel_name' => 'MercadoLibre'])->one();
        $mercado_store_id = $channel_connect->channel_ID;
        $mercado_data = ChannelConnection::find()->where(['channel_id' => $mercado_store_id, 'user_id' => $user_id])->one();
        $client_id = $mercado_data->mercado_client;
        $secret_id = $mercado_data->mercado_secret;
//        $redirect_uri = Yii::$app->params['BASE_URL'].'mercadolibre/generate-access-token?user_id=' . $user_id . '&client_id=' . $client_id . '&client_secret=' . $secret_id;
      //  require_once '/var/www/html/mercadolibreq/Meli/meli.php';
        $mercado_obj = new Meli('31236314994942649190', 'ee58f5df8a26fb2f7d836e0fea6f3d85');
//       $redirect_uri = Yii::$app->params['BASE_URL'].'mercadolibre/token?user_id='.$user_id.'&client_id='.$client_id.'&client_secret='.$secret_id;
       $redirect_uri = Yii::$app->params['BASE_URL'].'mercadolibre/token';
   echo     $redirection = 'https://sandbox-cbt.mercadolibre.com/merchant/authorization?client_id='.$client_id.'&response_type=code&redirect_uri='.$redirect_uri;
   $auth_url  = 'https://sandbox-cbt.mercadolibre.com/merchant';
       $a=$mercado_obj->getAuthUrl($redirect_uri, $auth_url);
        header("location: " . $a);
        exit;
    }
    public function actionToken() {
        echo "<pre>";print_r($_REQUEST);
            print_r($_GET); die('sdff');
    }
    public function actionGenerateAccessToken() {
        $user_id = $_GET['user_id'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox-cbt.mercadolibre.com/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: "
            . "form-data; name=\"client_id\"\r\n\r\n" . $_GET['client_id'] . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: "
            . "form-data; name=\"client_secret\"\r\n\r\n" . $_GET['client_secret'] . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: "
            . "form-data; name=\"grant_type\"\r\n\r\nauthorization_code\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: "
            . "form-data; name=\"code\"\r\n\r\n" . $_GET['code'] . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 5e3a18d1-cc5f-3578-3b47-8fcbd0209744"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
                        echo '<pre>'; print_r($response); echo '</pre>'; die('sdafs');
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response, true);
            $channel_connect = Channels::find()->where(['channel_name' => 'MercadoLibre'])->one();
            $mercado_store_id = $channel_connect->channel_ID;
            $checkConnection = ChannelConnection::find()->where(['channel_id' => $mercado_store_id, 'user_id' => $user_id])->one();
            if (!empty($checkConnection)){
                $checkConnection->mercado_access_token = $mercadolibre_channel_email;
                $checkConnection->mercado_refresh_token = $mercadolibre_channel_password;
                if($checkConnection->save(false))
                {
                    header('location: https://shub01.s86.co/');
                }
            }
            
        }
    }

}
