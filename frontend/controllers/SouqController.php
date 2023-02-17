<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

use common\models\Category;
use common\models\Country;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductImage;
use common\models\User;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\SouqConnectionForm;
use frontend\components\ConsoleRunner;

/**
 * SouqController implements for Channels model.
 */
class SouqController extends Controller {
	/**
     * action filter
     */
	public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'get-accesstoken'],
                'rules' => [
                    [
                        'actions' => ['index', 'get-accesstoken'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($id) {
    	$error_msg = '';
        $error_status = false;
        $is_connect_channel = false;
        $connect_status = UserConnection::IMPORT_STATUS_FAIL;
        $user_id = Yii::$app->user->identity->id;

        // check if this connection is still existed in Connection table
        $connection_row = \common\models\Connection::find()->where(['id' => $id])->one();

        // connection is not exsited
        if(empty($connection_row)) {
            return $this->redirect(['/channels']);
        }

        $connection_name = $connection_row->name;

        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where(['connection_id' => $id, 'user_id' => $user_id])->available()->one();

        // the connection is already existed
        if(!empty($user_connection_row)) {
            if($user_connection_row->connected == UserConnection::CONNECTED_YES) {
                $is_connect_channel = true; 
            } else {
                $connect_status = $user_connection_row->import_status;
            }
        }

        $model = new SouqConnectionForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
            } catch(\Exception $e) {
                $error_msg = $e->getMessage();
                $error_status = true;
            }
        }

        // either the page is initially displayed or there is some validation error
        return $this->render('index', [
            'model' => $model,
            'error_status' => $error_status,
            'error_msg' => $error_msg,
            'is_connect_channel' => $is_connect_channel,
            'connect_status' => $connect_status,
            'connection_name' => $connection_name
        ]);
    }

}