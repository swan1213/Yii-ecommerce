<?php

namespace frontend\controllers;

use Yii;
use common\models\Attribution;
use frontend\models\search\AttributesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\AttributionType;

/**
 * AttributesController implements the CRUD actions for Attributes model.
 */
class AttributesController extends Controller {

  /**
   * @inheritdoc
   */
  public function behaviors() {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'only' => ['index', 'view', 'create', 'update', 'delete', 'get-attr_type','shopify'],
        'rules' => [
        [
            'actions' => ['shopify'],
            'allow' => true,
            'roles' => ['?'],
        ],
          [
            'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-attr_type','shopify'],
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
   * Lists all Attributes models.
   * @return mixed
   */
  public function actionIndex() {

        $currentUserId = Yii::$app->user->identity->id;
        $searchModel = new AttributesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUserId);

        return $this->render('index', [
              'searchModel' => $searchModel,
              'dataProvider' => $dataProvider,
        ]);
  }

  /**
   * Displays a single Attributes model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id) {
    return $this->render('view', [
          'model' => $this->findModel($id),
    ]);
  }

  /**
   * Creates a new Attributes model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate() {
      $model = new Attribution();
      if (isset($_POST['_csrf'])) {
          $model->user_id = Yii::$app->user->identity->id;
          $model->name = $_POST['attr_name'];
          $model->label = $_POST['attr_label'];
          $model->description = $_POST['attr_desc'];
          $model->attribution_type = $_POST['attr_type'];
          //$model->attribution_type = $_POST['attr_type'];
          $model->save(false);
          Yii::$app->session->setFlash('success', 'Success! Attribute has been created.');
          return $this->redirect(['/attributes']);
      }
      else {
          return $this->render('create', [
                'model' => $model,
          ]);
      }
  }

  /**
   * Updates an existing Attributes model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id) {
      $model = $this->findModel($id);

      if ($model->load(Yii::$app->request->post())) {
          if ($model->save(false)) {
              Yii::$app->session->setFlash('success', 'Success! Attribute has been updated.');
              $currentUserId = Yii::$app->user->identity->id;
              $searchModel = new AttributesSearch();
              $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUserId);

              return $this->render('index', [
                  'searchModel' => $searchModel,
                  'dataProvider' => $dataProvider,
              ]);
          }
          else {
              Yii::$app->session->setFlash('danger', 'Error! Attribute are not Updated Successfully');
              return $this->render('create', [
                  'model' => $model,
              ]);
          }
      }
      else {
        return $this->render('create', [
            'model' => $model,
        ]);
      }
  }

  /**
   * Deletes an existing Attributes model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id) {
    $this->findModel($id)->delete();
    return $this->redirect(['index']);
  }

  /**
   * Finds the Attributes model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Attributes the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = Attribution::findOne($id)) !== null) {
      return $model;
    }
    else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

  /**
   * Fetch All Attribute Types
   */
  public function actionGetAttr_type() {
    $attr_types = AttributionType::find()->where(['user_id' => Yii::$app->user->identity->id])->all();
    foreach ($attr_types as $key => $attr_type_data) {
      $attr_type_name = $attr_type_data->name;
      $attr_type_array[$key][$attr_type_data->id] = $attr_type_name;
    }
    echo json_encode($attr_type_array);
  }

  /**
   * Update the Attributes 
   */
  public function actionUpdateAttr() {
    $attr_id = $_POST['id'];
    $attr_name = $_POST['attr_name'];
    $attr_label = $_POST['attr_label'];
    $attr_type = $_POST['attr_type'];
    $attr_desc = $_POST['attr_desc'];
    $attr_Model = Attribution::find()->where(['user_id' => Yii::$app->user->identity->id, 'id' => $attr_id])->one();
    $attr_type_obj = AttributionType::find()->where(['user_id' => Yii::$app->user->identity->id, 'attribute_type_name' => $attr_type])->one();
    if (!empty($attr_Model)):
      $attr_Model->name = $attr_name;
      $attr_Model->label = $attr_label;
      $attr_Model->description = $attr_desc;
      $attr_Model->attribute_type = $attr_type_obj->attribute_type_id;
      $attr_Model->save(false);
    endif;
    Yii::$app->session->setFlash('success', 'Success! Attribute has been updated.');
    return $this->redirect(['/attributes']);
  }

}
