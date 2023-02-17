<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 1/29/2018
 * Time: 10:30 AM
 */

namespace console\controllers;

use common\models\Notification;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductConnection;
use common\models\ProductImage;
use common\models\UserConnection;
use common\models\UserProfile;
use frontend\components\CustomFunction;
use Yii;
use yii\console\Controller;
use common\commands\SendEmailCommand;

class PinterestImportController extends Controller
{

    public function actionIndex(){
        echo "This is pinterest-import.";
    }

    public function actionPinterest($user_id, $token, $board_id, $category, $country) {
        $base_url  = "https://api.pinterest.com/v1/pins/?access_token=" . $token . "&fields=id";
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Pinterest Client-PHP/2.0.1',
        );
        $categories = explode(",", $category);
        $countries = explode(",", $country);
        foreach ($categories as $category) {

            $category_models = ProductCategory::find()->where(['user_id' => $user_id, 'category_id' => $category])->all();
            foreach ($category_models as $category_model) {
                $product_model = Product::find()->where(['id' => $category_model->product_id])->one();
                if (!empty($product_model)) {
                    $productConnection = ProductConnection::find()->where(['product_id' => $category_model->product_id])->one();
                    $user_connection_model = $productConnection->userConnection;
                    $country_code = $user_connection_model->userConnectionDetails->country_code;
                    if (in_array($country_code, $countries))
                    {
                        $product_image_model = ProductImage::find()->where(['product_id' => $product_model->id, 'default_image' => ProductImage::DEFAULT_IMAGE_YES])->one();
                        if (!empty($product_image_model)) {
                            $params = array(
                                'board' => $board_id,
                                'note' => $product_model->name,
                                'link' => $product_model->url,
                                'image_url' => $product_image_model->link,
                            );

                            $response = CustomFunction::curlHttp($base_url, $params, 'POST', $headers, 1);
                            $json_response = json_decode($response, true);
                            var_dump($json_response);
                        }
                    }
                }
            }
        }
    }
}