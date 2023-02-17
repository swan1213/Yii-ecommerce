<?php

namespace frontend\controllers;

use Yii;
use common\models\Attributes;
use frontend\models\search\ProductTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\ProductType;

/**
 * AttributesController implements the CRUD actions for Attributes model.
 */
class ProductsTypeController extends Controller {

  /**
   * @inheritdoc
   */
  public function behaviors() {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'only' => ['index', 'view', 'create', 'update'],
        'rules' => [
          [
            'actions' => ['index', 'view', 'create', 'update'],
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
      $searchModel = new ProductTypeSearch();

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
    $model = new ProductType();

    if ( $model->load(Yii::$app->request->post()) && $model->save() ) {
        Yii::$app->session->setFlash('success', 'Success! A Product Type has been created.');
        return $this->redirect(['/products-type']);

    } else {
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

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->session->setFlash('success', 'Success! A Product Type has been updated.');

        return $this->redirect(['index', 'id' => $id]);
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
    if (($model = ProductType::findOne($id)) !== null) {
      return $model;
    }
    else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
