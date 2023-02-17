<?php

namespace frontend\modules\feed\controllers;


use common\models\UserFeed;
use SimpleXMLElement;
use Yii;
use yii\base\Controller;


class FacebookController extends Controller
{

    public function actionIndex()
    {
        if(isset($_GET["u_id"])){
            $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $u_id = $_GET["u_id"];
            $feed_model = UserFeed::findOne(["link"=>$actual_link]);
            if(empty($feed_model)){
                return "Unknown Request!";
                exit;
            }
            $user_id = $feed_model->user_id;
            $selected_categories = json_decode($feed_model->categories);
            if(empty($selected_categories)){
                return "Unknown Request!";
                exit;
            }
            $selected_countries = json_decode($feed_model->country_codes);
            if(empty($selected_countries)){
                return "Unknown Request!";
                exit;
            }
            $user_connection_query = "select user_connection_id from user_connection_details where ";
            $i=0;
            foreach ($selected_countries as $country){
                if($i==0)
                    $user_connection_query = $user_connection_query . "country_code='{$country}'";
                else
                    $user_connection_query = $user_connection_query .  " OR country_code='{$country}'";
                $i++;
            }
            $user_connections_detail = \Yii::$app->db->createCommand($user_connection_query)->queryAll();
            $query = "SELECT    A.*, 
                                (SELECT `name` FROM category WHERE id = B.category_id) AS cat_name,
                                (SELECT `link` FROM `product_image` WHERE product_id = A.id LIMIT 1) AS product_img
                      FROM      `product` AS A, 
                                `product_category` AS B, 
                                `product_connection` AS C
                      WHERE   A.user_id = $user_id AND 
                              B.product_id = A.id  AND (";
            $i=0;
            foreach ($selected_categories as $category){
                if($i==0)
                    $query = $query .  "B.category_id =$category";
                else
                    $query = $query .  " Or B.category_id =$category";
                $i++;
            }
            $query = $query .  ") AND C.`product_id`=A.`id` AND (";
            $i=0;
            if(empty($user_connections_detail)){
                return "No Product";
                exit;
            }
            foreach ($user_connections_detail as $user_connections){
                if($i==0)
                    $query = $query .  " C.`user_connection_id` ={$user_connections['user_connection_id']}";
                else
                    $query = $query .  " Or C.`user_connection_id` ={$user_connections['user_connection_id']}";
                $i++;
            }
            $query = $query .  ")";
            $products = \Yii::$app->db->createCommand($query)->queryAll();

            $items_xml = '';
            if (isset($products) and ! empty($products)) {
                foreach ($products as $single_product) {
                    $id = $single_product['id'] ? $single_product['id'] : "";
                    $name = htmlspecialchars(strip_tags($single_product['name'] ? $single_product['name'] : ""));
                    $description = htmlspecialchars(strip_tags($single_product['description'] ? $single_product['description'] : $name));
                    $url = htmlspecialchars(strip_tags($single_product['url'] ? $single_product['url'] : ""));
                    $product_img = htmlspecialchars(strip_tags($single_product['product_img'] ? $single_product['product_img'] : ""));
                    $brand = htmlspecialchars(strip_tags($single_product['brand'] ? $single_product['brand'] : "Elliot"));
                    $condition = htmlspecialchars(strip_tags($single_product['condition'] ? $single_product['condition'] : "New"));
                    $stock_level = htmlspecialchars(strip_tags($single_product['stock_level'] ? $single_product['stock_level'] : ""));
                    $price = htmlspecialchars(strip_tags($single_product['price'] ? $single_product['price'] : "0"));
                    $currency = htmlspecialchars(strip_tags($single_product['currency'] ? $single_product['currency'] : "USD"));
                    $country_code = htmlspecialchars(strip_tags($single_product['country_code'] ? $single_product['country_code'] : "US"));
                    $cat_name = htmlspecialchars(strip_tags($single_product['cat_name'] ? $single_product['cat_name'] : ""));
                    $type = htmlspecialchars(strip_tags($single_product['type'] ? $single_product['type'] : ""));
                    $items_xml .= "<item>
                                        <g:id>$id</g:id>
                                        <g:title>$name</g:title>
                                        <g:description>$description</g:description>
                                        <g:link>$url</g:link>
                                        <g:image_link>$product_img</g:image_link>
                                        <g:brand>$brand</g:brand>
                                        <g:condition>$condition</g:condition>
                                        <g:availability>$stock_level</g:availability>
                                        <g:price>$price $currency</g:price>
                                        <g:shipping>
                                            <g:country>$country_code</g:country>
                                            <g:service>Standard</g:service>
                                            <g:price>$price $currency</g:price>
                                        </g:shipping>
                                        <g:google_product_category>$cat_name</g:google_product_category>
                                        <g:custom_label_0>$type</g:custom_label_0>
                                    </item>";
                }
            }

            $base_url = env('SEVER_URL');
            $note = "<?xml version=\"1.0\"?>
                    <rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">
                        <channel>
                        <title>Elliot Store</title>
                        <link>$base_url</link>
                        <description>An Elliot item from the feed</description>
                        $items_xml
                    </channel>
                    </rss>";
            $xml = new SimpleXMLElement($note);
            echo $xml->asXML();
        }
        else{
            echo("Unknown Request!");
        }
    }

}