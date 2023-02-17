<?php

namespace frontend\controllers;

use common\models\Category;
use common\models\CurrencySymbol;
use common\models\ConnectionCategoryList;
use common\models\ProductCategory;
use common\models\ProductConnection;
use common\models\ProductImage;
use common\models\ProductTranslation;
use common\models\User;
use common\models\UserConnection;
use common\models\UserPermission;
use common\models\VariationValue;
use console\controllers\ProductExportController;
use frontend\components\ConsoleRunner;
use frontend\components\CustomFunction;
use frontend\modules\api\v1\resources\OrderProduct;
use Yii;
use yii\filters\AccessControl;
use frontend\components\BaseController;

use common\models\Product;
use frontend\models\search\ProductSearch;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use common\models\Variation;
use common\models\VariationItem;
use common\models\VariationSet;
use common\models\ProductVariation;
use common\models\Attribution;
use common\models\AttributionType;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends BaseController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'index', 'view', 'create', 'index', 'update', 'google-product-category', 'update-translation',
                    'update-product', 'product-image-upload', 'create-product', 'category-add', 'channel-add', 'inventory-add',
                    'sale-add', 'save-product-variations', 'remove-product-variations', 'add-product-variations-row',
                    'get-opset-variations', 'get-attributes', 'ajax-product-delete',
                    'connected-products', 'test', 'inactive-products', ''],
                'rules' => [
                    [
                        'actions' => ['products-ajax'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'logout', 'index', 'view', 'create', 'index', 'update', 'update-translation',
                            'google-product-category', 'update-product', 'product-image-upload', 'channel-add', 'inventory-add', 'sale-add',
                            'create-product', 'category-add', 'save-product-variations', 'remove-product-variations',
                            'add-product-variations-row', 'get-opset-variations', 'get-attributes', 'ajax-product-delete',
                            'connected-products', 'test', 'inactive-products', 'products-ajax'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    ' delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Disable CSRF Validation
     * @param type $action
     * @return type
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex() {

        $currentUserId = Yii::$app->user->identity->id;
        $products = Product::find()->Where(['user_id' => $currentUserId])->all();
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->searchInDB(Yii::$app->request->queryParams, $currentUserId);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'products' => $products,
        ]);
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionConnectedProducts() {
        return $this->render('connectedproducts');
    }

    public function getCategoryConcatenation($connection_category_id, $ids) {
        $connection_category_row = ConnectionCategoryList::find()->where([
            'connection_parent_id' => 10,
            'category_connection_id' => $connection_category_id])->one();

        if(empty($connection_category_row)) {
            return $ids;
        }

        if($connection_category_row->parent_id < 1) {
            return $ids;
        }

        array_push($ids, $connection_category_row->category_connection_id);

        return $this->getCategoryConcatenation($connection_category_row->parent_id, $ids);
    }

    public function listCategories($category_list, $connection_category_id, $ids) {
        $html = '';

        foreach ($category_list as $single_category) {
            $child_categories = ConnectionCategoryList::find()->where(['connection_parent_id' => 10, 'parent_id' => $single_category->category_connection_id])->all();

            if(!empty($child_categories)) {
                $in = '';
                $icon_name = 'glyphicon-chevron-right';

                if(in_array($single_category->category_connection_id, $ids)) {
                    $in = 'in';
                    $icon_name = 'glyphicon-chevron-down';
                }

                $html .= sprintf(
                    '<a href="#item_%s" class="list-group-item" data-toggle="collapse">
                        <i class="glyphicon %s"></i>%s
                    </a>

                    <div class="list-group collapse %s" id="item_%s">'
                    .$this->listCategories($child_categories, $connection_category_id, $ids).
                    '</div>',
                    $single_category->category_connection_id,
                    $icon_name,
                    $single_category->name,
                    $in,
                    $single_category->category_connection_id
                );
            } else {
                $checked = '';

                if($single_category->category_connection_id == $connection_category_id) {
                    $checked = 'cur checked';
                }

                $html .= sprintf(
                    '<a href="#" class="list-group-item %s" cate="%s">%s</a>',
                    $checked,
                    $single_category->category_connection_id,
                    $single_category->name
                );
            }
        }

        return $html;
    }

    public function actionSetLazadaCategory() {
        $post_data = Yii::$app->request->post();

        try {
            $product_id = $post_data['lazada_product_id'];
            $category_id = $post_data['lazada_category_id'];
            $user_id = Yii::$app->user->identity->id;

            if(empty($product_id)) {
                throw new \Exception('Empty product id.');
            }

            if(empty($category_id)) {
                throw new \Exception('Empty category id.');
            }

            $product_row = Product::find()->where([
                'id' => $product_id
            ])->one();

            if(empty($product_row)) {
                throw new \Exception('Invalid product id');
            }

            $user_connection_rows = UserConnection::find()->where([
                'user_id' => $user_id,
                'connection_id' => [30, 31, 32, 33, 34, 35]
            ])->all();

            if(empty($user_connection_rows) or count($user_connection_rows) == 0) {
                throw new \Exception('You need to connect to any lazada channel.');
            }

            if(!empty($product_row->productCategories)) {
                foreach ($product_row->productCategories as $single_product_category) {
                    if($single_product_category->user_id == $user_id) {
                        $user_connection_id = $single_product_category->category->user_connection_id;
                        $user_connection_row = UserConnection::find()->where([
                            'id' => $user_connection_id
                        ])->one();

                        if(!empty($user_connection_row)) {
                            if($user_connection_row->connection_id >= 30 and $user_connection_row->connection_id <= 35) {
                                $single_product_category->delete();
                            }
                        }
                    }
                }
            }

            foreach ($user_connection_rows as $single_user_connection) {
                $category_row = Category::find()->where([
                    'user_id' => $user_id,
                    'user_connection_id' => $single_user_connection->id,
                    'connection_category_id' => $category_id
                ])->one();

                $lazada_category_row = ConnectionCategoryList::find()->where([
                    'connection_parent_id' => 10,
                    'category_connection_id' => $category_id
                ])->one();

                if(empty($category_row)) {
                    $category_row = new Category();
                }
                $category_row->name = $lazada_category_row->name;
                $category_row->parent_id = 0;
                $category_row->user_id = $user_id;
                $category_row->user_connection_id = $single_user_connection->id;
                $category_row->connection_category_id = $lazada_category_row->category_connection_id;
                $category_row->connection_parent_id = $lazada_category_row->parent_id;
                $category_row->save(false);
            }

            $product_category_row = ProductCategory::find()->where([
                'user_id' => $user_id,
                'category_id' => $category_row->id,
                'product_id' => $product_id
            ])->one();

            if(empty($product_category_row)) {
                $product_category_row = new ProductCategory();
            }

            $product_category_row->user_id = $user_id;
            $product_category_row->category_id = $category_row->id;
            $product_category_row->product_id = $product_id;
            $product_category_row->save(false);

            echo json_encode([
                'success' => true,
                'message' => 'Category is set successfully!'
            ]);
        } catch(\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetLazadaCategories($product_id) {
        $product_row = Product::find()->where([
            'id' => $product_id
        ])->one();
        $user_id = Yii::$app->user->identity->id;
        $connection_category_id = -1;

        if(!empty($product_row)) {
            if(!empty($product_row->productCategories)) {
                $user_connection_rows = UserConnection::find()->where([
                    'user_id' => $user_id,
                    'connection_id' => [30, 31, 32, 33, 34, 35]
                ])->all();

                if(!empty($user_connection_rows) and count($user_connection_rows) > 0) {
                    $ids = array_column($user_connection_rows, 'id');

                    foreach ($product_row->productCategories as $single_product_category) {
                        if($single_product_category->user_id == $user_id) {
                            if(in_array($single_product_category->category->user_connection_id, $ids)) {
                                $connection_category_id = $single_product_category->category->connection_category_id;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $lazada_categories = ConnectionCategoryList::find()->where(['connection_parent_id' => 10, 'parent_id' => 0])->all();
        $ids = [];
        $ids = $this->getCategoryConcatenation($connection_category_id, $ids);
        $lazada_category_html = $this->listCategories($lazada_categories, $connection_category_id, $ids);
        echo $lazada_category_html;
        die;
    }

    /**
     * Displays a single Products model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        $users_Id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $users_Id = Yii::$app->user->identity->parent_id;
        $attribute_types = AttributionType::find()->where(['user_id' => $users_Id])->all();

        return $this->render('view', [
            'product_model' => $this->findModel($id),
            'attribute_types' => $attribute_types
        ]);
    }

    public function actionGetchannel() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        //$store_connection = UserConnection::find()->where(['user_id' => $users_id])->available()->all();
        $store_connection = UserConnection::find()->where(['user_id' => $user_id])->all();
        $connections = array();
        foreach ($store_connection as $con) {
            if ($con->id == User::getDefaultConnection($user_id)) {
                continue;
            }
            $arr[] = array("value" => $con->id, "text" => $con->getPublicName());
        }
        echo json_encode($arr);
        die;
    }

    /**
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $users_Id = Yii::$app->user->identity->id;
        //$model = new Product($users_Id);
        $model = new Product();
        $store_connection = UserConnection::find()->where(['user_id' => $users_Id])->available()->all();

        $connections = array();
        foreach ($store_connection as $con) {
            $connection['id'] = $con->connection->id;
            $connection['name'] = $con->getPublicName();
            $connections[] = $connection;
        }

        $attribute_types = AttributionType::find()->where(['user_id' => $users_Id])->all();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'connections' => $connections,
                'attribute_types' => $attribute_types,
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionUpdateTranslation(){
        $post_data = Yii::$app->request->post();

        $smartling_id = $post_data['smartling_id'];
        $description = $post_data['description'];
        $brand = $post_data['brand'];
        $title = $post_data['title'];

        $product_translation = ProductTranslation::findOne(['id'=>$smartling_id]);
        $product_translation->description = $description;
        $product_translation->brand = $brand;
        $product_translation->name = $title;
        $product_translation->save(false);
    }
    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /* Function for Google Product Categories */

    public function actionGoogleProductCategory() {
        $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : $_REQUEST['term'];
        $sql = "SELECT google_category_name FROM google_product_categories WHERE google_category_name LIKE '{$query}%'";
        $google_categories = GoogleProductCategories::findBySql($sql)->all();
        $cat_array = array();
        foreach ($google_categories as $cat) {
            $cat_array[] = $cat->google_category_name;
        }
        //RETURN JSON ARRAY
        return json_encode($cat_array);
    }

    /* Update the Single Product Details */

    public function actionUpdateProduct($pId) {

        $user_id = Yii::$app->user->identity->id;
        $post_data = Yii::$app->request->post();

        $product_model = $this->findModel($pId);
        $salePrice = preg_replace('/[^a-zA-Z0-9_.]/', '', $post_data['pSale_price']);
        //$price = preg_replace('/[^a-zA-Z0-9_.]/', '', $post_data['pPrice']);

        // Price Conversion as Per the Currency Selected( NOT IN CASE OFUSD) By User Starts
        $user = Yii::$app->user->identity;
        $conversion_rate = 1;

        //$price = $price * $conversion_rate;
        $salePrice = $salePrice * $conversion_rate;
        //$price = number_format((float) $price, 2, '.', '');
        $salePrice = number_format((float) $salePrice, 2, '.', '');
        // Price Conversion as Per the Currency Selected( NOT IN CASE OFUSD) By User Ends
        $stock_qty = $post_data['pStk_qty'];
        $low_stc_ntf = $post_data['plow_stk_ntf'];
        if ($post_data['plow_stk_ntf'] == 'Empty') :
            $low_stc_ntf = NULL;
        endif;

        $pstk_status = $post_data['pStk_status'];

        if ($pstk_status == "Visible") :
            $update_pstk_status = "simple";
        else :
            $update_pstk_status = "none";
        endif;

        $pcat = isset($_POST['pcat']) ? $_POST['pcat'] : '';
        //Delete All product categories acc to product id

        if (!empty($pcat)) :
            $p_cat_data = ProductCategory::deleteAll('product_id = :product_ID', [':product_ID' => $pId]);
            foreach ($pcat as $pcat_data) :
                $pcat_data = str_replace("&amp;", "&", $pcat_data);
                $cat_data = Category::find()->Where(['name' => $pcat_data])->one();
                $cat_id = $cat_data->id;
                $product_category_model = new ProductCategory();
                $product_category_model->user_id = Yii::$app->user->identity->id;
                $product_category_model->category_id = $cat_id;
                $product_category_model->product_id = $pId;
                $product_category_model->created_at = date('Y-m-d H:i:s', time());
                $product_category_model->save(false);
            endforeach;
        endif;
        /* Allocate Inventorey */
        $elli_allocate_inv = $post_data['elli_allocate_inv'];

        $channel_sale = isset($post_data['channel_sale']) ? $post_data['channel_sale'] : '';

        $product_model->name = $post_data['pName'];
        $product_model->sku = $post_data['pSKU'];
        $product_model->hts = $post_data['pHTS'];
        $product_model->upc = $post_data['pUPC'];
        $product_model->ean = $post_data['pEAN'];
        $product_model->jan = $post_data['pJAN'];
        $product_model->isbn = $post_data['pISBN'];
        $product_model->mpn = $post_data['pMPN'];
        $product_model->description = $post_data['pDescription'];
        if ($post_data['pAdult'] == 'Empty') :
            $post_data['pAdult'] = 'no';
        endif;
        $product_model->adult = $post_data['pAdult'];
        if ($post_data['pAgeGroup'] == 'Empty') :
            $post_data['pAgeGroup'] = NULL;
        endif;
        $product_model->age_group = $post_data['pAgeGroup'];
        if ($post_data['pAvail'] == 'Empty') :
            $post_data['pAvail'] = 'Out of Stock';
        endif;

        $product_model->brand = $post_data['pBrand'];
        if ($post_data['pCond'] == 'Empty') :
            $post_data['pCond'] = 'New';
        endif;
        $product_model->condition = $post_data['pCond'];
        if ($post_data['pGender'] == 'Empty') :
            $post_data['pGender'] = 'Unisex';
        endif;
        $product_model->weight = $post_data['pWeight'];
        $product_model->package_length = $post_data['package_length'];
        $product_model->package_height = $post_data['package_height'];
        $product_model->package_width = $post_data['package_width'];
        $product_model->package_box = $post_data['package_box'];
        //Inventory Management Tab
        $product_model->stock_quantity = $post_data['pStk_qty'];
        $product_model->allocate_inventory = $elli_allocate_inv;
        //$product_model->stock_level = $_POST['pStk_lvl'];
        $product_model->stock_status = $post_data['pStk_status'];
        $product_model->low_stock_notification = $low_stc_ntf;
        //Pricing Tab
        //$product_model->price = $price;
        $product_model->sales_price = $salePrice;
        $schedule_date = $_POST['pSchedule_date'];
        $schedule_date_time = date('Y-m-d h:i:s', strtotime($schedule_date));
        $product_model->schedule_sales_date = $schedule_date_time;

        /* Check for update Product in Stores and channels */
        if (!empty($channel_sale)) {
            $store_infos = array();
            $store_ids = array();
            $userConnections = UserConnection::find()->where(['user_id' => $user_id])->all();
            foreach ($userConnections as $userConnection) {
                $store_info['name'] = $userConnection->getPublicName();
                $store_info['id'] = $userConnection->id;
                $store_infos[] = $store_info;
            }

            foreach ($channel_sale as $sale) {
                foreach ($store_infos as $store_info) {
                    if (strcmp($sale, $store_info['name']) == 0) {
                        $store_ids[] = $store_info['id'];
                    }
                }
            }

            foreach ($store_ids as $store_id) {
                $product_connection = ProductConnection::find()->where(['product_id' => $pId, 'user_connection_id' => $store_id])->one();
                if (!empty($product_connection)) {
                    $product_connection->status = ProductConnection::STATUS_YES;
                    $product_connection->save(true, ['status']);
                }
                else {
                    $product_connection = new ProductConnection();
                    $product_connection->user_id = $user_id;
                    $product_connection->user_connection_id = $store_id;
                    $product_connection->status = ProductConnection::STATUS_YES;
                    $product_connection->product_id = $pId;
                    $product_connection->save(false);
                }
            }

            //Variation
            $product_store_variants_rows = ProductVariation::find()
                ->where(['product_id' => $pId, 'user_id' => $user_id])
                ->distinct()
                ->groupBy('sku_value')
                ->all();
            if (!empty($product_store_variants_rows)) {
                foreach ( $product_store_variants_rows as $variant_sku_row ) {
                    foreach ($store_ids as $store_id) {
                        $count = $variant_sku_row->inventory_value/sizeof($store_ids);
                        $percent = number_format((float) 100/sizeof($store_ids) , 2, '.', '');
                        $product_connection_id = $store_id;
                        $productVariation = ProductVariation::findOne(['user_id' => $user_id, 'sku_value'=> $variant_sku_row->sku_value, 'product_id' => $pId, 'user_connection_id' => $product_connection_id]);
                        if (!empty($productVariation)) {

                            $productVariation->allocate_inventory = $count;
                            $productVariation->allocate_percent = $percent;
                            $productVariation->save(false);
                        }
                        else {
                            $tmp = ProductVariation::findOne(['user_id' => $user_id, 'user_connection_id' => $product_connection_id]);
                            $newProductVariation = new ProductVariation();
                            $newProductVariation->user_id = $user_id;
                            $newProductVariation->variation_id = $variant_sku_row->variation_id;
                            $newProductVariation->product_id = $variant_sku_row->product_id;
                            $newProductVariation->sku_key = !empty($tmp) ? $tmp->sku_key : $variant_sku_row->sku_key;
                            $newProductVariation->sku_value = $variant_sku_row->sku_value;
                            $newProductVariation->inventory_key = !empty($tmp) ? $tmp->inventory_key : $variant_sku_row->inventory_key;
                            $newProductVariation->inventory_value = $variant_sku_row->inventory_value;
                            $newProductVariation->price_key = !empty($tmp) ? $tmp->price_key : $variant_sku_row->price_key;
                            $newProductVariation->price_value = $variant_sku_row->price_value;
                            $newProductVariation->weight_key = !empty($tmp) ? $tmp->weight_key : $variant_sku_row->weight_key;
                            $newProductVariation->weight_value = $variant_sku_row->weight_value;
                            $newProductVariation->variation_set_id = $variant_sku_row->variation_set_id;
                            $newProductVariation->connection_variation_id = 0;
                            $newProductVariation->user_connection_id = $product_connection_id;
                            $newProductVariation->allocate_inventory = $count;
                            $newProductVariation->allocate_percent = $percent;
                            $newProductVariation->save(false);
                        }
                    }
                }
            }

            $deleted_product_connections = ProductConnection::find()->where(['product_id' => $pId])
                ->andWhere(['not', ['in', 'user_connection_id', $store_ids]])->all();
            if (!empty($deleted_product_connections)) {
                foreach ($deleted_product_connections as $deleted_product_connection) {
                    $deleted_product_connection->status = ProductConnection::STATUS_NO;
                    $deleted_product_connection->save(false);
                }
            }

        }
        else {
            //$product_model->user_connection_id = User::getDefaultConnection($user_id);
            $product_connections = ProductConnection::find()->where(['product_id' => $pId])->all();
            foreach ($product_connections as $product_connection) {
                $product_connection->status = ProductConnection::STATUS_NO;
                $product_connection->save(false);
            }
        }

        if ($product_model->save(false)) {
            $response = $this->productUpdate($pId, 0, false);
            $json_response = json_decode($response, true);
            if(isset($json_response['success']) && $json_response['success']) {
                Yii::$app->session->setFlash('success', 'Success! Product has been updated.');
                return $this->redirect(['view', 'id' => $product_model->id]);
            }
            else {
                //Yii::$app->session->setFlash('danger', 'Error! Product has not been updated.');
                $json_msg = $json_response['msg'];
                $fail_message = '';
                foreach ($json_msg as $msg) {
                    $user_connection_id = $msg['user_connection_id'];
                    $userConnection = UserConnection::find()->where(['id' => $user_connection_id])->one();
                    if (!empty($userConnection)) {
                        $fail_message = $fail_message.$userConnection->getPublicName().'<br />'.$msg['message'].'<br />';
                    }
                }
                $return_result['success'] = false;
                $return_result['message'] = $fail_message;
                return json_encode($return_result, JSON_UNESCAPED_UNICODE);
            }

        } else {
            Yii::$app->session->setFlash('danger', 'Error! Product has not been updated.');
            return $this->redirect(['view', 'id' => $product_model->id]);
        }
    }

    public function actionEnableIq() {
        $post = Yii::$app->request->post();
        $stockManFlag = isset($post['stockMan']) ? $post['stockMan'] : null;
        $productId = isset($post['productId']) ? $post['productId'] : null;

        $product_model = $this->findModel($productId);
        if (!empty($product_model) && isset($stockManFlag) && !empty($stockManFlag)) {
            $product_model->stock_manage = $stockManFlag;
            $product_model->save(false);
        }

        if ($stockManFlag == "Yes") {
            $user_id = Yii::$app->user->identity->id;
            if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
                $user_id = Yii::$app->user->identity->parent_id;

            $product = Product::find()->where(['id' => $productId])->one();
            $product_connections = ProductConnection::find()->where(['product_id' => $productId, 'status' => ProductConnection::STATUS_YES])->all();
            $product_store_variants_rows = ProductVariation::find()
                ->where(['product_id' => $productId, 'user_id' => $user_id])
                ->distinct()
                ->groupBy('sku_value')
                ->all();
            if (!empty($product_store_variants_rows)) {
                foreach ( $product_store_variants_rows as $variant_sku_row ) {
                    foreach ($product_connections as $product_connection) {
                        if ($product_connection->status == ProductConnection::STATUS_YES) {

                            $count = $variant_sku_row->inventory_value/sizeof($product_connections);
                            $percent = number_format((float) 100/sizeof($product_connections) , 2, '.', '');
                            $product_connection_id = $product_connection->user_connection_id;
                            $productVariation = ProductVariation::findOne(['user_id' => $user_id, 'sku_value'=> $variant_sku_row->sku_value, 'product_id' => $productId, 'user_connection_id' => $product_connection_id]);
                            if (!empty($productVariation)) {

                                $productVariation->allocate_inventory = $count;
                                $productVariation->allocate_percent = $percent;
                                $productVariation->save(false);
                            }
                            else {
                                $tmp = ProductVariation::findOne(['user_id' => $user_id, 'user_connection_id' => $product_connection_id]);
                                $newProductVariation = new ProductVariation();
                                $newProductVariation->user_id = $user_id;
                                $newProductVariation->variation_id = $variant_sku_row->variation_id;
                                $newProductVariation->product_id = $variant_sku_row->product_id;
                                $newProductVariation->sku_key = !empty($tmp) ? $tmp->sku_key : $variant_sku_row->sku_key;
                                $newProductVariation->sku_value = $variant_sku_row->sku_value;
                                $newProductVariation->inventory_key = !empty($tmp) ? $tmp->inventory_key : $variant_sku_row->inventory_key;
                                $newProductVariation->inventory_value = $variant_sku_row->inventory_value;
                                $newProductVariation->price_key = !empty($tmp) ? $tmp->price_key : $variant_sku_row->price_key;
                                $newProductVariation->price_value = $variant_sku_row->price_value;
                                $newProductVariation->weight_key = !empty($tmp) ? $tmp->weight_key : $variant_sku_row->weight_key;
                                $newProductVariation->weight_value = $variant_sku_row->weight_value;
                                $newProductVariation->variation_set_id = $variant_sku_row->variation_set_id;
                                $newProductVariation->connection_variation_id = 0;
                                $newProductVariation->user_connection_id = $product_connection_id;
                                $newProductVariation->allocate_inventory = $count;
                                $newProductVariation->allocate_percent = $percent;
                                $newProductVariation->save(false);
                            }
                        }
                    }
                }
            }
        }

        $response_ajax['success'] = true;
        return json_encode($response_ajax);
    }

    public function actionStockParentUpdate() {
        $post = Yii::$app->request->post();

        $variationId = isset($post['variationId']) ? $post['variationId'] : null;
        $stockValue = isset($post['stockVal']) ? $post['stockVal'] : null;
        $sku = isset($post['sku']) ? $post['sku'] : null;
        $productId = isset($post['productId']) ? $post['productId'] : null;

        $response_ajax = [];

        if (isset($sku) && !empty($sku) && isset($productId) && !empty($productId)) {

            $productVariations = ProductVariation::find()->where(['sku_value' => $sku, 'product_id' => $productId])->all();
            if (!empty($productVariations)) {
                foreach ($productVariations as $productVariation) {
                    $productVariation->inventory_value = $stockValue;
                    $productVariation->save(false);
                }
            }
        }
        $response_ajax['success'] = true;

        return json_encode($response_ajax);
    }

    public function actionStockUpdate() {
        $post = Yii::$app->request->post();

        $variationId = isset($post['variationId']) ? $post['variationId'] : null;
        $stockValue = isset($post['stockVal']) ? $post['stockVal'] : null;
        $stockManFlag = isset($post['stockMan']) ? $post['stockMan'] : null;
        $productId = isset($post['productId']) ? $post['productId'] : null;

        $response_ajax = [];
        $product_model = $this->findModel($productId);
        if (!empty($product_model) && isset($stockManFlag) && !empty($stockManFlag)) {
            $product_model->stock_manage = $stockManFlag;
            $product_model->save(false);
        }

        if (isset($variationId) && !empty($variationId) && isset($stockValue) && !empty($stockValue)) {

            $productVariation = ProductVariation::findOne(['id' => $variationId]);
            if (!empty($productVariation)) {
                $prevVal = $productVariation->allocate_inventory;
                if ($prevVal != $stockValue) {
                    $productVariation->allocate_inventory = $stockValue;
                    $productVariation->save(false);
                    $p_variant_id = $productVariation->id;
                    $this->productUpdate($productId, $p_variant_id);
                }
            }
        }
        $response_ajax['success'] = true;

        return json_encode($response_ajax);
    }

    public function actionStockPercentUpdate() {
        $post = Yii::$app->request->post();

        $variationId = isset($post['variationId']) ? $post['variationId'] : null;
        $percentVal = isset($post['percentVal']) ? $post['percentVal'] : null;
        $productId = isset($post['productId']) ? $post['productId'] : null;

        $response_ajax = [];

        if (isset($variationId) && !empty($variationId) && isset($percentVal) && !empty($percentVal)) {

            $productVariation = ProductVariation::findOne(['id' => $variationId]);
            if (!empty($productVariation)) {
                $stockValue = number_format((float) $productVariation->inventory_value * $percentVal / 100, 0, '.', '');
                $productVariation->allocate_inventory = $stockValue;
                $productVariation->allocate_percent = $percentVal;
                $productVariation->save(false);
                $p_variant_id = $productVariation->id;
                $this->productUpdate($productId, $p_variant_id);
            }
        }
        $response_ajax['success'] = true;

        return json_encode($response_ajax);
    }

    public function actionStockQtyUpdate() {
        $post = Yii::$app->request->post();

        $stockValue = isset($post['stockVal']) ? $post['stockVal'] : null;
        $productId = isset($post['productId']) ? $post['productId'] : null;

        $product_model = $this->findModel($productId);
        $product_model->stock_quantity = $stockValue;

        $response_ajax['success'] = false;
        if ($product_model->save(false)) {
            $response_ajax['success'] = true;
        }

        return json_encode($response_ajax);
    }

    public function actionVariationUpdate() {
        $post = Yii::$app->request->post();

        $keyId = $post['itemKey'];
        $keyValue = $post['itemVal'];
        $keyNameField = $post['itemField'] . "_key";
        $keyValueField = $post['itemField'] . "_value";

        $response_ajax['success'] = false;
        $productVariation = ProductVariation::findOne(['id' => $keyId]);
        if (!empty($productVariation)) {

            $productVariation->$keyValueField = $keyValue;
            $productVariation->save(false);
            $this->productUpdate($productVariation->product_id, $productVariation->id);
            $response_ajax['success'] = true;
        }

        return json_encode($response_ajax);
    }

    public function actionPriceUpdate() {
        $post = Yii::$app->request->post();

        $pId = $post['pId'];
        $price = preg_replace('/[^a-zA-Z0-9_.]/', '', $post['pPrice']);

        // Price Conversion as Per the Currency Selected( NOT IN CASE OFUSD) By User Starts
        $user = Yii::$app->user->identity;
        $conversion_rate = 1;
        $price = $price * $conversion_rate;
        $price = number_format((float) $price, 2, '.', '');

        $product_model = $this->findModel($pId);
        $product_model->price = $price;
        $product_model->save(false);
        $product_variations = ProductVariation::find()->where(['product_id' => $pId])->all();
        if (!empty($product_variations)) {
            foreach ($product_variations as $product_variation) {
                $product_variation->price_value = $price;
                $product_variation->save(false);
            }
        }

        $this->productUpdate($pId);
        $response_ajax['success'] = true;
        return json_encode($response_ajax);
    }

    /* Temporary Saving or Creating the Single Product * */

    public function actionCreateProduct() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $product_model = new Product();
        //General Tab
        $product_model->user_id = $user_id;
        $product_model->name = $_POST['pName'];
        $product_model->sku = $_POST['pSKU'];
        $product_model->hts = $_POST['pHTS'];
        $product_model->upc = $_POST['pUPC'];
        $product_model->ean = $_POST['pEAN'];
        $product_model->jan = $_POST['pJAN'];
        $product_model->isbn = $_POST['pISBN'];
        $product_model->mpn = $_POST['pMPN'];
        $product_model->description = $_POST['pDes'];
        if ($_POST['pAdult'] == 'Please Select') :
            $_POST['pAdult'] = 'no';
        endif;
        $product_model->adult = $_POST['pAdult'];
        if ($_POST['pAgeGroup'] == 'Please Select') :
            $_POST['pAgeGroup'] = NULL;
        endif;
        $product_model->age_group = $_POST['pAgeGroup'];

        $product_model->brand = $_POST['pBrand'];
        if ($_POST['pCond'] == 'Please Select') :
            $_POST['pCond'] = 'New';
        endif;
        $product_model->condition = $_POST['pCond'];
        if ($_POST['pGender'] == 'Please Select') :
            $_POST['pGender'] = 'Unisex';
        endif;
        $product_model->gender = $_POST['pGender'];
        $product_model->weight = $_POST['pWeight'];
        if ($_POST['pAvail'] == 'Please Select') :
            $_POST['pAvail'] = 'Out of Stock';
        endif;
        $product_model->stock_level = $_POST['pAvail'];
        $product_model->status = Product::STATUS_ACTIVE;
        $product_model->permanent_hidden = Product::STATUS_NO;
        $product_model->stock_manage = Product::STOCK_MANAGE_YES;
        $product_model->translate_status = Product::STATUS_NO;
        $created_date = date('Y-m-d h:i:s', time());
        $product_model->created_at = $created_date;

        $product_model->save(false);

        return $product_model->id;
    }

    /* Upload Product Images - Dropzone Form Action */

    public function actionProductImageUpload() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $baseDomain = Yii::$app->params['globalDomain'];
        $userDomain = Yii::$app->user->identity->domain;
        $baseURL = CustomFunction::getBaseUrl(Yii::$app->request, $userDomain, $baseDomain);
        $basedir = Yii::getAlias('@base');
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = $basedir . '/frontend/web/img/product_images';
        $error_code = $_FILES['file']['error'];
        $product_id = $_POST['pid'];
        if (!empty($_FILES) && $error_code == 0) {
            $tempFile = $_FILES['file']['tmp_name'];
            $imageName = $_FILES['file']['name'];
            $imageNameArray = explode('.', $imageName);
            $imageLabel = $imageNameArray[0];
            $RandomImageId = uniqid();
            //Generate Unique Name for each Image Uploaded
            $new_imageName = $RandomImageId . '_' . $imageName;
            //Path Setting
            $targetPath = $storeFolder . $ds;
            $targetFile = $targetPath . $new_imageName;
            //Save the Uploaded File
            move_uploaded_file($tempFile, $targetFile);
            //Image Thumbnail
//            Image::thumbnail(Yii::getAlias('@product_images/' . $new_imageName), 71, 71)
//                ->save(Yii::getAlias('@product_images/thumbnails/thumb_' . $new_imageName), ['quality' => 100]);
            //Temp entry into Product_images Table
            $pImageModel = new ProductImage;
            $pImageModel->product_id = $product_id;
            $pImageModel->user_id = $user_id;
            $product_image_name = $new_imageName;
            $product_image_link = $baseURL . '/img/product_images/' . $product_image_name;
            $pImageModel->link = $product_image_link;
            $pImageModel->label = $imageLabel;
            $pImageModel->status = 0;
            $created = date('Y-m-d H:i:s', time());
            $pImageModel->created_at = $created;
            if ($pImageModel->save(false)) {
                $imgId = $pImageModel->id;
                $imgLabel = $pImageModel->label;
                $response = ['status' => 'success', 'imgId' => $imgId, 'imgLabel' => $imgLabel, 'data' => 'Product image has been uploaded successfully'];
            } else {
                $response = ['status' => 'error', 'data' => 'Failed to upload Product Image'];
            }
            echo json_encode($response);
            exit;
        }
    }

    public function actionUploadProductImageVideo360() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $baseDomain = Yii::$app->params['globalDomain'];
        $userDomain = Yii::$app->user->identity->domain;
        $baseURL = CustomFunction::getBaseUrl(Yii::$app->request, $userDomain, $baseDomain);
        $basedir = Yii::getAlias('@base');
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = $basedir . '/frontend/web/img/product_videos';
        $error_code = $_FILES['file']['error'];
        $image_id = $_GET['imgId'];
        if (!empty($_FILES) && $error_code == 0) {
            $tempFile = $_FILES['file']['tmp_name'];
            $videoName = $_FILES['file']['name'];
            $videoNameArray = explode('.', $videoName);
            $videoLabel = $videoNameArray[0];
            $RandomVideoId = uniqid();
            //Generate Unique Name for each Image Uploaded
            $new_videoName = $RandomVideoId . '_' . $videoName;
            //Path Setting
            $targetPath = $storeFolder . $ds;
            $targetFile = $targetPath . $new_videoName;
            //Save the Uploaded File
            move_uploaded_file($tempFile, $targetFile);
            //Temp entry into Product_images Table for 360 video link
            $pImageModel = ProductImage::find()->where(['id' => $image_id])->one();
            $product_image_video_name = $new_videoName;
            $product_image_video_link = $baseURL . '/img/product_videos/' . $product_image_video_name;
            $pImageModel->degree_360_video_link = $product_image_video_link;
            if ($pImageModel->save(false)) {
                $imgId = $pImageModel->id;
                $imgLabel = $pImageModel->label;
                $response = $product_image_video_link;
            } else {
                $response = '';
            }
            return $response;
        }
    }

    /* Save Product Images - Media Step (Product Create) */

    public function actionSaveProductImage() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $img_id = isset($_POST['img_Id']) ? $_POST['img_Id'] : '';
        $img_default = isset($_POST['img_default']) ? $_POST['img_default'] : '';
        $img_label = isset($_POST['img_label']) ? $_POST['img_label'] : '';
        $img_alt = isset($_POST['img_alt']) ? $_POST['img_alt'] : '';
        $img_html_video = isset($_POST['img_html_video']) ? $_POST['img_html_video'] : '';
        //    $img_360_video = isset($_POST['img_360_video']) ? $_POST['img_360_video'] : '';
        if ($img_label == 'Empty'):
            $img_label = NULL;
        endif;
        if ($img_alt == 'Empty'):
            $img_alt = NULL;
        endif;
        if ($img_html_video == 'Empty'):
            $img_html_video = NULL;
        endif;
        //    if ($img_360_video == 'Empty'):
        //      $img_360_video = NULL;
        //    endif;
        //Update the Image Fields in Product_images Table
        $pImageModel = ProductImage::find()->where(['id' => $img_id])->one();
        $pImageModel->label = $img_label;
        $pImageModel->tag = $img_alt;
        $pImageModel->user_id = $user_id;
        $pImageModel->html_video_link = $img_html_video;
        //    $pImageModel->_360_degree_video_link = $img_360_video;
        $pImageModel->save(false);
        return 'success';
    }

    /* Update Product Image Order */

    public function actionUpdateProductImageOrder() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $img_id = isset($_POST['ImgId']) ? $_POST['ImgId'] : '';
        $img_order = isset($_POST['ImgOrder']) ? $_POST['ImgOrder'] : '';
        $img_default = isset($_POST['ImgDefault']) ? $_POST['ImgDefault'] : '';
        if ($img_default == 1):
            $img_default = 'Yes';
        else:
            $img_default = 'No';
        endif;
        //Update the Image Fields in Product_images Table
        $pImageModel = ProductImage::find()->where(['id' => $img_id])->one();
        $pImageModel->priority = $img_order;
        $pImageModel->default_image = $img_default;
        $pImageModel->save(false);
        return 'success';
    }

    /* Get Variation Items */

    public function actionGetVariantItems() {

        $var_Id = Yii::$app->request->post('pVar_Id');
        $current_user_id = Yii::$app->request->post('current_user_id');
        $selectedVariationItem = Variation::findOne([
            'id' => $var_Id,
            'user_id' => $current_user_id
        ]);

        $variationItemIds = $selectedVariationItem->getVariationItemList();
        $var_items = VariationValue::find()->where(['in', 'id', $variationItemIds])->all();
        $items_array = array();
        foreach ($var_items as $var_item):
            $var_item_id = $var_item->id;
            $var_item_name = $var_item->value;
            $items_array[$var_item_id] = $var_item_name;
        endforeach;
        return json_encode($items_array);
    }

    /* Get List of Categories-Elliot */

    public function actionGetCats() {
        $users_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $users_id = Yii::$app->user->identity->parent_id;
        $cats = Category::find()->where(['user_id' => $users_id])->all();
        foreach ($cats as $cat) {
            $arr[] = array("value" => $cat->id, "text" => $cat->name);
        }
        echo json_encode($arr);
        die;
    }

    /* Create the Single Product */

    public function actionGetVaritems($var_name, $user_id) {

        $var_name = strtoupper($var_name);
        $varItemObjects = VariationItem::findAll([
            'name' => $var_name,
            'user_id' => $user_id
        ]);

        $items_array = array();
        foreach ($varItemObjects as $varItemObject){

            $varItem_Id = $varItemObject->id;
            $varItem_values = VariationValue::findAll([
                'variation_item_id' => $varItem_Id,
                'user_id' => $user_id
            ]);


            if ( !empty($varItem_values) ){
                foreach ($varItem_values as $var_item){
                    $var_item_id = $var_item->id;
                    $var_item_name = $var_item->value;
                    $items_array[$var_item_id] = $var_item_name;
                }
            }

        }

        return json_encode($items_array);
    }

    public function actionGetVariationItemName() {

        $varItem_Value_Id = Yii::$app->request->post('pItemValueId');
        $current_user_id = Yii::$app->request->post('current_user_id');

        $varItemValue = VariationValue::findOne([
            'id' => $varItem_Value_Id,
            'user_id' => $current_user_id
        ]);

        $response = array(
            'item_name' => ''
        );
        if ( !empty($varItemValue) ){
            $varItemId = $varItemValue->variation_item_id;

            $variantItem = VariationItem::findOne([
                'id' => $varItemId
            ]);

            $response['item_name'] = $variantItem->name;

        }

        return json_encode($response);


    }

    /* Save Product Variations */

    public function actionSaveProductVariations() {

        $user_id = Yii::$app->request->post('current_user_id');

        $currentUser = User::findOne(['id' => $user_id]);
        $user_connection_id = User::getDefaultConnection($user_id);
        if ($currentUser->level == User::USER_LEVEL_MERCHANT_USER){
            $user_id = $currentUser->parent_id;
            $user_connection_id = User::getDefaultConnection($user_id);
        }

        $requestData = Yii::$app->request->post();
        $pid = $requestData['pID'];

        ProductVariation::deleteAll([
            'product_id' => $pid,
            'user_id' => $user_id,
            'user_connection_id' => $user_connection_id
        ]);


        $variation_key = isset($requestData['varHeaderArr'])?$requestData['varHeaderArr']:null;
        $variation_data = isset($requestData['varRowArr'])?$requestData['varRowArr']:null;
        $variation_key_data = [];

        if ( empty($variation_data) ){
            return 'success';
        }

        foreach ($variation_data as $data){

            $preChkVariationData = [];

            $keyCount = 0;
            $variants_options = [];
            foreach ($variation_key as $datakey) {

                if ( $datakey !== "SKU" && $datakey !== "Inventory" && $datakey !== "Price" && $datakey !== "Weight" ){

                    $value_data = [
                        'label' => '',
                        'value' => $data[$keyCount]
                    ];
                    $oneVariantOption = [
                        'name' => $datakey,
                        'value' => $value_data
                    ];
                    $variants_options[] = $oneVariantOption;

                }
                $preChkVariationData[$datakey] = $data[$keyCount];
                $keyCount ++;
            }



            $oneVariantData = [
                'sku_key' => 'sku',
                'sku_value' => $preChkVariationData['SKU'],
                'inventory_key' => 'inventory_quantity',
                'inventory_value' => $preChkVariationData['Inventory'],
                'price_key' => 'price',
                'price_value' => $preChkVariationData['Price'],
                'weight_key' => 'weight',
                'weight_value' => $preChkVariationData['Weight'],
                'options' => $variants_options,
            ];

            $variation_key_data[] = $oneVariantData;
        }

        if ( !empty($variation_key_data) ){

            foreach ( $variation_key_data as $variant_p_data ) {

                $productVariationName = "";
                $productVariationValues = "";
                $productVariationDesc = "";
                $product_v_SetNames = [];
                $product_v_SetValues = [];

                $optionsCount= 0;
                $variationOptions = $variant_p_data['options'];

                if ( !empty($variationOptions) ) {
                    foreach ($variationOptions as $each_v_option) {

                        $optionName = strtoupper($each_v_option['name']);
                        $optionValue = $each_v_option['value'];

                        $optionDescription = $optionName;

                        $productVariationOptionItem = VariationItem::findOne([
                            'name' => $optionName,
                            'description' => $optionDescription
                        ]);

                        if (empty($productVariationOptionItem)) {

                            $productVariationOptionItem = new VariationItem();

                            $productVariationOptionItem->name = $optionName;
                            $productVariationOptionItem->description = $optionDescription;
                            $productVariationOptionItem->user_id = $user_id;

                            $productVariationOptionItem->save(false);

                        }

                        $productVariationOptionValue = VariationValue::findOne([
                            'user_id' => $user_id,
                            'label' => isset($optionValue['label'])?$optionValue['label']:'',
                            'value' => $optionValue['value']
                        ]);

                        if ( empty($productVariationOptionValue) ){
                            $productVariationOptionValue = new VariationValue();

                            $productVariationOptionValue->variation_item_id = $productVariationOptionItem->id;
                            $productVariationOptionValue->label = $optionValue['label'];
                            $productVariationOptionValue->value = $optionValue['value'];
                            $productVariationOptionValue->user_id = $user_id;

                            $productVariationOptionValue->save(false);

                        }

                        if ($productVariationName == "") {
                            $productVariationName = $optionName . ' | ' . $optionValue['value'];
                            $productVariationDesc = $optionName . ' | ' . isset($optionValue['label'])?$optionValue['label']:$optionValue['value'];
                        } else {
                            $productVariationName .= ' ' . $optionName . ' | ' . $optionValue['value'];
                            $productVariationDesc .= '<br>' . $optionName . ' | ' . isset($optionValue['label'])?$optionValue['label']:$optionValue['value'];
                        }

                        if ($productVariationValues == "") {
                            $productVariationValues = $productVariationOptionValue->id;
                        } else {
                            $productVariationValues .= "-" . $productVariationOptionValue->id;
                        }

                        if (!in_array($productVariationOptionItem->name, $product_v_SetNames)) {
                            array_push($product_v_SetNames, $productVariationOptionItem->name);
                        }
                        if (!in_array($productVariationOptionValue->id, $product_v_SetValues)) {
                            array_push($product_v_SetValues, $productVariationOptionValue->id);
                        }

                        $optionsCount ++;
                    }
                }

                $variationModel = Variation::findOne(['items' => $productVariationValues]);
                if (empty($variationModel)) {

                    $variationModel = new Variation();
                    $variationModel->name = $productVariationName;
                    $variationModel->items = $productVariationValues;
                    $variationModel->description = $productVariationDesc;
                    $variationModel->user_id = $user_id;
                    $variationModel->save(false);
                }

                $productVariation = new ProductVariation();

                $productVariation->user_id = $user_id;
                $productVariation->variation_id = $variationModel->id;
                $productVariation->product_id = $pid;
                $productVariation->sku_key = $variant_p_data['sku_key'];
                $productVariation->sku_value = $variant_p_data['sku_value'];
                $productVariation->inventory_key = $variant_p_data['inventory_key'];
                //$productVariation->inventory_value = $variant_p_data['inventory_value'];
                $productVariation->price_key = $variant_p_data['price_key'];
                $productVariation->price_value = $variant_p_data['price_value'];
                $productVariation->weight_key = $variant_p_data['weight_key'];
                $productVariation->weight_value = $variant_p_data['weight_value'];
                $productVariation->variation_set_id = 0;
                $productVariation->connection_variation_id = '-1';
                $productVariation->user_connection_id = $user_connection_id;

                $productVariation->save(false);
            }


            $productVarSetName = implode(' / ', $product_v_SetNames);
            $productVarSetValueStr = implode('-', $product_v_SetValues);

            $p_VariationSet = VariationSet::findOne([
                'name' => $productVarSetName,
                'items' => $productVarSetValueStr
            ]);
            if (empty($p_VariationSet)) {
                $p_VariationSet = new VariationSet();
                $p_VariationSet->name = $productVarSetName;
                $p_VariationSet->items = $productVarSetValueStr;
                $p_VariationSet->description = $productVarSetName;
                $p_VariationSet->user_id = $user_id;
                $p_VariationSet->item_count = count($product_v_SetNames);
                $p_VariationSet->save(false);
            }

            ProductVariation::updateAll(
                [
                    'variation_set_id' => $p_VariationSet->id
                ],
                [
                    'variation_set_id' => 0,
                    'product_id' => $pid,
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                ]
            );
            return 'success';
        }
        return 'failed';
    }

    public function actionCategoryAdd()
    {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $requestData = Yii::$app->request->post();
        $pid = $requestData['pID'];
        $category_id = $requestData['category_id'];
        $category_model = new ProductCategory();
        $category_model->product_id = $pid;
        $category_model->user_id = $user_id;
        $category_model->category_id = $category_id;
        $created_date = date('Y-m-d h:i:s', time());
        $category_model->created_at = $created_date;
        $category_model->save(false);
        return 'success';
    }

    public function actionChannelAdd()
    {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $requestData = Yii::$app->request->post();
        $pid = $requestData['pID'];
        $channel_id = $requestData['channel_id'];
        $userConnection = UserConnection::find()->where(['user_id' => $user_id, 'connection_id' => $channel_id])->one();
        if (!empty($userConnection)) {
            $product_connection = new ProductConnection();
            $product_connection->user_id = $user_id;
            $product_connection->user_connection_id = $userConnection->id;
            $product_connection->product_id = $pid;
            $product_connection->save(false);
        }
        return 'success';
    }

    public function actionInventoryAdd()
    {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $requestData = Yii::$app->request->post();
        $pid = $requestData['pID'];
        $stock_level = $requestData['stock_level'];
        $stock_status = $requestData['stock_status'];
        $low_stock_notification = $requestData['low_stock_notification'];
        $product = Product::find()->where(['id' => $pid])->one();
        if (!empty($product)) {
            $product->stock_level = $stock_level;
            $product->stock_status = $stock_status;
            $product->low_stock_notification = $low_stock_notification;
            $product->save(false);
        }
        return 'success';
    }

    public function actionSaleAdd()
    {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $requestData = Yii::$app->request->post();
        $pId = $requestData['pID'];
        $price = $requestData['price'];
        $sale_price = $requestData['sale_price'];
        $schedule_date = $requestData['schedule_date'];
        $product = Product::find()->where(['id' => $pId])->one();

        $return_result['success'] = true;
        $return_result['message'] = "";

        if (!empty($product)) {
            $product->price = $price;
            $product->sales_price = $sale_price;
            $product->save(false);

            $response = $this->productUpdate($pId, 0, false);
            $json_response = json_decode($response, true);
            if(isset($json_response['success']) && $json_response['success']) {
                return json_encode($return_result, JSON_UNESCAPED_UNICODE);
            }
            else {
                //Yii::$app->session->setFlash('danger', 'Error! Product has not been updated.');
                $json_msg = $json_response['msg'];
                $fail_message = '';
                foreach ($json_msg as $msg) {
                    $user_connection_id = $msg['user_connection_id'];
                    $userConnection = UserConnection::find()->where(['id' => $user_connection_id])->one();
                    if (!empty($userConnection)) {
                        $fail_message = $fail_message.$userConnection->getPublicName().'<br />'.$msg['message'].'<br />';
                    }
                }
                $return_result['success'] = false;
                $return_result['message'] = $fail_message;
                return json_encode($return_result, JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode($return_result, JSON_UNESCAPED_UNICODE);
    }

    /* Remove Product Variations */

    public function actionRemoveProductVariations() {
        $pid = $_POST['pID'];
        $rowid = $_POST['tr_id'];
        if (!empty($rowid)):
            $var_Model = ProductVariation::find()->where(['store_variation_id' => $rowid, 'product_id' => $pid])->all();
            if (!empty($var_Model)):
                foreach ($var_Model as $var):
                    $var->delete();
                endforeach;
            endif;
        endif;
        return 'success';
    }

    /* Get Variations Set */

    public function actionGetVarSet() {
        $var_sets = VariationSet::find()->where(['create_user' => Yii::$app->user->identity->id])->all();
        $sets_array = array();
        foreach ($var_sets as $var_set):
            $var_set_id = $var_set->id;
            $var_set_name = $var_set->variations_set_name;
            $sets_array[$var_set_id] = $var_set_name;
        endforeach;
        return json_encode($sets_array);
    }

    //Product Variants Combination 
    public function actionAddProductVariationsRow() {
        $var_set = $_POST['var_set'];
        $op_set_object = VariationSet::find()->where(['create_user' => Yii::$app->user->identity->id, 'variations_set_name' => $var_set])->one();
        $op_ids_arr = array();
        if (!empty($op_set_object)):
            $op_ids_arr = explode("-", $op_set_object->variations_items);
        endif;
        $var_name_arr = array();
        foreach ($op_ids_arr as $key => $val):
            $var_obj = VariationSet::find()->where(['id' => $val])->one();
            array_push($var_name_arr, $var_obj->variation_name);
        endforeach;
        return json_encode($var_name_arr);
    }

    //Get option Set Variations
    public function actionGetOpsetVariations() {
        $var_set_id = $_POST['opset_id'];
        $op_set_object = VariationSet::find()->where(['id' => $var_set_id])->one();
        $op_set = '';
        $op_ids_arr = array();
        if (!empty($op_set_object)):
            $op_set = $op_set_object->variations_set_name;
            $op_ids_arr = explode("-", $op_set_object->variations_items);
        endif;
        $var_name_arr = array();
        foreach ($op_ids_arr as $key => $val):
            $var_obj = Variation::find()->where(['id' => $val])->one();
            array_push($var_name_arr, $var_obj->variation_name);
        endforeach;
        return json_encode($var_name_arr);
    }

    //Get Attributes related to Attribute Type
    public function actionGetAttributes() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $attr_type_id = $_GET['attr_type'];
        $attrs = Attribution::find()->where(['attribution_type' => $attr_type_id, 'user_id' => $user_id])->all();
        $attr_arr = array();
        foreach ($attrs as $key => $attr_data):
            $attr_name = $attr_data->name;
            $attr_arr[$key][$attr_data->id] = $attr_name;
        endforeach;
        echo json_encode($attr_arr);
    }

    /*
     * showing graph 
     * ajax hit from custom_graph.js
     */

    public function actionDonutchartonproduct() {
        $requestData = Yii::$app->request->post();
        if ( isset($requestData['data']) ) {
            $post = $requestData['data'];
        } else {
            $post = "";
        }

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }

        $data_id = '';
        /*	 * ***************************FOR TODAY******************************************** */
        if ($post == 'donutcharttoday' || $post == 'donutcharttodaymob') {
            $currentdate = date('Y-m-d');
            $connection = \Yii::$app->db;
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id Where order_product.user_id ="' . $user_id . '" AND date(order_product.created_at)= "' . $currentdate . '"');
            $models = $orders_data->queryAll();
            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }
        /*	 * ***************************FOR WEEK******************************************** */
        if ($post == 'donutchartweek' || $post == 'donutchartweekmob') {
            $Week_previous_date = date('Y-m-d', strtotime('-7  days'));
            $currentdate = date('Y-m-d');
            $connection = \Yii::$app->db;
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id Where order_product.user_id ="' . $user_id . '" AND date(order_product.created_at) BETWEEN "' . $Week_previous_date . '" AND "' . $currentdate . '"');
            $models = $orders_data->queryAll();
            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }
        /*	 * ***************************FOR MONTH******************************************** */
        if ($post == 'donutchartmonth' || $post == 'donutchartmonthmob') {
            $currentmonth = date('m');
            $currentyear = date('Y');
            $connection = \Yii::$app->db;
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id Where order_product.user_id ="' . $user_id . '" AND month(order_product.created_at)="' . $currentmonth . '" AND year(order_product.created_at)="' . $currentyear . '"');
            $models = $orders_data->queryAll();
            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }
        /*	 * ***************************FOR QUARTER******************************************** */
        if ($post == 'donutchartQuarter' || $post == 'donutchartQuartermob' || $data_id == 'donutchartQuarter') {

            $currentmonth_pre = date('Y-m-d', strtotime('-3 months'));
            $currentdate = date('Y-m-d');
            $connection = \Yii::$app->db;
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id  Where order_product.user_id ="' . $user_id . '" AND date(order_product.created_at) BETWEEN "' . $currentmonth_pre . '" AND "' . $currentdate . '"');
            $models = $orders_data->queryAll();
            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }
        /*	 * ***************************FOR YEAR******************************************** */
        if ($post == 'donutchartyear' || $post == 'donutchartyearmob') {
            $currentyear = date('Y');
            $lasttenyear = date('Y', strtotime('-1 years'));
            $connection = \Yii::$app->db;
            //$orders_data = $connection->createCommand('SELECT * from order_product Where year(created_at) BETWEEN "' . $lasttenyear . '" AND "' . $currentyear . '"');
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id Where order_product.user_id ="' . $user_id . '" AND year(order_product.created_at)="' . $currentyear . '"');
            $models = $orders_data->queryAll();

            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }
        /*	 * ***************************FOR ANNUAL******************************************** */
        if ($post == 'donutchartannual' || $post == 'donutchartannualmob') {
            $currentyear = date('Y');
            $lasttenyear = date('Y', strtotime('-3 years'));
            $connection = \Yii::$app->db;
            //$orders_data = $connection->createCommand('SELECT * from order_product Where year(created_at) BETWEEN "' . $lasttenyear . '" AND "' . $currentyear . '"');
            $orders_data = $connection->createCommand('SELECT product_category.product_id as product_id, product_category.category_id as category_id from order_product join product_category ON order_product.product_id = product_category.product_id Where order_product.user_id ="' . $user_id . '" AND year(order_product.created_at)="' . $currentyear . '"');
            $models = $orders_data->queryAll();

            $categoryname = Category::find()->where(['user_id' => $user_id])->all();
        }

        $count = 0;
        $customarr = array();
        if (!empty($models)) {
            foreach ($models as $model) {
                $count += 1;
                $catID = $model['category_id'];
                if (array_key_exists($catID, $customarr)) {
                    $customarr[$catID][] = array(
                        'productID' => $model['product_id'],
                        'categoryID' => $model['category_id'],
                    );
                } else {
                    $customarr[$catID][] = array(
                        'productID' => $model['product_id'],
                        'categoryID' => $model['category_id'],
                    );
                }
            }
        } else {
            $data = 'invalid';
            return \yii\helpers\Json::encode($data);
        }
        if ($count == 0) {
            $data = 'invalid';
            return \yii\helpers\Json::encode($data);
        }

        $arr3 = array();
        foreach ($customarr as $key => $val) {
            $arr3[$key] = count($val);
        }

        $array5 = array();
        foreach ($categoryname as $catename) {
            $array4 = array();
            if (array_key_exists($catename->id, $arr3)) {
                $array4['label'] = $catename->name;
                $array4['value'] = $arr3[$catename->id];
            } else {
                $array4['label'] = $catename->name;
                $array4['value'] = 0;
            }
            $array5[] = $array4;
        }
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
            16 => '#e3f2fd',
            17 => '#bbdefb',
            18 => '#304ffe',
            19 => '#3d5afe',
            20 => '#536dfe',
            21 => '#8c9eff',
            22 => '#1a237e',
            23 => '#283593',
            24 => '#303f9f',
            25 => '#3949ab',
            26 => '#3f51b5',
            27 => '#5c6bc0',
            28 => '#7986cb',
            29 => '#9fa8da',
            30 => '#c5cae9',
            31 => '#e8eaf6',
            32 => '#e8eaf6',
            33 => '#0000ff',
            34 => '#00bfff',
            35 => '#0000e5',
            36 => '#19c5ff',
            37 => '#0000cc',
            38 => '#32cbff',
            39 => '#0000b2',
            40 => '#4cd2ff',
            41 => '#0086b3',
            42 => '#0099cc',
            43 => '#00ace6',
            44 => '#00bfff',
            45 => '#4dd2ff',
            46 => '#33ccff',
            47 => '#1ac5ff',
            48 => '#bc8f8f',
            49 => '#cd5c5c',
            50 => '#8b4513',
            51 => '#a0522d',
            52 => '#cd853f',
            53 => '#deb887',
            54 => '#f5f5dc',
            55 => '#d2b48c',
            56 => '#e9967a',
            57 => '#fa8072',
            58 => '#ffa07a',
            59 => '#0099cc',
            60 => '#00ace6',
            61 => '#00bfff',
            62 => '#4dd2ff',
            63 => '#33ccff',
            64 => '#1ac5ff',
            65 => '#bc8f8f',
            66 => '#cd5c5c',
            67 => '#8b4513',
            67 => '#a0522d',
            68 => '#cd853f',
            69 => '#0091ea',
            70 => '#00b0ff',
            71 => '#40c4ff',
            72 => '#80d8ff',
            73 => '#01579b',
            74 => '#0277bd',
            75 => '#039be5',
            76 => '#03a9f4',
            77 => '#b3e5fc',
            78 => '#81d4fa',
            79 => '#29b6f6',
            80 => '#e1f5fe',
            81 => '#b5af79',
            82 => '#914f84',
            83 => '#ce6d87',
            84 => '#e04a72',
            85 => '#e3f2fd'
        );
        $randcolor = array();
        $randcolor_2 = array();
        $c = 0;

        foreach ($array5 as $val) {
            $randcolor[] = $colorarr[$c++];
            if ($c == sizeof($colorarr))
                $c = 0;
        }

        $randcolor_2 = $randcolor;
        if ($count > 0) {
            $data = array('data' => json_encode($array5), 'colors' => json_encode($randcolor_2), 'id' => json_encode($post));
            return \yii\helpers\Json::encode($data);
        } else {
            $data = 'invalid';
            return \yii\helpers\Json::encode($data);
        }
    }

    public function actionAjaxProductPublish() {

        if (!isset($_POST['productIds']) || count($_POST['productIds']) == 0) {
            return FALSE;
        }
        if (!isset($_POST['channelIds']) || count($_POST['channelIds']) == 0) {
            return FALSE;
        }
        $product_ids = $_POST['productIds'];
        $channel_ids = $_POST['channelIds'];
        foreach ($product_ids as $_product_id) {
            //$delete_product = Product::updateAll(array('permanent_hidden' => Product::PRODUCT_PERMANENT_YES), 'id = ' . $_product_id);
        }
        echo 'success';
    }

    public function actionAjaxProductDelete() {

        if (!isset($_POST['productIds']) || count($_POST['productIds']) == 0) {
            return FALSE;
        }
        $product_ids = $_POST['productIds'];
        foreach ($product_ids as $_product_id) {
            $delete_product = Product::updateAll(array('permanent_hidden' => Product::PRODUCT_PERMANENT_YES), 'id = ' . $_product_id);
        }
    }

    public function actionAjaxInactiveProductDelete() {

        //$post = Yii::$app->request->get();
        if (!isset($_POST['checkedProductID']) || count($_POST['checkedProductID']) == 0) {
            return false;
        }
        $product_ids = $_POST['checkedProductID'];

        foreach ($product_ids as $_product_id) {
            $undo_delete_product = Product::updateAll(array('product_status' => 'active'), 'id = ' . $_product_id);
        }
    }

    public function actionPiechartonproduct() {
        $post = $_POST['data'];
        $connected_users = UserConnection::find()->where(['user_id' => Yii::$app->user->identity->id])->available()->all();
        $arr = array();
        if (!empty($connected_users)) {

            $namearr = array();

            foreach ($connected_users as $connected_user) {
                $name = $connected_user->connection->name;
                $store_channel_id = $connected_user->id;

                /* for today */
                if (!empty($store_channel_id) && !empty($name) && $post == 'piecharttoday' || $post == 'piecharttodaymob') {
                    $currentdate = date('Y-m-d');
                    $connection = \Yii::$app->db;
                    $orders_data = $connection->createCommand('SELECT * from product Where user_connection_id="' . $store_channel_id . '"AND date(created_at) = "' . $currentdate . '"');
                    $model = count($orders_data->queryAll());
                    $arr[] = array($model);
                    $namearr[] = $name;
                }
                /* for week */
                if (!empty($store_channel_id) && !empty($name) && $post == 'piechartweek' || $post == 'piechartweekmob') {
                    $currentmonth = date('m');
                    $date_check = date('m');
                    for ($i = 1; $i <= 7; $i++) {
                        if ($date_check == $currentmonth) {
                            $previous_day = date('Y-m-d', strtotime('-' . $i . 'days'));
                            $date_check = date('m', strtotime($previous_day));
                        }
                    }

                    $week_previous_day = date($previous_day, strtotime('+1  days'));
                    $Week_previous_date = date('Y-m-d', strtotime('-7  days'));
                    $currentdate = date('Y-m-d');
                    // $date_check = date('Y-m-d', strtotime($previous_hour));
                    $connection = \Yii::$app->db;
                    $orders_data = $connection->createCommand('SELECT * from product Where user_connection_id="' . $store_channel_id . '"AND date(created_at) BETWEEN "' . $Week_previous_date . '" AND "' . $currentdate . '"');
                    $model = count($orders_data->queryAll());
                    $arr[] = array($model);
                    $namearr[] = $name;
                }

                /* for month */
                if (!empty($store_channel_id) && !empty($name) && $post == 'piechartmonth' || $post == 'piechartmonthmob') {
                    //$Month_previous_date = date('Y-m-d', strtotime('-30  days'));
                    $currentdate = date('Y-m-d');
                    $currentyear = date('Y');
                    $currentmonth = date('m');
                    $connection = \Yii::$app->db;
                    $orders_data = $connection->createCommand('SELECT * from product Where user_connection_id="' . $store_channel_id . '"AND month(created_at)= "' . $currentmonth . '" AND year(created_at)="' . $currentyear . '"');
                    $model = count($orders_data->queryAll());
                    //$model = count(ProductChannel::find()->where([$fieldname => $store_channel_id])->all());
                    $arr[] = array($model);
                    $namearr[] = $name;
                }
                /* for quarter */
                if (!empty($store_channel_id) && !empty($name) && $post == 'piechartQuarter' || $post == 'piechartQuartermob') {
                    $currentmonth = date('m');
                    $currentyear = date('Y');
                    $val = $currentmonth - 3;
                    $currentmonth_pre = date("m", mktime(0, 0, 0, $val, 10));
                    $Month_previous_date = date('Y-m-d', strtotime('-30  days'));
                    $currentdate = date('Y-m-d');
                    $connection = \Yii::$app->db;
                    $orders_data = $connection->createCommand('SELECT * from product Where user_connection_id="' . $store_channel_id . '"AND month(created_at) BETWEEN "' . $currentmonth_pre . '" AND "' . $currentmonth . '" AND year(created_at)="' . $currentyear . '"');
                    $model = count($orders_data->queryAll());
                    //$model = count(ProductChannel::find()->where([$fieldname => $store_channel_id])->all());
                    $arr[] = array($model);
                    $namearr[] = $name;
                }
                /* for year */
                if (!empty($store_channel_id) && !empty($name) && $post == 'piechartyear' || $post == 'piechartyearmob') {
                    $currentyear = date('Y');
                    $connection = \Yii::$app->db;
                    $orders_data = $connection->createCommand('SELECT * from product Where user_connection_id="' . $store_channel_id . '"AND year(created_at)= "' . $currentyear . '"');
                    $model = count($orders_data->queryAll());
                    // $model = count(ProductChannel::find()->where([$fieldname => $store_channel_id])->all());
                    $arr[] = array($model);
                    $namearr[] = $name;
                }
            }
        }

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
            16 => '#0000ff',
            17 => '#00bfff',
            18 => '#0000e5',
            19 => '#19c5ff',
            20 => '#0000cc',
            21 => '#32cbff',
            22 => '#0000b2',
            23 => '#4cd2ff',
            24 => '#0086b3',
            25 => '#0099cc',
            26 => '#00ace6',
            27 => '#00bfff',
            28 => '#4dd2ff',
            29 => '#33ccff',
            30 => '#1ac5ff',
            31 => '#bc8f8f',
            32 => '#cd5c5c',
            33 => '#8b4513',
            34 => '#a0522d',
            35 => '#cd853f',
            36 => '#deb887',
            37 => '#f5f5dc',
            38 => '#d2b48c',
            39 => '#e9967a',
            40 => '#fa8072',
            41 => '#ffa07a',
        );
        $randcolor = array();
        $c = 0;
        foreach ($arr as $array1) {
            $randcolor[] = $colorarr[$c++];
        }
        $randcolor_2 = $randcolor;
        $data = array('label' => json_encode($namearr), 'color' => json_encode($randcolor_2), 'data' => json_encode($arr));
        return \yii\helpers\Json::encode($data);
    }

    public function actionInactiveProducts() {
        return $this->render('inactiveproducts');
    }

    public function actionSmartingTranslationStatusEnable() {
        $post_data = Yii::$app->request->post();
        $data = '';
        if (!empty($post_data)) {
            $channel_acc = $post_data['channel_acc'];
            $product_id = $post_data['product_id'];
            $connection_id = $post_data['connection_id'];

            $store_check = Stores::find()->Where(['store_name' => $channel_acc])->one();
            if (!empty($store_check)) {

                $product_abbrivation_data = ProductAbbrivation::find()->Where(['product_id' => $product_id, 'mul_store_id' => $connection_id, 'channel_accquired' => $channel_acc])->one();
                $product_abbrivation_data->translation_status = 'yes';
                if ($product_abbrivation_data->save(false)) {
                    $data = "success";
                } else {
                    $data = "error";
                }
            } else {
                $product_abbrivation_data = ProductAbbrivation::find()->Where(['channel_accquired' => $channel_acc])->one();
                $product_abbrivation_data->translation_status = 'yes';
                if ($product_abbrivation_data->save(false)) {
                    $data = "success";
                } else {
                    $data = "error";
                }
            }
        }
        return $data;
    }

    public function actionSmartingTranslationStatusDisable() {
        $post_data = Yii::$app->request->post();
        $data = '';
        if (!empty($post_data)) {
            $channel_acc = $post_data['channel_acc'];
            $product_id = $post_data['product_id'];
            $connection_id = $post_data['connection_id'];

            $store_check = Stores::find()->Where(['store_name' => $channel_acc])->one();
            if (!empty($store_check)) {

                $product_abbrivation_data = ProductAbbrivation::find()->Where(['product_id' => $product_id, 'mul_store_id' => $connection_id, 'channel_accquired' => $channel_acc])->one();
                $product_abbrivation_data->translation_status = 'no';
                if ($product_abbrivation_data->save(false)) {
                    $data = "success";
                } else {
                    $data = "error";
                }
            } else {
                $product_abbrivation_data = ProductAbbrivation::find()->Where(['channel_accquired' => $channel_acc])->one();
                $product_abbrivation_data->translation_status = 'no';
                if ($product_abbrivation_data->save(false)) {
                    $data = "success";
                } else {
                    $data = "error";
                }
            }
        }
        return $data;
    }

    public function getAllStoreName() {
        $stores_names = Stores::find()->select('store_name')->asArray()->all();
        $store_array = array();
        foreach ($stores_names as $_store) {
            $store_array[] = $_store['store_name'];
        }
        return $store_array;
    }

    public function rainbow($start, $end, $steps) {
        $s = $this->str_to_rgb($start);
        $e = $this->str_to_rgb($end);
        $out = array();
        $r = (integer) ($e['r'] - $s['r']) / $steps;
        $g = (integer) ($e['g'] - $s['g']) / $steps;
        $b = (integer) ($e['b'] - $s['b']) / $steps;
        for ($x = 0; $x < $steps; $x++) {
            $out[] = '#' . rgb_to_str(
                    $s['r'] + (integer) ($r * $x), $s['g'] + (integer) ($g * $x), $s['b'] + (integer) ($b * $x));
        }
        //            echo'<pre>';
        //            print_r($out);
        return $out;
    }

    public function rgb_to_str($r, $g, $b) {
        return str_pad($r, 2, '0', STR_PAD_LEFT)
            . str_pad($g, 2, '0', STR_PAD_LEFT)
            . str_pad($b, 2, '0', STR_PAD_LEFT);
    }

    public function str_to_rgb($str) {
        return array(
            'r' => hexdec(substr($str, 0, 2)),
            'g' => hexdec(substr($str, 3, 2)),
            'b' => hexdec(substr($str, 5, 2))
        );
    }


    public function actionProductsAjax() {

        $post = Yii::$app->request->get();
        $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $sortbyvalue = true;
        $orderby_str = 'name';
        if ($orderby == 1) {
            $orderby_str = 'name';
            $sortbyvalue = false;
        } elseif ($orderby == 2) {
            $orderby_str = 'sku';
            $sortbyvalue = false;
        } elseif ($orderby == 4) {
            $orderby_str = 'price';
            $sortbyvalue = false;
        } elseif ($orderby == 5) {
            $sortbyvalue = true;
        } else {
            $orderby_str = 'name';
            $sortbyvalue = true;
        }

        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }

        $root = true;
        $user_connection_id = [];
        if (isset($post['user_connection_id']) && !empty($post['user_connection_id'])) {
            $user_connection_id[] = $post['user_connection_id'];
            $root = false;
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $user_connection_id[] = $userConnection->id;
                    }
                }
            }
        }

        if (isset($user_connection_id) && !empty($user_connection_id)) {
            $products = Product::find()
                ->limit($length)->offset($start)
                ->joinWith(['productConnections'])
                ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'product_connection.status' => ProductConnection::STATUS_YES])
                ->andWhere(['and',
                    ['in', 'product_connection.user_connection_id', $user_connection_id]])
                ->orderBy($orderby_str . " " . $asc)
                ->groupBy('product_connection.product_id')
                ->all();
            $count = Product::find()
                ->joinWith(['productConnections'])
                ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'product_connection.status' => ProductConnection::STATUS_YES])
                ->andWhere(['and',
                    ['in', 'product_connection.user_connection_id', $user_connection_id]])
                ->orderBy($orderby_str . " " . $asc)
                ->groupBy('product_connection.product_id')
                ->count();

        }
        else {
            $products = Product::find()
                ->limit($length)->offset($start)
                ->Where(['user_id' => $user_id, 'permanent_hidden' => Product::PRODUCT_PERMANENT_NO])
                ->orderBy($orderby_str . " " . $asc)
                ->all();
            $count = Product::find()
                ->Where(['user_id' => $user_id, 'permanent_hidden' => Product::PRODUCT_PERMANENT_NO])
                ->orderBy($orderby_str . " " . $asc)
                ->count();
        }

        if($search != ''){
            if (isset($user_connection_id) && !empty($user_connection_id)) {
                $products = Product::find()
                    ->limit($length)->offset($start)
                    ->joinWith(['productConnections'])
                    ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'product_connection.status' => ProductConnection::STATUS_YES])
                    ->andWhere(['and',
                        ['in', 'product_connection.user_connection_id', $user_connection_id]])
                    ->andFilterWhere([
                        'or',
                        ['like', 'product.name', $search],
                        ['like', 'product.SKU', $search],
                        ['like', 'product.price', $search],
                    ])
                    ->orderBy($orderby_str . " " . $asc)
                    ->groupBy('product_connection.product_id')
                    ->all();
                $count = Product::find()
                    ->joinWith(['productConnections'])
                    ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'product_connection.status' => ProductConnection::STATUS_YES])
                    ->andWhere(['and',
                        ['in', 'product_connection.user_connection_id', $user_connection_id]])
                    ->andFilterWhere([
                        'or',
                        ['like', 'product.name', $search],
                        ['like', 'product.SKU', $search],
                        ['like', 'product.price', $search],
                    ])
                    ->orderBy($orderby_str . " " . $asc)
                    ->groupBy('product_connection.product_id')
                    ->count();
            }
            else {
                $products = Product::find()
                    ->limit($length)->offset($start)
                    ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO])
                    ->andFilterWhere([
                        'or',
                        ['like', 'name', $search],
                        ['like', 'SKU', $search],
                        ['like', 'price', $search],
                    ])
                    ->orderBy($orderby_str . " " . $asc)
                    ->all();
                $count = Product::find()
                    ->Where(['product.user_id' => $user_id, 'product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO])
                    ->andFilterWhere([
                        'or',
                        ['like', 'name', $search],
                        ['like', 'SKU', $search],
                        ['like', 'price', $search],
                    ])
                    ->orderBy($orderby_str . " " . $asc)
                    ->count();
            }
        }

        $user = Yii::$app->user->identity;
        $currency_symbol = '$';
        $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
        if (isset($selected_currency) and !empty($selected_currency)) {
            $currency_symbol = $selected_currency['symbol'];
        }

        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count);
        $arr = array();
        $data_arr = array();
        if (!empty($products)) {

            $i = 1;
            foreach ($products as $product) :
                //$imageUrl = $product->userConnection->connection->getConnectionImage();
                $channelImages = '';
                $product_connections = ProductConnection::find()->where(['user_id' => $user_id, 'product_id' => $product->id, 'status' => ProductConnection::STATUS_YES])->all();

                foreach ($product_connections as $product_connection) {
                    $imageUrl = $product_connection->userConnection->connection->getConnectionImage();
                    $channelImages = $channelImages . Html::img($imageUrl, ['alt' => 'Channel Image', 'width' => '50', 'height' => '50', 'class' => 'ch_img', 'data-toggle' => "tooltip", 'data-placement' => "right", 'title' => '', 'data-original-title' => '', 'test_sttr' => '']);
                }

                $countOrders = OrderProduct::find()->where(['product_id' => $product->id])->count();
                $price1 = isset($product->price) ? $product->price : 0;
                $price = @$price1; //number_format($price1, 2);

                $total_refunds = 0;
                $refundedOrders = OrderProduct::find()->where(['product_id' => $product->id])->with(['order' => function($query) {
                    $query->andWhere(['status' => ['Refunded', 'Cancel']]);
                }])->all();

                if (isset($refundedOrders) and ! empty($refundedOrders)) {

                    foreach ($refundedOrders as $single_order) {
                        if (isset($single_order->order) and ! empty($single_order->order)) {
                            $total_refunds += 1;
                        }
                    }
                }
                $name_pro = $product->name;

                $arr[0] = '<div class="be-checkbox"><input name="product_check" id="ck' . $i . '" value="' . $product->id . '" name="ck1" type="checkbox" data-parsley-multiple="groups" value="bar" data-parsley-mincheck="2" data-parsley-errors-container="#error-container1" class="product_row_check"><label class="getId" for="ck' . $i . '"></label></div>';
                if (!$root)
                    $arr[1] = '<div class="product_listing_title"><a href="view?id=' . $product->id . '">' . $name_pro . '</a></div>';
                else
                    $arr[1] = '<div class="product_listing_title"><a href="product/view?id=' . $product->id . '">' . $name_pro . '</a></div>';
                $arr[2] = '<div class="channel_scrolling">' . $channelImages . '</div>';
                $arr[3] = $product->sku;
                $arr[4] = $currency_symbol . number_format((float)$price, 2, '.', ',');
                $arr[5] = $countOrders;
                $arr[6] = $total_refunds;
                $data_arr[] = $arr;
                $i++;
            endforeach;
            if ($sortbyvalue):
                if ($asc == 'asc'):
                    usort($data_arr, function ($a, $b) {
                        return $a[5] - $b[5];
                    });
                else:
                    usort($data_arr, function ($a, $b) {
                        return $b[5] - $a[5];
                    });
                endif;
                $data_arr1 = array_values($data_arr);
            else:
                $data_arr1 = array_values($data_arr);
            endif;

            $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $data_arr1);
            echo json_encode($response_arr);
        }
        else {
            $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => array());
            echo json_encode($response_arr);
        }
    }

    public function productUpdate($product_id, $p_variant_id = 0, $is_background = true) {

        $importCr = new ConsoleRunner(['file' => '@console/yii']);

        $deleteCmd = 'product-export/delete ' . $product_id;
        $res = $importCr->run($deleteCmd);

        if ($is_background) {
            $importCmd = 'product-export/export ' . $product_id;
            $importCmd .= ' ';
            $importCmd .= ' ' . $p_variant_id;
            $res = $importCr->run($importCmd);
        }
        else {
            return ProductExportController::actionExport($product_id, $p_variant_id);
        }

        $return_response = [
            'success' => true,
            'product_id' => $product_id
        ];
        return json_encode($return_response, JSON_UNESCAPED_UNICODE);
    }
}
