<?php

namespace frontend\controllers;

ob_start();

//session_start();

use common\models\UserConnectionDetails;
use Yii;
use common\models\User;                
use common\models\Connection;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Country;
use common\models\State;
use common\models\City;
use common\models\UserConnection;
use common\models\Product;
use common\models\ProductVariation;
use common\models\CurrencySymbol;
use common\models\Feed;
use common\models\UserFeed;
use frontend\models\search\CategorySearch;

/**
 * CustomerUserController implements the CRUD actions for CustomerUser model.
 */
class FacebookController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index', 'view', 'create', 'update','delete' , 'facebook'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'view', 'create', 'update','delete', 'facebook'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index', 'view', 'create', 'update','delete', 'facebook'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function beforeAction($action) {

        $this->enableCsrfValidation = false;


        return parent::beforeAction($action);
    }

    public function actionIndex() {
        $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
        $user_id = Yii::$app->user->identity->id;
        $userfeed_models = UserFeed::findAll(["user_id"=>$user_id, "feed_id"=>$fb_feed_model->id]);
        return $this->render('index', [
            "fb_feed_model" => $fb_feed_model,
            "userfeed_models" => $userfeed_models
        ]);
    }

    public function actionCreate() {
        $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
        $user_id = Yii::$app->user->identity->id;
        $feed_name = "";
        $selected_categories = [];
        $selected_countries = [];
        if (Yii::$app->request->post()) {
            if(isset($_POST['feed_name'])){
                $feed_name = $_POST['feed_name'];
            }
            if(isset($_POST['categories'])){
                $selected_categories = $_POST['categories'];
            }
            if(isset($_POST['countries'])){
                $selected_countries = $_POST['countries'];
            }
            if(isset($_POST['feed_name']) && isset($_POST['categories']) && isset($_POST['countries'])){
                $new_feed = new UserFeed();
                $new_feed->name = $feed_name;
                $new_feed->user_id = $user_id;
                $new_feed->feed_id = $fb_feed_model->id;
                $new_feed->categories = json_encode($selected_categories);
                $new_feed->country_codes = json_encode($selected_countries);
                $new_feed->updated_at = date("Y-m-d H:i:s");
                $new_feed->feed_connection_id = 0;
                $new_feed->save(false);
                $base_url = env('SEVER_URL');
                $new_feed->code = base64_encode("Base facebook:" . $new_feed->id);
                $new_feed->link = "{$base_url}{$fb_feed_model->link}?u_id={$new_feed->code}";
                $new_feed->save(false);
                return $this->redirect(['/facebook']);
            }
        }
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $countries_model = \Yii::$app->db->createCommand("SELECT A.*, B.`user_id` FROM user_connection_details AS A, user_connection AS B WHERE A.`user_connection_id` = B.`id` AND B.`user_id`={$user_id}  GROUP BY A.country_code ")->queryAll();
        $countries = [];
        foreach ($countries_model as $country){
            $country_name = Country::countryInfoFromCode($country['country_code']);
            if($country_name)
                $countries[] = $country_name;
        }
        return $this->render('create', [
                "action"=> "create",
                "feed_name" => $feed_name,
                "selected_categories" => $selected_categories,
                "selected_countries" => $selected_countries,
                "categories"=>$categories,
                "countries"=>$countries
            ]
        );
    }

    public function actionUpdate($id) {
        $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
        $cur_feed = UserFeed::findOne(["id"=>$id]);
        $user_id = Yii::$app->user->identity->id;
        $feed_name = $cur_feed->name;
        $selected_categories = json_decode($cur_feed->categories);
        $selected_countries = json_decode($cur_feed->country_codes);
        if (Yii::$app->request->post()) {
            if(isset($_POST['feed_name'])){
                $feed_name = $_POST['feed_name'];
            }
            if(isset($_POST['categories'])){
                $selected_categories = $_POST['categories'];
            }
            if(isset($_POST['countries'])){
                $selected_countries = $_POST['countries'];
            }
            if(isset($_POST['feed_name']) && isset($_POST['categories']) && isset($_POST['countries'])){
                $cur_feed->name = $feed_name;
                $cur_feed->categories = json_encode($selected_categories);
                $cur_feed->country_codes = json_encode($selected_countries);
                $cur_feed->updated_at = date("Y-m-d H:i:s");
                $cur_feed->code = base64_encode("Base facebook:" . $cur_feed->id);
                $base_url = env('SEVER_URL');
                $cur_feed->link = "{$base_url}{$fb_feed_model->link}?u_id={$cur_feed->code}";
                $cur_feed->save(false);
                return $this->redirect(['/facebook']);
            }
        }
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $countries_model = \Yii::$app->db->createCommand("SELECT A.*, B.`user_id` FROM user_connection_details AS A, user_connection AS B WHERE A.`user_connection_id` = B.`id` AND B.`user_id`={$user_id}  GROUP BY A.country_code ")->queryAll();
        $countries = [];
        foreach ($countries_model as $country){
            $countries[] = Country::countryInfoFromCode($country['country_code']);
        }
        return $this->render('update', [
                "action"=> "update?id=$id",
                "feed_name" => $feed_name,
                "selected_categories" => $selected_categories,
                "selected_countries" => $selected_countries,
                "categories"=>$categories,
                "countries"=>$countries
            ]
        );
    }

    public function actionFacebook() {
        return $this->redirect(['/facebook']);
    }
    public function actionDelete($id){
        $cur_feed = UserFeed::findOne(["id"=>$id]);
        $cur_feed->delete();
        return $this->redirect(['/facebook']);
    }


    function actionFeed($u_id) {
//header('Content-Type: application/xml');

        if (empty($u_id)) {
            echo 'Invalid User Id';
            die;
        }
        $u_id = base64_decode(base64_decode($u_id));
//        echo $u_id;
        $user = User::find()->where(['id' => $u_id])->one();
//        echo'<pre>';
//        print_r($user);
//        die;
        if (empty($user)) {
            echo 'Invalid User';
            die;
        }

        $products = Products::find()->with('productImages', 'productCategories.category', 'productChannels', 'productVariations')->asArray()->all();
//        echo"<pre>";        print_r($products);die;
        $items_xml = '';
        if (isset($products) and ! empty($products)) {
            foreach ($products as $single_product) {
                $items_xml .= '<item>
  <g:id>' . $single_product['id'] . '</g:id> 
  <g:title>' . str_replace("&", "and", $single_product['product_name']) . '</g:title> 
  <g:description>' . str_replace("&", "and", $single_product['product_name']) . '</g:description> 
  <g:link>http://www.example.com/bowls/db-1.html</g:link>';
                if (isset($single_product["brand"]) and ! empty($single_product["brand"])) {
                    $items_xml .= '<g:brand>' . $single_product["brand"] . '</g:brand>';
                } else {
                    $items_xml .= '<g:brand>Ravi</g:brand>';
                }
                $items_xml .= '<g:condition>new</g:condition> 
  <g:availability>' . $single_product["stock_level"] . '</g:availability> 
  <g:price>' . $single_product["price"] . '</g:price> 
  <g:google_product_category>Animals > Test Category</g:google_product_category> 
  <g:custom_label_0>Made in Waterford, IE</g:custom_label_0> ';
                if (isset($single_product["productImages"][0])) {
                    $items_xml .= ' <g:image_link>' . $single_product["productImages"][0]["link"] . '</g:image_link> ';
                } else {
                    $items_xml .= ' <g:image_link>http://google.com</g:image_link> ';
                }
                $items_xml .= '</item>';
            }
        }
//header('Content-Type: text/xml');
//        $input_request = "<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>
//- <channel>
//  <title>Test Store</title> 
//  <link>http://www.example.com</link> 
//  <description>An example item from the feed</description> 
//- <item>
//  <g:id>DB_1</g:id> 
//  <g:title>Dog Bowl In Blue</g:title> 
//  <g:description>Solid plastic Dog Bowl in marine blue color</g:description> 
//  <g:link>http://www.example.com/bowls/db-1.html</g:link> 
//  <g:image_link>http://images.example.com/DB_1.png</g:image_link> 
//  <g:brand>Example</g:brand> 
//  <g:condition>new</g:condition> 
//  <g:availability>in stock</g:availability> 
//  <g:price>9.99 GBP</g:price> 
//- <g:shipping>
//  <g:country>UK</g:country> 
//  <g:service>Standard</g:service> 
//  <g:price>4.95 GBP</g:price> 
//  </g:shipping>
//  <g:google_product_category>Animals > Pet Supplies</g:google_product_category> 
//  <g:custom_label_0>Made in Waterford, IE</g:custom_label_0> 
//  </item>
//  </channel>
//  </rss>";
        $BASE_URL = Yii::$app->params['BASE_URL'];
// $note = <<<XML
//<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>
//- <channel>
//  <title>Test Store</title>
//      <link>$BASE_URL</link> 
//  <description>An item from the feed</description>
//                $items_xml
//  </channel>
//  </rss>
//XML;

        $note = <<<XML
<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>
- <channel>
  <title>Test Store</title> 
  <link>$BASE_URL</link> 
  <description>An item from the feed</description> 
XML;
        $note .= <<<XML
                $items_xml
  </channel>
  </rss>
XML;

        $xml = new \SimpleXMLElement($note);
        echo $xml->asXML();
//echo $note;die;
//header('Content-Type: application/xml');
//$output = "<root><name>sample_name</name></root>";
//print ($output);
// echo 'hdfas';
//   die;
    }

}
