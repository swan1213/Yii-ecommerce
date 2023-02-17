<?php

namespace frontend\controllers;

use Yii;
use common\models\AttributionType;
use frontend\models\search\AttributeTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AttributeTypeController implements the CRUD actions for AttributeType model.
 */
class AttributeTypeController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'update', 'delete', 'update-attr_type'],
                'rules' => [
                        [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-attr_type'],
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
     * Lists all AttributeType models.
     * @return mixed
     */
    public function actionIndex() {
        $currentUserId = Yii::$app->user->identity->id;
        $searchModel = new AttributeTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUserId);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AttributeType model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new AttributeType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new AttributionType();
        if (isset($_POST['_csrf'])) {
            $model->user_id = Yii::$app->user->identity->id;
            $model->name = $_POST['attr_type_name'];
            $model->label = $_POST['attr_type_label'];
            $model->description = $_POST['attr_type_desc'];
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Success! Attribute Type has been created.');
            return $this->redirect(['/attribute-type']);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AttributeType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AttributeType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AttributeType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AttributeType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = AttributionType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Update the Attribute Type 
     */
    public function actionUpdateAttr_type() {
        $attr_type_id = $_POST['id'];
        $attr_typeModel = AttributionType::find()
            ->where(['id' => Yii::$app->user->identity->id, 'id' => $attr_type_id])->one();
        if (!empty($attr_typeModel)):
            $attr_typeModel->name = $_POST['attr_type_name'];
            $attr_typeModel->label = $_POST['attr_type_label'];
            $attr_typeModel->description = $_POST['attr_type_desc'];
            $attr_typeModel->save(false);
        endif;
        Yii::$app->session->setFlash('success', 'Success! Attribute Type has been updated.');
        return $this->redirect(['/attribute-type']);
    }

}
