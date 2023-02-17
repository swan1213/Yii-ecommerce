<?php

namespace frontend\controllers;


use common\models\Country;
use common\models\Feed;
use common\models\Pinterest;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductImage;
use common\models\UserFeed;
use frontend\components\ConsoleRunner;
use frontend\components\CustomFunction;
use frontend\models\search\CategorySearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class PinterestController extends Controller
{
    /**
     * action filter
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {
        $user_id = Yii::$app->user->identity->id;
        $pinterest_model = Feed::findOne(["name"=>"pinterest"]);
        $board_models = UserFeed::find()->where(["feed_id"=>$pinterest_model->id, "user_id"=>$user_id])->all();
        return $this->render('index', [
            "pinterest_model" => $pinterest_model,
            "board_models" => $board_models
        ]);
    }

    public function actionCreate() {
        $user_id = Yii::$app->user->identity->id;
        $token = "";
        $board_name = "";
        $selected_categories = [];
        $selected_countries = [];
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $countries_model = \Yii::$app->db->createCommand("SELECT A.*, B.`user_id` FROM user_connection_details AS A, user_connection AS B WHERE A.`user_connection_id` = B.`id` AND B.`user_id`={$user_id}  GROUP BY A.country_code ")->queryAll();
        $countries = [];
        foreach ($countries_model as $country){
            $country_name = Country::countryInfoFromCode($country['country_code']);
            if($country_name)
                $countries[] = $country_name;
        }
        return $this->render('create', [
                "id"=> 0,
                "user_id" => $user_id,
                "token" => $token,
                "board_name" => $board_name,
                "selected_categories" => $selected_categories,
                "selected_countries" => $selected_countries,
                "categories"=>$categories,
                "countries"=>$countries
            ]
        );
    }

    public function actionUpdate($id) {
        $cur_feed = UserFeed::findOne(["id"=>$id]);
        $user_id = Yii::$app->user->identity->id;
        $board_name = $cur_feed->name;
        $token = $cur_feed->feed_connection_id;
        $selected_categories = json_decode($cur_feed->categories);
        $selected_countries = json_decode($cur_feed->country_codes);
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $countries_model = \Yii::$app->db->createCommand("SELECT A.*, B.`user_id` FROM user_connection_details AS A, user_connection AS B WHERE A.`user_connection_id` = B.`id` AND B.`user_id`={$user_id}  GROUP BY A.country_code ")->queryAll();
        $countries = [];
        foreach ($countries_model as $country){
            $countries[] = Country::countryInfoFromCode($country['country_code']);
        }
        return $this->render('update', [
                "id"=> $id,
                "user_id" => $user_id,
                "token" => $token,
                "board_name" => $board_name,
                "selected_categories" => $selected_categories,
                "selected_countries" => $selected_countries,
                "categories"=>$categories,
                "countries"=>$countries
            ]
        );
    }

    public function actionDelete($id) {
        $cur_feed = UserFeed::findOne(["id"=>$id]);
        $cur_feed->delete();
        return $this->redirect(['/pinterest']);
    }

    public function actionCreateBoard() {
        $fb_feed_model = Feed::findOne(["name"=>"pinterest"]);
        $user_id = \Yii::$app->user->identity->id;
        $token = '';
        $board_name = '';
        $category = array();
        $country = array();
        if (isset($_POST['token']) and ! empty($_POST['token'])) {
            $token = $_POST['token'];
        }
        if (isset($_POST['board_name']) and ! empty($_POST['board_name'])) {
            $board_name = $_POST['board_name'];
        }
        if (isset($_POST['category']) and ! empty($_POST['category'])) {
            $category = $_POST['category'];
        }
        $category_str = json_encode($category);
        $category_str_tmp = implode(",", $category);
        if (isset($_POST['country']) and ! empty($_POST['country'])) {
            $country = $_POST['country'];
        }
        $country_str = json_encode($country);
        $country_str_tmp = implode(",", $country);

        if (!$this->validToken($token)) {
            $result['error'] = 'Your access token invalid!';
            return json_encode($result);
        }
        else {
            $base_url = "https://api.pinterest.com/v1/boards/?access_token=" . $token . "&fields=id";
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: Pinterest Client-PHP/2.0.1',
            );
            $params = array(
                'name' => $board_name,
                'description' => ''
            );

            $response = CustomFunction::curlHttp($base_url, $params, 'POST', $headers, 1);
            $json_response = json_decode($response, true);

            if (isset($json_response['data']) && !empty($json_response['data'])) {

                $result['success'] = true;
                $result['error'] = '';
                $result['id'] = $json_response['data']['id'];

                $importCr = new ConsoleRunner(['file' => '@console/yii']);
                $importCmd = 'pinterest-import/pinterest ' . $user_id;
                $importCmd .= ' ';
                $importCmd .= $token;
                $importCmd .= ' ';
                $importCmd .= $result['id'];
                $importCmd .= ' ';
                $importCmd .= $category_str_tmp;
                $importCmd .= ' ';
                $importCmd .= $country_str_tmp;
                $res = $importCr->run($importCmd);

                //$this->test($user_id, $token, $result['id'], $category_str, $country_str);

                $new_feed = new UserFeed();
                $new_feed->name = $board_name;
                $new_feed->user_id = $user_id;
                $new_feed->feed_id = $fb_feed_model->id;
                $new_feed->categories = $category_str;
                $new_feed->country_codes = $country_str;
                $new_feed->updated_at = date("Y-m-d H:i:s");
                $new_feed->feed_connection_id = $token;
                $new_feed->code = $result['id'];
                $new_feed->link = "";
                $new_feed->save(false);

                return json_encode($result);
            }
            $result['error'] = 'Board name already exist!';
            return json_encode($result);
        }
    }

    public function test($user_id, $token, $board_id, $category, $country) {
        $base_url  = "https://api.pinterest.com/v1/pins/?access_token=" . $token . "&fields=id";
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Pinterest Client-PHP/2.0.1',
        );
        $categories = json_decode($category);
        $countries = json_decode($country);
        foreach ($categories as $category) {

            $category_models = ProductCategory::find()->where(['user_id' => $user_id, 'category_id' => $category])->all();
            foreach ($category_models as $category_model) {
                $product_model = Product::find()->where(['id' => $category_model->product_id])->one();
                if (!empty($product_model)) {
                    $user_connection_model = $product_model->userConnection;
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

    public function actionUpdateBoard() {
        $id = "";
        $token = "";
        $board_name = "";
        $category = array();
        $country = array();
        if(isset($_POST['id'])){
            $id = $_POST['id'];
        }
        if(isset($_POST['token'])){
            $token = $_POST['token'];
        }
        if(isset($_POST['board_name'])){
            $board_name = $_POST['board_name'];
        }
        if (isset($_POST['category']) and ! empty($_POST['category'])) {
            $category = $_POST['category'];
        }
        $category_str = json_encode($category);
        if (isset($_POST['country']) and ! empty($_POST['country'])) {
            $country = $_POST['country'];
        }
        $country_str = json_encode($country);

        if (!$this->validToken($token)) {
            $result['error'] = 'Your access token invalid!';
            return json_encode($result);
        }
        else {
            $cur_feed = UserFeed::findOne(["id"=>$id]);
            if ($cur_feed->name == $board_name) {
                $cur_feed->name = $board_name;
                $cur_feed->feed_connection_id = $token;
                $cur_feed->categories = $category_str;
                $cur_feed->country_codes = $country_str;
                $cur_feed->updated_at = date("Y-m-d H:i:s");
                $cur_feed->save(false);

                $result['success'] = true;
                $result['error'] = '';
                return json_encode($result);
            }
            else {
                $base_url = "https://api.pinterest.com/v1/boards/?access_token=" . $token . "&fields=id";
                $headers = array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'User-Agent: Pinterest Client-PHP/2.0.1',
                );
                $params = array(
                    'name' => $board_name,
                    'description' => ''
                );

                $response = CustomFunction::curlHttp($base_url, $params, 'POST', $headers, 1);
                $json_response = json_decode($response, true);

                if (isset($json_response['data']) && !empty($json_response['data'])) {

                    $result['success'] = true;
                    $result['error'] = '';
                    $result['id'] = $json_response['data']['id'];

                    $cur_feed->name = $board_name;
                    $cur_feed->feed_connection_id = $token;
                    $cur_feed->categories = $category_str;
                    $cur_feed->country_codes = $country_str;
                    $cur_feed->code = $result['id'];
                    $cur_feed->updated_at = date("Y-m-d H:i:s");
                    $cur_feed->save(false);

                    return json_encode($result);
                }
                $result['error'] = 'Board name already exist!';
                return json_encode($result);
            }
        }
    }

    public function validToken($token) {

        $base_url  = "https://api.pinterest.com/v1/me/?access_token=" . $token . "&fields=id";
        $response = CustomFunction::curlHttp($base_url, null, 'GET', [], 1);
        $json_response = json_decode($response, true);

        if(isset($json_response['data']) && !empty($json_response['data'])) {
            return true;
        }
        return false;
    }
}