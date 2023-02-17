<?php

namespace frontend\controllers;

ob_start();

//session_start();

use Yii;
use frontend\components\BaseController;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\Customer;
use common\models\User;
use common\models\Country;
use common\models\State;
use common\models\City;
use common\models\UserConnection;
use Bigcommerce\Api\Client as Bigcommerce;
use common\models\Order;
use common\models\OrderProduct;
use common\models\OrderConnection;
use common\models\Product;
use common\models\Category;
use common\models\ProductCategory;
use common\models\ProductConnection;
use common\models\ProductImage;
use common\models\ProductVariation;
use common\models\VariationSet;
use common\models\Variation;
use common\models\VariationItem;
use common\models\DocumentInfo;
use common\models\DocumentDirector;
use common\models\DocumentFile;


use Automattic\WooCommerce\Client as Woocommerce;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;

/**
 * CustomerUserController implements the CRUD actions for CustomerUser model.
 */
class DocumentsController extends BaseController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
	return [
	    'access' => [
		'class' => AccessControl::className(),
		'only' => ['index', 'add-update-document', 'upload-documents'],
		'rules' => [
			[
			'actions' => ['index', 'add-update-document', 'upload-documents'],
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
     * Lists all CustomerUser models.
     * @return mixed
     */
    public function beforeAction($action) {

	$this->enableCsrfValidation = false;


	return parent::beforeAction($action);
    }

    public function actionIndex() {
        $currentUserId = Yii::$app->user->identity->id;
	    $model			=	DocumentInfo::findOne(['user_id' => $currentUserId ]);
	    $document_director	=	DocumentDirector::findAll(['user_id' => $currentUserId ]);
	
        return $this->render('index', [
            'model' => $model,
            'document_director' => $document_director
        ]);
    }

    function actionAddUpdateDocument() {
	    $post_data = Yii::$app->request->post();

        if (isset($post_data) and ! empty($post_data)) {
            $document_info = DocumentInfo::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
            if (empty($document_info)) {
                $document_info = new DocumentInfo();
            }
            $document_info->user_id = Yii::$app->user->identity->id;
            //$document_info->channel_id = '131';
            $document_info->bank_account_no = isset($post_data['account_no']) ? $post_data['account_no'] : '';
            $document_info->bank_roting_no = isset($post_data['routing_no']) ? $post_data['routing_no'] : '';
            $document_info->bank_code = isset($post_data['bank_code']) ? $post_data['bank_code'] : '';
            $document_info->bank_name = isset($post_data['bank_name']) ? $post_data['bank_name'] : '';
            $document_info->bank_address = isset($post_data['bank_address']) ? $post_data['bank_address'] : '';
            $document_info->bank_swift = isset($post_data['swift']) ? $post_data['swift'] : '';
            $document_info->bank_account_type = isset($post_data['account_type']) ? $post_data['account_type'] : '';
            $document_info->business_tax_id = isset($post_data['tax_id']) ? $post_data['tax_id'] : '';

            $document_info->alipay_payment_account_no = isset($post_data['alipay_payment_account_no']) ? $post_data['alipay_payment_account_no'] : '';
            $document_info->alipay_payment_account_id = isset($post_data['alipay_payment_account_id']) ? $post_data['alipay_payment_account_id'] : '';
            $document_info->alipay_payment_account_email = isset($post_data['alipay_payment_account_email']) ? $post_data['alipay_payment_account_email'] : '';
            $document_info->alipay_payment_address_1 = isset($post_data['alipay_payment_address_1']) ? $post_data['alipay_payment_address_1'] : '';
            $document_info->alipay_payment_address_2 = isset($post_data['alipay_payment_address_2']) ? $post_data['alipay_payment_address_2'] : '';
            $document_info->alipay_payment_account_city = isset($post_data['alipay_payment_account_city']) ? $post_data['alipay_payment_account_city'] : '';
            $document_info->alipay_payment_account_state = isset($post_data['alipay_payment_account_state']) ? $post_data['alipay_payment_account_state'] : '';
            $document_info->alipay_payment_account_country = isset($post_data['alipay_payment_account_country']) ? $post_data['alipay_payment_account_country'] : '';
            $document_info->alipay_payment_account_zip_code = isset($post_data['alipay_payment_account_zip_code']) ? $post_data['alipay_payment_account_zip_code'] : '';

            $document_info->dinpay_payment_account_no = isset($post_data['dinpay_payment_account_no']) ? $post_data['dinpay_payment_account_no'] : '';
            $document_info->dinpay_payment_account_id = isset($post_data['dinpay_payment_account_id']) ? $post_data['dinpay_payment_account_id'] : '';
            $document_info->dinpay_payment_account_email = isset($post_data['dinpay_payment_account_email']) ? $post_data['dinpay_payment_account_email'] : '';
            $document_info->dinpay_payment_address_1 = isset($post_data['dinpay_payment_address_1']) ? $post_data['dinpay_payment_address_1'] : '';
            $document_info->dinpay_payment_address_2 = isset($post_data['dinpay_payment_address_2']) ? $post_data['dinpay_payment_address_2'] : '';
            $document_info->dinpay_payment_account_city = isset($post_data['dinpay_payment_account_city']) ? $post_data['dinpay_payment_account_city'] : '';
            $document_info->dinpay_payment_account_state = isset($post_data['dinpay_payment_account_state']) ? $post_data['dinpay_payment_account_state'] : '';
            $document_info->dinpay_payment_account_country = isset($post_data['dinpay_payment_account_country']) ? $post_data['dinpay_payment_account_country'] : '';
            $document_info->dinpay_payment_account_zip_code = isset($post_data['dinpay_payment_account_zip_code']) ? $post_data['dinpay_payment_account_zip_code'] : '';

            $document_info->payoneer_payment_account_no = isset($post_data['payoneer_payment_account_no']) ? $post_data['payoneer_payment_account_no'] : '';
            $document_info->payoneer_payment_account_id = isset($post_data['payoneer_payment_account_id']) ? $post_data['payoneer_payment_account_id'] : '';
            $document_info->payoneer_payment_account_email = isset($post_data['payoneer_payment_account_email']) ? $post_data['payoneer_payment_account_email'] : '';
            $document_info->payoneer_payment_address_1 = isset($post_data['payoneer_payment_address_1']) ? $post_data['payoneer_payment_address_1'] : '';
            $document_info->payoneer_payment_address_2 = isset($post_data['payoneer_payment_address_2']) ? $post_data['payoneer_payment_address_2'] : '';
            $document_info->payoneer_payment_account_city = isset($post_data['payoneer_payment_account_city']) ? $post_data['payoneer_payment_account_city'] : '';
            $document_info->payoneer_payment_account_state = isset($post_data['payoneer_payment_account_state']) ? $post_data['payoneer_payment_account_state'] : '';
            $document_info->payoneer_payment_account_country = isset($post_data['payoneer_payment_account_country']) ? $post_data['payoneer_payment_account_country'] : '';
            $document_info->payoneer_payment_account_zip_code = isset($post_data['payoneer_payment_account_zip_code']) ? $post_data['payoneer_payment_account_zip_code'] : '';

            $document_info->worldfirst_payment_account_no = isset($post_data['worldfirst_payment_account_no']) ? $post_data['worldfirst_payment_account_no'] : '';
            $document_info->worldfirst_payment_account_id = isset($post_data['worldfirst_payment_account_id']) ? $post_data['worldfirst_payment_account_id'] : '';
            $document_info->worldfirst_payment_account_email = isset($post_data['worldfirst_payment_account_email']) ? $post_data['worldfirst_payment_account_email'] : '';
            $document_info->worldfirst_payment_address_1 = isset($post_data['worldfirst_payment_address_1']) ? $post_data['worldfirst_payment_address_1'] : '';
            $document_info->worldfirst_payment_address_2 = isset($post_data['worldfirst_payment_address_2']) ? $post_data['worldfirst_payment_address_2'] : '';
            $document_info->worldfirst_payment_account_city = isset($post_data['worldfirst_payment_account_city']) ? $post_data['worldfirst_payment_account_city'] : '';
            $document_info->worldfirst_payment_account_state = isset($post_data['worldfirst_payment_account_state']) ? $post_data['worldfirst_payment_account_state'] : '';
            $document_info->worldfirst_payment_account_country = isset($post_data['worldfirst_payment_account_country']) ? $post_data['worldfirst_payment_account_country'] : '';
            $document_info->worldfirst_payment_account_zip_code = isset($post_data['worldfirst_payment_account_zip_code']) ? $post_data['worldfirst_payment_account_zip_code'] : '';
            $document_info->created_at = date('Y-m-d H:i:s');
            $document_info->updated_at = date('Y-m-d H:i:s');
            $document_info->save();
            if (isset($post_data['directors']) and ! empty($post_data['directors'])) {
                $directors = $post_data['directors'];

                $directors_array = array();
                $file_count = count($directors['first_names']);
                $file_keys = array_keys($directors);

                //unset($file_keys[5]);
                for ($i = 0; $i < $file_count; $i++) {
                    foreach ($file_keys as $key) {
                        $directors_array[$i][$key] = $directors[$key][$i];
                    }
                }

                $hidden_directors_file_values = $post_data['hidden_directors_file_values'];
                if (isset($directors_array) and ! empty($directors_array)) {
                    foreach ($directors_array as $key => $single_director) {
                        $key_to_check=$key;
                        if($key_to_check>0){
                          $key_to_check++;
                        }

                        $user_id=isset($single_director['hidden_ids']) ? $single_director['hidden_ids'] : 0;
                        $document_data_check = DocumentDirector::find()->Where(['id'=>$user_id])->one();
                        if(empty($document_data_check)){
                            $director_model = new DocumentDirector();
                            $director_model->user_id = Yii::$app->user->identity->id;
                            $director_model->first_name = isset($single_director['first_names']) ? $single_director['first_names'] : '';
                            $director_model->last_name = isset($single_director['last_names']) ? $single_director['last_names'] : '';
                            $director_model->dob = isset($single_director['dobs']) ? date('Y-m-d H:i:s', strtotime($single_director['dobs'])) : '';

                            $director_model->address = isset($single_director['addresses']) ? $single_director['addresses'] : '';
                            $director_model->last_4_social = isset($single_director['last_4_socials']) ? $single_director['last_4_socials'] : '';

                            $director_model->document_file_id = isset($hidden_directors_file_values[$key_to_check]) ? $hidden_directors_file_values[$key_to_check] : '0';
                            $director_model->created_at = date('Y-m-d H:i:s');
                            $director_model->updated_at = date('Y-m-d H:i:s');
                            $director_model->save();
                        } else {
                            $document_data_check->first_name = isset($single_director['first_names']) ? $single_director['first_names'] : '';
                            $document_data_check->last_name = isset($single_director['last_names']) ? $single_director['last_names'] : '';
                            $document_data_check->dob = isset($single_director['dobs']) ? date('Y-m-d H:i:s', strtotime($single_director['dobs'])) : '';
                            $document_data_check->address = isset($single_director['addresses']) ? $single_director['addresses'] : '';
                            $document_data_check->last_4_social = isset($single_director['last_4_socials']) ? $single_director['last_4_socials'] : '';
                            $document_data_check->save();
                        }
                    }
                }
            }
        }
    }

    public function actionUploadDocuments() {
        //CorporateDocuments.
        $user_id = Yii::$app->user->identity->id;
        $ds = DIRECTORY_SEPARATOR;
        //$basedir = Yii::getAlias('@basedir');

        $maxsize = 26214400;
        /* For validation */
        if (isset($_FILES['file']) and ! empty($_FILES['file']) and isset($_GET['type']) and ! empty($_GET['type'])) {
            $file = $_FILES['file'];
            $upload_docs_dir = Yii::getAlias('@important_documents');
            $user_dir = $upload_docs_dir . '/' . $user_id;
            if (!is_dir($user_dir)) {
                mkdir($user_dir, 0777);
            }
            /* Starts buisness_file Document Upload */
            $tempFile = $file['tmp_name'];
            $file_name = $file['name'];
            $final_name = uniqid() . '_' . $file_name;
            $targetFile = $user_dir . $ds . $final_name;
            $buisness_file_upload = move_uploaded_file($tempFile, $targetFile);

            $document_file = DocumentFile::find()->Where(['user_id' => $user_id, 'type' => $_GET['type']])->one();
            if (empty($document_file) or ( !empty($document_file) and $_GET['type'] == 'directors')) {
                $document_file = new DocumentFile();
            } else {
                //$file_delete1 = unlink($basedir . '/' . $document_file->file_path);
            }
            $document_file->file_path = 'img/important_documents/' . $user_id . $ds . $final_name;
            $document_file->user_id = $user_id;
            $document_file->type = $_GET['type'];
            $document_file->created_at = date('Y-m-d h:i:s', time());
            $document_file->updated_at = date('Y-m-d h:i:s', time());

            $document_file->save(false);
    //                return $this->redirect('documents');
            echo json_encode([
            'msg' => 'success',
            'id' => $document_file->id
            ]);
            die;
        }
    }
    
    public function actionDeleteBankingDocuments(){
        $basedir = Yii::getAlias('@basedir');


        $status='error';
        $upload_docs_dir = Yii::getAlias('@important_documents');
        $post_data = Yii::$app->request->post();
        $doc_path=$post_data['imgpath'];
        $bank_id=$post_data['bank_id'];
        $type=$post_data['type'];

        $banking_file_data = DocumentFiles::find()->where(['id'=>$bank_id,'type'=>$type])->one();
        if(!empty($banking_file_data)){
            $banking_file_data->delete();
            $ulink_file=unlink($basedir.'/'.$doc_path);
            $status='success';
        }
        return $status;
        }

        function actionSpecialtitle() {
        $pro = Products::find()->all();
        $name_array = array();
        foreach ($pro as $_pro) {
            $name_array[] = $_pro->product_name;
        }

        echo "<pre>";
        print_r($name_array);
        echo "</pre>";
        echo "<br>";
        echo "<br>";
        echo "Encode data herer";
        echo "<br>";
        echo "<br>";
        echo json_encode($name_array);
        die;
    }
}
