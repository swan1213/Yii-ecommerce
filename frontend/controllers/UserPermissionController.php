<?php
namespace frontend\controllers;

use common\models\Connection;
use common\models\ConnectionParent;
use common\models\UserConnection;
use Yii;
use common\models\UserPermission;
use common\models\User;
use common\models\PermissionMenu;
use common\models\PermissionSubmenu;
use common\models\PermissionOther;
use frontend\components\CustomFunction;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class UserPermissionController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index', 'view', 'create'],
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

        $permission_channel = Connection::find()->all();
        $permission_data = UserPermission::find()->where(['user_id' => Yii::$app->user->identity->id])->all();
        $role_data = array();
        $i = 0;
        foreach ($permission_data as $permission) {

            $role_data[$i]['id'] = $permission['id'];
            $role_data[$i]['title'] = $permission['title'];
            $role_data[$i]['menu_permission'] = CustomFunction::getPermissionMenuLabel($permission['menu_permission']);
            $role_data[$i]['channel_permission'] = CustomFunction::getPermissionChannelLabel($permission['channel_permission']);
            $i ++;
        }
        return $this->render('index', ['role_data' => $role_data]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {

        if (Yii::$app->user->identity->role == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }

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

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }
        $role_menu_permission = array();
        $permission_menus = PermissionMenu::find()->all();
        $permission_submenus = PermissionSubmenu::find()->all();
        foreach ($permission_menus as $permission_menu) {
            $children = array();
            foreach ($permission_submenus as $permission_submenu) {
                if ($permission_menu["id"] == $permission_submenu["parent_id"]) {
                    $children[$permission_submenu["id"]] = $permission_submenu["name"];
                }
            }
            $role_menu_permission[$permission_menu["name"]] = $children;
        }

        $user_connection_ids = array();
        $user_connections = UserConnection::find()->where(['user_id' => $user_id])->all();
        foreach ($user_connections as $user_connection) {
            $user_connection_ids[] = $user_connection->connection_id;
        }
        $db_conn = \Yii::$app->db;
        $role_channel_permission = array();
        $channel_parents = ConnectionParent::find()->all();
        $channels = Connection::find()
            ->andWhere(['and', ['in', 'id', $user_connection_ids]])
            ->all();

        $store_channels = Connection::find()
            ->andWhere(['type_id' => 1])
            ->andWhere(['and', ['in', 'id', $user_connection_ids]])
            ->all();
        foreach ($store_channels as $store_channel) {
            $role_channel_permission[$store_channel->id] = $store_channel->getConnectionName();
        }

        foreach ($channel_parents as $channel_parent) {
            $channel_group_query = $db_conn->createCommand('SELECT COUNT(parent_id), id, parent_id, name FROM connection WHERE parent_id = '.$channel_parent["id"].' GROUP BY parent_id');
            $channels_group = $channel_group_query->queryAll();
            if ($channels_group[0]["COUNT(parent_id)"] == 1) {
                foreach ($channels as $channel_item) {
                    if ($channel_item["parent_id"] == $channel_parent["id"]) {
                        $role_channel_permission[$channel_item["id"]] = $channel_item["name"];
                        break;
                    }
                }
            }
            else {
                $children = array();
                foreach ($channels as $channel_item) {
                    if ($channel_item["parent_id"] == $channel_parent["id"]) {
                        $children[$channel_item["id"]] = $channel_item["name"];
                    }
                }
                $role_channel_permission[$channel_parent["name"]] = $children;
            }
        }

        $role_other_permission = array();
        $permission_others = PermissionOther::find()->all();
        foreach ($permission_others as $permission_other) {
            $role_other_permission[$permission_other["name"]] = $permission_other['label'];
        }

        $model = new UserPermission();

        if ($model->load(Yii::$app->request->post())) {
            $roleArray = Yii::$app->request->post();

            $model->user_id = $user_id;
            $role = $roleArray["UserPermission"]["title"];
            $roledata = UserPermission::find()->Where(['title' => $role, 'user_id' => $user_id])->one();
            if (!empty($roleArray["role_menu_select"])) {
                $role_menu_select = $roleArray["role_menu_select"];
                $menu_permission = "";
                foreach ($role_menu_select as $key => $value) {
                    $menu_permission = $menu_permission . $value . ", ";
                }
                if (strlen($menu_permission) > 0) $menu_permission = substr($menu_permission, 0, strlen($menu_permission) - 2);
                $model->menu_permission = $menu_permission;
            }
            if (!empty($roleArray["role_channel_select"])) {
                $role_channel_select = $roleArray["role_channel_select"];
                $channel_permission = "";
                foreach ($role_channel_select as $key => $value) {
                    $channel_permission = $channel_permission . $value . ", ";
                }
                if (strlen($channel_permission) > 0) $channel_permission = substr($channel_permission, 0, strlen($channel_permission) - 2);
                $model->channel_permission = $channel_permission;
            }
            if (!empty($roleArray["role_other_select"])) {
                $role_other_select = $roleArray["role_other_select"];
                $other_permission = "";
                foreach ($role_other_select as $key => $value) {
                    $other_permission = $other_permission . $value . ", ";
                }
                if (strlen($other_permission) > 0) $other_permission = substr($other_permission, 0, strlen($other_permission) - 2);
                $model->other_permission = $other_permission;
            }
            if ($roledata != null) {
                Yii::$app->session->setFlash('exist', 'Error! UserRole Exist');
                $model = new UserPermission();
                return $this->render('create', [
                    'model' => $model,
                    'role_menu_permission' => $role_menu_permission,
                    'role_channel_permission' => $role_channel_permission,
                    'role_other_permission' => $role_other_permission,
                ]);
            }
            else if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Success! UserRole Created Successfully');
                $model = new UserPermission();
                return $this->render('create', [
                    'model' => $model,
                    'role_menu_permission' => $role_menu_permission,
                    'role_channel_permission' => $role_channel_permission,
                    'role_other_permission' => $role_other_permission,
                ]);
            } else {
                Yii::$app->session->setFlash('danger', 'Error! UserRole are not Created Successfully');
                $model = new UserPermission();
                return $this->render('create', [
                    'model' => $model,
                    'role_menu_permission' => $role_menu_permission,
                    'role_channel_permission' => $role_channel_permission,
                    'role_other_permission' => $role_other_permission,
                ]);
            }

        } else {
            return $this->render('create', [
                'model' => $model,
                'role_menu_permission' => $role_menu_permission,
                'role_channel_permission' => $role_channel_permission,
                'role_other_permission' => $role_other_permission,
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

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }
        $role_menu_permission = array();
        $permission_menus = PermissionMenu::find()->all();
        $permission_submenus = PermissionSubmenu::find()->all();
        foreach ($permission_menus as $permission_menu) {
            $children = array();
            foreach ($permission_submenus as $permission_submenu) {
                if ($permission_menu["id"] == $permission_submenu["parent_id"]) {
                    $children[$permission_submenu["id"]] = $permission_submenu["name"];
                }
            }
            $role_menu_permission[$permission_menu["name"]] = $children;
        }

        $user_connection_ids = array();
        $user_connections = UserConnection::find()->where(['user_id' => $user_id])->all();
        foreach ($user_connections as $user_connection) {
            $user_connection_ids[] = $user_connection->connection_id;
        }

        $db_conn = \Yii::$app->db;
        $role_channel_permission = array();
        $channel_parents = ConnectionParent::find()->all();
        $channels = Connection::find()
            ->andWhere(['and', ['in', 'id', $user_connection_ids]])
            ->all();

        $store_channels = Connection::find()
            ->andWhere(['type_id' => 1])
            ->andWhere(['and', ['in', 'id', $user_connection_ids]])
            ->all();
        foreach ($store_channels as $store_channel) {
            $role_channel_permission[$store_channel->id] = $store_channel->getConnectionName();
        }
        
        foreach ($channel_parents as $channel_parent) {
            $channel_group_query = $db_conn->createCommand('SELECT COUNT(parent_id), id, parent_id, name FROM connection WHERE parent_id = '.$channel_parent["id"].' GROUP BY parent_id');
            $channels_group = $channel_group_query->queryAll();
            if ($channels_group[0]["COUNT(parent_id)"] == 1) {
                foreach ($channels as $channel_item) {
                    if ($channel_item["parent_id"] == $channel_parent["id"]) {
                        $role_channel_permission[$channel_item["id"]] = $channel_item["name"];
                        break;
                    }
                }
            }
            else {
                $children = array();
                foreach ($channels as $channel_item) {
                    if ($channel_item["parent_id"] == $channel_parent["id"]) {
                        $children[$channel_item["id"]] = $channel_item["name"];
                    }
                }
                $role_channel_permission[$channel_parent["name"]] = $children;
            }
        }

        $role_other_permission = array();
        $permission_others = PermissionOther::find()->all();
        foreach ($permission_others as $permission_other) {
            $role_other_permission[$permission_other["name"]] = $permission_other['label'];
        }
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $roleArray = Yii::$app->request->post();
            $role = $roleArray["UserPermission"]["title"];
            $role_menu_select = $roleArray["role_menu_select"];
            $menu_permission = "";
            foreach ($role_menu_select as $key => $value) {
                $menu_permission = $menu_permission.$value.", ";
            }
            if(strlen($menu_permission) > 0) $menu_permission = substr($menu_permission, 0, strlen($menu_permission) - 2);
            $model->menu_permission = $menu_permission;

            $role_channel_select = $roleArray["role_channel_select"];
            $channel_permission = "";
            foreach ($role_channel_select as $key => $value) {
                $channel_permission = $channel_permission.$value.", ";
            }
            if(strlen($channel_permission) > 0) $channel_permission = substr($channel_permission, 0, strlen($channel_permission) - 2);
            $model->channel_permission = $channel_permission;

            $role_other_select = $roleArray["role_other_select"];
            $other_permission = "";
            foreach ($role_other_select as $key => $value) {
                $other_permission = $other_permission.$value.", ";
            }
            if(strlen($other_permission) > 0) $other_permission = substr($other_permission, 0, strlen($other_permission) - 2);
            $model->other_permission = $other_permission;

            if ($model->save(false)) {
                $permission_channel = Connection::find()->all();
                $permission_data = UserPermission::find()->where(['user_id' => Yii::$app->user->identity->id])->all();
                $role_data = array();
                $i = 0;
                foreach ($permission_data as $permission) {

                    $role_data[$i]['id'] = $permission['id'];
                    $role_data[$i]['title'] = $permission['title'];
                    $role_data[$i]['menu_permission'] = CustomFunction::getPermissionMenuLabel($permission['menu_permission']);
                    $role_data[$i]['channel_permission'] = CustomFunction::getPermissionChannelLabel($permission['channel_permission']);
                    $i ++;
                }
                return $this->render('index', ['role_data' => $role_data]);
            }
            else {
                Yii::$app->session->setFlash('danger', 'Error! UserRole are not Updated Successfully');
                return $this->render('create', [
                    'model' => $model,
                    'role_menu_permission' => $role_menu_permission,
                    'role_channel_permission' => $role_channel_permission,
                    'role_other_permission' => $role_other_permission,
                ]);
            }

        } else {
            return $this->render('create', [
                'model' => $model,
                'role_menu_permission' => $role_menu_permission,
                'role_channel_permission' => $role_channel_permission,
                'role_other_permission' => $role_other_permission,
            ]);
        }
    }
    /**
     * Deletes an existing UserRole model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {

        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            return $this->goBack();
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the UserRole model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = UserPermission::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}