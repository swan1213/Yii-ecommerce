<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use common\models\Category;
use frontend\models\search\CategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Bigcommerce\Api\Client as Bigcommerce;
use frontend\components\BaseController;
/**
 * CategoriesController implements the CRUD actions for Categories model.
 */
class CategoriesController extends BaseController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'index', 'update'],
                'rules' => [
                        [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['logout', 'index', 'view', 'create', 'index', 'update'],
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

    /**
     * Lists all Categories models.
     * @return mixed
     */
    public function actionIndex() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $categories = CategorySearch::find()->Where(['user_id' => $user_id, 'parent_id' => 0])->orderBy(['name' => SORT_ASC])->all();
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $user_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
        ]);
    }

    /**
     * Displays a single Categories model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdateNestedCat() {

        if (Yii::$app->request->post()) :

            $data = Yii::$app->request->post('data');
            $cat_arr = json_decode($data);
//            echo'<pre>';
//            print_r($cat_arr);
//            echo "bhjfjkdhjgfjsdf";

            $varfun = $this->displayArrayRecursively($cat_arr);

        endif;
    }

    public static function displayArrayRecursively($cat_arr, $indent = '') {
        if ($cat_arr) {
            $array = array();
            $arr[] = '';

            foreach ($cat_arr as $value) {

                if (array_key_exists("children", $value)) {

                    $count_child = count($value->children);

                    for ($i = 0; $i <= $count_child; $i++) {

                        $data = $value->children[$i]->id;
                        $id = $value->id;
                        $array[$value->id] = $data;
                        $arr = array("parent" => $value->id, "child" => $data);

                        $count_child--;

                        self::displayArrayRecursively($value->children, $indent . '--' . $value->id);


                        $db_cat_data = Categories::find()->Where(['category_ID' => $data])->one();
                        $db_cat_data->parent_category_ID = $id;
                        if ($db_cat_data->save(false)) :

                        endif;
                    }
                } else {
                    $id = $value->id;
                    $db_cat_data = Categories::find()->Where(['category_ID' => $id])->one();
                    $db_cat_data->parent_category_ID = 0;
                    if ($db_cat_data->save(false)) :

                    endif;
                }
            }
        }
    }

    /**
     * Creates a new Categories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {


        //Only For Bigcommerce
        $model = new Category();
        $request = Yii::$app->request;
        if ($request->post()):
            $cat_name = $request->post('add_cat_name');
            $parent_name = $request->post('add_parent_cat_name');

            if ($parent_name == 'Please Select') :
                $parent_id = 0;
            else :
                $categories_data = Category::find()->Where(['name' => $parent_name])->one();
                $parent_id = !empty($categories_data)?$categories_data->id:0;
            endif;
            
            $model->name = $cat_name;
            $model->parent_id = $parent_id;
            $model->user_id = Yii::$app->user->identity->id;
            
            if ($model->save()) {
                //$create_cat_stores_channels = Stores::create_category($cat_name, $parent_name);

                //$group_id = $create_cat_stores_channels->group_id;
                //$abb_id = $create_cat_stores_channels->abb_id;

                $update_categories_data = Category::find()->Where(['id' => $model->id])->one();
                //$update_categories_data->channel_abb_id=$abb_id;
                //$update_categories_data->store_category_groupid=$group_id;
                $update_categories_data->save(false);
                
                Yii::$app->session->setFlash('success', 'Success! Category has been Created Successfully.');
                return $this->redirect('/categories');
            } else {
                Yii::$app->session->setFlash('danger', 'Error! Category has Not been Created Successfully.');
                return $this->render('create', [
                            'model' => $model,
                ]);
            }

        else :
            return $this->render('create', [
                        'model' => $model,
            ]);
        endif;
    }

    /**
     * Creates a new Categories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionGetParentcat() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $parent_cat = Category::find()->where(['user_id' => $user_id])->all();
        foreach ($parent_cat as $key => $parent_cat_data) {
            $parent_name = $parent_cat_data->name;
            $parent_cat_array[$key][$parent_cat_data->id] = $parent_name;
        }
        //$a="{'M': 'male', 'F': 'female'}";
        echo json_encode($parent_cat_array);
    }

    /**
     * Updates an existing Categories model.
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
     * Updates cat an existing Categories model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
     public function actionUpdatecat() {

         $user_id = Yii::$app->user->identity->id;
         if (Yii::$app->request->post()) {
             $id = Yii::$app->request->post('id');
             $cat_name = Yii::$app->request->post('update_cat_name');
             $parent_name = Yii::$app->request->post('update_parent_cat_name');
             if ($parent_name == 'Please Select') :
                 $parent_id = 0;
                 $parent_channel_abb_id = 0;
             else :
                 $categories_data = Category::find()->Where(['name' => $parent_name, 'user_id' => $user_id])->one();
                 $parent_id = isset($categories_data->id) ? $categories_data->id : 0;
             endif;

             $model = Category::find()->Where(['id' => $id])->one();

             $model->name = $cat_name;
             $model->parent_id = $parent_id;
            
             if ($model->save(false)) {
                 Yii::$app->session->setFlash('success', 'Success! Categories has been updated.');
                 return $this->redirect('/categories');
             }
             else {
                 Yii::$app->session->setFlash('danger', 'Error! Category has Not been Updated Successfully.');
                 return $this->redirect(['update', 'id' => $model->id]);
             }
         }
     }

    /**
     * Deletes an existing Categories model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Categories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Categories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionEdit($id) {
        $categories = Category::find()->where(['id' => $id])->one();
        $parent_cat = Category::find()->where(['id' => $categories->parent_id])->one();
        return $this->render('edit', [
                    'categories' => $categories->name,
                    'parent_cat' => $parent_cat->name,
        ]);
        //return $this->render('wechat');
    }

}
 