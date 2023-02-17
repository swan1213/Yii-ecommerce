<?php

namespace frontend\controllers;

use common\models\UserPermission;
use common\models\UserProfile;
use Yii;
use common\models\User;
use common\commands\SendEmailCommand;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class SubUserController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create','update','savesubprofile'],
                'rules' => [
                        [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['logout', 'index', 'view', 'create','update','savesubprofile'],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {

        $model = new User();
        $userProfile = new UserProfile();
        $users_Id = Yii::$app->user->identity->id;

        if ($model->load(Yii::$app->request->post()) && $userProfile->load(Yii::$app->request->post())) {

            $email = $model->email;
            $company_name = Yii::$app->user->identity->company;
            $domain_name = Yii::$app->user->identity->domain;
            $model->setPassword('123456');
            $model->auth_key = $model->getAuthKey();
            //$model->password_hash = $passHash;
            $model->parent_id = $users_Id;
            $model->domain = Yii::$app->user->identity->domain;
            $model->company = Yii::$app->user->identity->company;
            $model->level = User::USER_LEVEL_MERCHANT_USER;

            if (empty($model->permission_id)) {
                return $this->render('create', [
                    'model' => $model,
                    'userProfile' => $userProfile,
                ]);
            }

            if ($model->save(false)) {
                $userProfile->locale = "en-US";
                if ($userProfile->save(false)) {

                }
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => 'Welcome to Elliot - You have been invited to join '.$company_name.'’s Elliot Environment',
                    'view' => '@common/mail/template',
                    'from' => ['mail@helloiamelliot.com' => 'Elliot'],
                    'to' => $email,
                    'params' => [
                        'title' => 'Welcome to Elliot - You have been invited to join '.$company_name.'’s Elliot Environment',
                        'content' => 'Your username is: '.$email.' <br> Your temporary password is: 123456 <br> Please login at '.Yii::$app->params['globalDomain'],
                        'server' => env('SEVER_URL')
                    ]
                ]));
                Yii::$app->session->setFlash('success', 'Success! User Created Successfully');

                $auth = Yii::$app->authManager;
                $auth->assign($auth->getRole(User::ROLE_USER), $model->id);

                $model = new User();
                $userProfile = new UserProfile();
                return $this->render('create', [
                    'model' => $model,
                    'userProfile' => $userProfile,
                ]);
            } else {
                Yii::$app->session->setFlash('danger', 'Error! User are not Created Successfully');
                return $this->render('create', [
                    'model' => $model,
                    'userProfile' => $userProfile,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'userProfile' => $userProfile,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {

        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }

        $model = $this->findModel($id);
        $user_profile = UserProfile::find()->where(['user_id' => $id])->one();

        if ($model->load(Yii::$app->request->post()) && $model->save() && $user_profile->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'user_profile' => $user_profile,
            ]);
        }
    }

    public function actionSavesubprofile() {

        $request = Yii::$app->request;
        $user_id = Yii::$app->request->post('sub_user_id');
        $user_data = User::find()->Where(['id' => $user_id])->one();
        $user_profile = UserProfile::find()->where([['id' => $user_id]])->one();

        if (Yii::$app->request->post()) {
            $user_profile->first_name = Yii::$app->request->post('sub_firstname');
            $user_profile->last_name = Yii::$app->request->post('sub_lastname');
            $user_data->email = Yii::$app->request->post('sub_email');
            $sub_role = Yii::$app->request->post('sub_role');
            $user_data = UserPermission::find()->Where(['title' => $sub_role])->one();
            if ($user_data != null) {
                $user_data->permission_id = $user_data->id;
            }
        }
        if ($user_data->save(false)) {
            $user_profile->save(false);
            $response = ['status' => 'success', 'data' => 'profile info saved'];
            Yii::$app->session->setFlash('success', 'Success! Profile info has been updated.');
            return $this->redirect(['view', 'id' => $user_id]);
        } else {
            $response = ['status' => 'error', 'data' => 'profile info not saved'];
            Yii::$app->session->setFlash('danger', 'Error! Profile info has not been updated.');
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id, $email) {
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
