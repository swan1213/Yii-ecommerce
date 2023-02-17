<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 2/6/2018
 * Time: 8:41 PM
 */

namespace console\controllers;
use common\models\ConnectionParent;
use common\models\Product;
use common\models\ProductConnection;
use common\models\UserConnection;
use frontend\components\BigcommerceComponent;
use frontend\components\ChannelAmazonComponent;
use frontend\components\ChannelLazadaComponent;
use frontend\components\ChannelNeweggComponent;
use frontend\components\ChannelWechatComponent;
use frontend\components\Magento2Component;
use frontend\components\MagentoComponent;
use frontend\components\PosSquareComponent;
use frontend\components\ShopifyComponent;
use frontend\components\WoocommerceComponent;
use yii\console\Controller;

class ProductExportController extends Controller
{
    public function actionIndex(){
        echo "This is product-export.";
    }

    /**
     * Product update or insert to Channel.
     * @param integer $product_id
     * @param boolean $is_update if true, insert new product to Channel if false, update
     */
    public static function actionExport($product_id, $p_variant_id = 0) {

        $product =  Product::findOne(["id"=>$product_id]);
        $productConnections = ProductConnection::findAll(['product_id' => $product_id, 'status' => ProductConnection::STATUS_YES]);

        $return_response = [
            'success' => true,
            'product_id' => $product_id
        ];
        $return_msg = array();

        foreach ($productConnections as $productConnection) {
            $prodcut_connection_id = 0;
            $user_connection_id = $productConnection->user_connection_id;
            $userConnection = UserConnection::findOne(['id' => $user_connection_id]);
            $is_update = true;
            if ($productConnection->connection_product_id == "-1")
                $is_update = false;

            $response = null;
            if ( !empty($userConnection) ){

                $store_name = $userConnection->connection->name;
                if ( !empty($store_name) ) {

                    switch ($store_name) {
                        case 'BigCommerce':
                            $response = BigcommerceComponent::bgcUpstreamProduct($user_connection_id, $product_id, $is_update, $p_variant_id);
                            break;
                        case 'Shopify':
                        case 'ShopifyPlus':
                            $response = ShopifyComponent::shopifyUpstreamProduct($user_connection_id, $product_id, $is_update, $p_variant_id);
                            break;
                        case 'Magento':
                            if ($is_update)
                                $response = MagentoComponent::submitUpdatedProduct($user_connection_id, $product_id);
                            else
                                $response = MagentoComponent::submitNewProduct($user_connection_id, $product_id);
                            break;
                        case 'Magento2':
                            $response = Magento2Component::submitUpdatedProduct($user_connection_id, $product_id);
                            break;
                        case 'WooCommerce':
                            $response = WoocommerceComponent::wcUpdateProduct($user_connection_id, $product_id, $is_update);
                            break;
                        default:
                            break;
                    }

                }
                //$parent_store_name = $userConnection->parent->name;
                $connection_parent_id = $userConnection->connection->parent_id;

                if ( $connection_parent_id > 0 ){
                    $parentConnection = ConnectionParent::findOne(['id' => $connection_parent_id]);
                    $parent_connection_name = $parentConnection->name;
                    switch ($parent_connection_name) {
                        case 'Lazada':
                            $response = ChannelLazadaComponent::feedProduct($user_connection_id, $product_id, $is_update);
                            break;
                        case 'Amazon':
                            $response = ChannelAmazonComponent::feedProduct($user_connection_id, $product_id, $is_update);
                            break;
                        case 'Newegg':
                            $response = ChannelNeweggComponent::feedProduct($user_connection_id, $product_id, $is_update);
                            break;
                        case 'WeChat':
                            $response = ChannelWechatComponent::feedProduct($user_connection_id, $product_id, $is_update);
                            break;
                        case 'Square':
                            $response = PosSquareComponent::feedProduct($user_connection_id, $product_id, $is_update);
                            break;
                        default:
                            break;
                    }
                }

                $json_response = json_decode($response, true);
                if(isset($json_response['success']) && $json_response['success']) {
                    if(isset($json_response['connection_product_id']) && $json_response['connection_product_id'] > 0){
                        $product_connection = ProductConnection::find()->where(
                            [
                                'user_id' => $userConnection->user_id,
                                'user_connection_id' => $user_connection_id,
                                'product_id' => $product_id
                            ])->one();
                        if (!empty($product_connection)) {
                            $product_connection->connection_product_id = $json_response['connection_product_id'];
                            $product_connection->save(false);
                        }
                    }
                }
                else {
                    if(isset($json_response['message'])) {
                        $return_response['success'] = false;
                        $fail_response['user_connection_id'] = $user_connection_id;
                        $fail_response['message'] = $json_response['message'];
                        $return_msg[] = $fail_response;
                    }
                }
            }
        }

        $return_response['msg'] = $return_msg;
        return json_encode($return_response, JSON_UNESCAPED_UNICODE);
    }

    public function actionDelete($product_id) {

        $productConnections = ProductConnection::findAll(['product_id' => $product_id, 'status' => ProductConnection::STATUS_NO]);

        foreach ($productConnections as $productConnection) {
            $user_connection_id = $productConnection->user_connection_id;
            $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

            $connection_product_id = $productConnection->connection_product_id;
            if ($productConnection->connection_product_id == "-1" || $productConnection->connection_product_id == "0")
                continue;

            $result = false;

            if ( !empty($userConnection) ){
                $store_name = $userConnection->connection->name;
                if ( !empty($store_name) ) {
                    switch ($store_name) {
                        case 'Magento':
                            $result = MagentoComponent::deleteProduct($user_connection_id, $product_id);
                            break;
                        case 'Magento2':
                            $result = Magento2Component::deleteProduct($user_connection_id, $product_id);
                            break;
                        case 'WooCommerce':
                            $result = WoocommerceComponent::wcDeleteProduct($user_connection_id, $connection_product_id);
                            break;
                        case 'BigCommerce':
                            $result = BigcommerceComponent::bgcDeleteProduct($user_connection_id, $connection_product_id);
                            break;
                        case 'Shopify':
                        case 'ShopifyPlus':
                            $result = ShopifyComponent::shopifyDeleteProduct($user_connection_id, $connection_product_id);
                            break;
                        default:
                            break;
                    }

                }

                //$parent_store_name = $userConnection->parent->name;
                $connection_parent_id = $userConnection->connection->parent_id;

                if ( $connection_parent_id > 0 ){
                    $parentConnection = ConnectionParent::findOne(['id' => $connection_parent_id]);
                    $parent_connection_name = $parentConnection->name;
                    switch ($parent_connection_name) {
                        case 'Lazada':
                            $result = ChannelLazadaComponent::deleteProduct($user_connection_id, $product_id);
                            break;
                        case 'Amazon':
                            $result = ChannelAmazonComponent::deleteProduct($user_connection_id, $product_id);
                            break;
                        case 'Newegg':
                            break;
                        case 'WeChat':
                            $result = ChannelWechatComponent::deleteProduct($user_connection_id, $product_id);
                            break;
                        case 'Square':
                            $result = PosSquareComponent::deleteProduct($user_connection_id, $product_id);
                            break;
                        default:
                            break;
                    }
                }

                if($result){
                    $product_connection = ProductConnection::find()->where(['user_id' => $userConnection->user_id, 'user_connection_id' => $user_connection_id, 'product_id' => $product_id])->one();
                    if (!empty($product_connection)) {
                        $product_connection->connection_product_id = "-1";
                        $product_connection->save(false);
                    }
                }
            }

        }

    }
}