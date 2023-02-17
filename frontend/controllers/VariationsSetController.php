<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use common\models\VariationSet;
use frontend\models\search\VariationSetSearch;
use frontend\components\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * VariationsSetController implements the CRUD actions for VariationsSet model.
 */
class VariationsSetController extends BaseController {

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
                      'actions' => ['signup'],
                      'allow' => true,
                      'roles' => ['?'],
                  ],
                  [
                      'actions' => ['logout', 'index', 'view', 'create','update'],
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
   * Lists all VariationsSet models.
   * @return mixed
   */
  public function actionIndex() {

      $user_id = Yii::$app->user->identity->id;
      if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
          $user_id = Yii::$app->user->identity->parent_id;

      $variationSets = VariationSet::find()->where(['user_id' => $user_id])->all();
      return $this->render('index', [
          'variationSets' => $variationSets,
          'userId' => $user_id
      ]);
  }

  /**
   * Displays a single VariationsSet model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id) {
    return $this->render('view', [
          'model' => $this->findModel($id),
    ]);
  }

  /**
   * Creates a new VariationsSet model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate() {
    $model = new VariationsSet();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }
    else {
      return $this->render('create', [
            'model' => $model,
      ]);
    }
  }

  /**
   * Updates an existing VariationsSet model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id) {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }
    else {
      return $this->render('update', [
            'model' => $model,
      ]);
    }
  }

  /**
   * Deletes an existing VariationsSet model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id) {
    $this->findModel($id)->delete();

    return $this->redirect(['index']);
  }

  /**
   * Finds the VariationsSet model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return VariationsSet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = VariationsSet::findOne($id)) !== null) {
      return $model;
    }
    else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
