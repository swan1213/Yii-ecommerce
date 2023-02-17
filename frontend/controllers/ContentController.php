<?php

namespace frontend\controllers;
use Yii;
use frontend\components\BaseController;
use yii\helpers\Html;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Content;
//use common\models\ChannelConnection;

class ContentController extends BaseController {
    
    
     public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create','update'],
                'rules' => [
                        [
                        'actions' => [''],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        'actions' => ['index', 'create', 'update'],
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

    public function actionIndex() {
        $user_id = Yii::$app->user->identity->id;
        $pages_all = Content::find()->where(['user_id' => $user_id])->asArray()->all();
        return $this->render('index', [
            'view_all' => $pages_all
        ]);
    }
//    
    public function actionGetpages() {
        $user_id = Yii::$app->user->identity->id;
        $post = Yii::$app->request->get();
         $start = $post['start'];
        $length = $post['length'];
        $draw = $post['draw'];
        $search = $post['search']['value'] ? $post['search']['value'] : '';
        $orderby = $post['order']['0']['column'] ? $post['order']['0']['column'] : '';
        $sortbyvalue = true;
        $orderby_str = 'title';
        if($orderby == 0) {
            $orderby_str = 'title';
        } else {
            $orderby_str = 'id';
        }
        
        $asc = $post['order']['0']['dir'] ? $post['order']['0']['dir'] : '';
        
        $count = Content::find()->where(['user_id' => $user_id])->count();
        $pages_all = Content::find()
                ->limit($length)->offset($start)
                ->Where(['user_id' => $user_id])
                ->andFilterWhere([
                    'or',
                        ['like', 'title', $search],
                ])
                ->orderBy($orderby_str . " " . $asc)
                ->asArray()
                ->all();
        $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count);
        $final_response = array();
        if(!empty($pages_all)) {
            foreach($pages_all as $pages) {
                
                $page_title =  $pages['title'];
                $page_delete = Html::a('', ['/content/delete', 'id' => $pages['id']], [
                                       'class' => 'mdi mdi-delete',
                                       'data' => [
                                           'confirm' => 'Are you sure you want to delete this page?',
                                           'method' => 'post',
                                       ],
                                   ]);
                $page_update = Html::a('', ['/content/update/', 'id' => $pages['id']], [
                                       'class' => 'mdi mdi-edit',
                                   ]);
                
                
                $final_response[0] = $page_title;
                $final_response[1] = $page_delete . ' ' . $page_update;
                $data_arr[] = $final_response; 
            }

            $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $data_arr);
            echo json_encode($response_arr);
        } else {
              $response_arr = array("draw" => $draw, "recordsTotal" => $count, "recordsFiltered" => $count, "data" => array());
            echo json_encode($response_arr);
        }
    }
    
    public function actionCreate() {
        $model = new Content();
        $user_id = Yii::$app->user->identity->id;
        
        if (Yii::$app->request->post()) {
            
         $page_title = $_POST['page_title'];
         $page_description = $_POST['page_desc'];
         $created_at = date('Y-m-d h:i:s', time());
         
         $model->user_id = $user_id;
         $model->title = $page_title;
         $model->description = $page_description;
         $model->created_at = $created_at;
         
         if($model->save(false)) {
             
             Yii::$app->session->setFlash('success', 'Success! Page has been created.');
                return $this->redirect(['/content/index']);
         }

            
    } else {
        return $this->render('create', [
            'model' => $model
        ]);
    }
       return $this->render('create', [
            'model' => $model
        ]); 
        
    }
    
    
    public function actionUpdate($id) {
 
        $model = Content::find()->where(['id' => $id])->one();
        return $this->render('update', [
            'model' => $model
        ]);
        
    }
    
    public function actionUpdatepage() {

         $model = new Content();
        if(Yii::$app->request->post()) {
            $page_id = $_POST['page_id'];
            $model = Content::find()->where(['id' => $page_id])->one();
            $model->title = $_POST['page_title'];
            $model->description = $_POST['page_desc'];
            if($model->save()) {
                Yii::$app->session->setFlash('success', 'Success! Page has been updated.');
                 return $this->redirect(['/content/index']);
                
            } else {
                return $this->render('update', [
                    'model' => $model
            ]);
            }      
        }
        
    }
    
        public function actionDelete($id) {
            $model = Content::find()->where(['id' => $id])->one()->delete();
            
//        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    
    public function actionTestCall() {
        $tzlist = \DateTimeZone::listIdentifiers();
       echo '<pre>';
        print_r($tzlist);
    }
    

}
