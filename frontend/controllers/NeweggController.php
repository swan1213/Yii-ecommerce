<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

use common\models\Connection;
use common\models\ConnectionParent;
use common\models\Country;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\NeweggConnectionForm;
use frontend\components\ConsoleRunner;
use common\components\newegg\NeweggMarketplace;
use frontend\components\CustomFunction;

/**
 * NeweggController implements for Channels model.
 */
class NeweggController extends Controller {
    /**
     * action filter
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($id) {
        $is_connect_channel = false;
        $user_id = Yii::$app->user->identity->id;

        // check if this connection is still existed in Connection table
        $connection_row = Connection::find()->where(['id' => $id])->one();

        // connection is not exsited
        if(empty($connection_row)) {
            return $this->redirect(['/channels']);
        }

        $connection_name = $connection_row->name;

        $user_connection_row = UserConnection::find()->where([
            'connection_id' => $id,
            'user_id' => $user_id])->one();
        $user_connection_id = '';

        if(!empty($user_connection_row)) {
            $user_connection_id = $user_connection_row->id;

            if($user_connection_row->connected == UserConnection::CONNECTED_YES or 
                $user_connection_row->import_status == UserConnection::IMPORT_STATUS_PROCESSING) {
                $is_connect_channel = true;
            }
        }

        $model = new NeweggConnectionForm();

        return $this->render('index', [
            'id' => $id,
            'is_connect_channel' => $is_connect_channel,
            'connection_name' => $connection_name,
            'user_connection_id' => $user_connection_id,
            'model' => $model
        ]);
    }

    public function actionAuthorize($id) {
        $user_id = Yii::$app->user->identity->id;
        $model = new NeweggConnectionForm();

        if ($model->load(Yii::$app->request->post())) {
            $model->item_file = UploadedFile::getInstance($model, 'item_file');

            if ($model->item_file && $model->validate()) {
                // get form parameters
                $post_data = Yii::$app->request->post('NeweggConnectionForm');
                $seller_id = $post_data['seller_id'];
                $api_key = $post_data['api_key'];
                $secret_key = $post_data['secret_key'];

                try {
                    $connection_row = Connection::find()->where(['id' => $id])->one();

                    // connection is not exsited
                    if(empty($connection_row)) {
                        throw new \Exception('Invalid Connection Id');
                    }

                    $connection_name = $connection_row->name;

                    $user_credential = array(
                        'market_id' => $seller_id,
                        'api_key' => $api_key,
                        'secret_key' => $secret_key,
                        'type' => $connection_name
                    );

                    $country_info = array(
                        'store_url' => 'www.newegg.com',
                        'country_code' => '',
                        'currency_code' => ''
                    );

                    $connection_parent_model = ConnectionParent::find()->where(['id' => $connection_row->parent_id])->one();
                    if(empty($connection_parent_model)) {
                        throw new \Exception('Invalid Connection.');
                    }
                    
                    $store_name = $connection_parent_model->name.' '.$connection_name;
                    // get seller status
                    $new_egg = new NeweggMarketplace(
                        $connection_name,
                        $seller_id,
                        $api_key,
                        $secret_key
                    );
                    $status_response = $new_egg->getSellerStatus();
                    $response_json = json_decode($status_response, true);

                    if($response_json['IsSuccess'] != true) {
                        throw new \Exception('Your credential is invalid.');
                    }

                    $business_country_code = 'USA';
                    $fulfillment_list = [];

                    if(isset($response_json['ResponseBody']['FufillmentCenterList'])) {
                        $business_country_code = $response_json['ResponseBody']['FufillmentCenterList'][0]['WarehouseLocation'];
                        foreach ($response_json['ResponseBody']['FufillmentCenterList'] as $single_fufillment) {
                            $fulfillment_list[] = $single_fufillment['WarehouseType'];
                        }
                    }
                    $file_path = $this->uploadZipFile($model->item_file);

                    // import user information
                    $user_connection_id = $this->importUserInfo(
                        $user_id,
                        $user_credential['market_id'],
                        $connection_row->id,
                        $user_credential,
                        $fulfillment_list
                    );
                    $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
                    $this->importUserDetail(
                        $user_connection_id,
                        $business_country_code,
                        $store_name,
                        $file_path
                    );

                    $importCr = new ConsoleRunner(['file' => '@console/yii']);
                    $importCmd = 'channel-import/newegg '.$user_connection_id;
                    $res = $importCr->run($importCmd);

                    $user_connection_row->import_status = UserConnection::IMPORT_STATUS_PROCESSING;
                    $user_connection_row->save(true, ['import_status']);

                    echo json_encode([
                        'success' => true,
                        'message' => 'Your Newegg Channel has been connected successfully. Importing has started and you will be notified once it is completed.'
                    ]);
                } catch( \Exception $e) {
                    if(empty($user_connection_id)) {
                        UserConnection::setFailStatus($user_connection_id);    
                    }

                    if(!empty($file_path)) {
                        $path_parts = pathinfo($file_path);
                        $directory_path = $path_parts['dirname'];
                        \frontend\components\CustomFunction::delete_directory($directory_path);
                    }

                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function actionUpdateBatch() {
        $post_data = Yii::$app->request->post();
        $user_connection_id = $post_data['user_connection_id'];
        $file = UploadedFile::getInstanceByName('file');

        try {
            if($file and $user_connection_id) {
                $file_path = $this->uploadZipFile($file);
                $this->checkZipFile($file_path);

                $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
                if(empty($user_connection_detail_model)) {
                    throw new \Exception('This user connection is not existed.');
                }

                if(!empty($user_connection_detail_model->others)) {
                    $path_info = $user_connection_detail_model->others;
                    $path_parts = pathinfo($path_info['file']);
                    $directory_path = $path_parts['dirname'];
                    \frontend\components\CustomFunction::delete_directory($directory_path);
                }

                $user_connection_detail_model->others = json_encode(array(
                    'file' => $file_path
                ));
                $user_connection_detail_model->updated_at = date('Y-m-d H:i:s', time());
                $user_connection_detail_model->save(false);

                echo json_encode([
                    'success' => true,
                    'message' => 'File update is successful!'
                ]);
            } else {
                throw new \Exception('Empty file.');
            }
        } catch (\Exception $e) {
            if(!empty($file_path)) {
                $path_parts = pathinfo($file_path);
                $directory_path = $path_parts['dirname'];
                \frontend\components\CustomFunction::delete_directory($directory_path);
            }
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function checkZipFile($file_path) {
        $zip = new \ZipArchive();
        $res = $zip->open($file_path);

        if ($res === true) {
            $path_parts = pathinfo($file_path);
            $directory_path = $path_parts['dirname'];
            for( $i = 0; $i < $zip->numFiles; $i++ ){
                $file_parts = pathinfo($zip->getNameIndex($i));
                if($file_parts['extension'] != 'csv') {
                    throw new \Exception('Invaild file type. We only accept the csv file at a moment.');
                }
            }
        } else {
            $prefix_error_msg = 'When open the zip file you provided, ';
            $error_msg = "Can't open zip file.";
            switch ($res) {
                case \ZipArchive::ER_EXISTS:
                    $error_msg = 'File already exists.';
                    break;

                case \ZipArchive::ER_INCONS:
                    $error_msg = 'Zip archive inconsistent.';
                    break;

                case \ZipArchive::ER_INVAL:
                    $error_msg = 'Invalid argument.';
                    break;

                case \ZipArchive::ER_MEMORY:
                    $error_msg = 'Malloc failure.';
                    break;

                case \ZipArchive::ER_NOENT:
                    $error_msg = 'No such file.';
                    break;

                case \ZipArchive::ER_NOZIP:
                    $error_msg = 'Not a zip archive.';
                    break;

                case \ZipArchive::ER_OPEN:
                    $error_msg = "Can't open file.";
                    break;

                case \ZipArchive::ER_READ:
                    $error_msg = 'Read error.';
                    break;

                case \ZipArchive::ER_SEEK:
                    $error_msg = 'Seek error.';
                    break;
            }

            throw new \Exception($prefix_error_msg.$error_msg);
        }
    }

    /**
    * save the data in UserConnectionDetails table
    * $user_connection_id: id in User table
    * $country_code: country code in Country table
    * $store_name: store name
    * $file_path: newegg items zip file path
    */
    public function importUserDetail($user_connection_id, $country_code, $store_name, $file_path) {
        $country_row = Country::find()->where(['sortname' => $country_code])->one();
        $country_name = '';
        $currency_code = '';
        $currency_symbol = '';

        if(!empty($country_row)) {
            $country_name = $country_row->name;
            $currency_code = $country_row->currency_code;
            $currency_symbol = $country_row->currency_symbol;
        }

        $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        if(empty($user_connection_detail_model)) {
            $user_connection_detail_model = new UserConnectionDetails();
        } else {
            if(!empty($user_connection_detail_model->others)) {
                $path_info = $user_connection_detail_model->others;
                $path_parts = pathinfo($path_info['file']);
                $directory_path = $path_parts['dirname'];
                \frontend\components\CustomFunction::delete_directory($directory_path);
            }
        }

        $user_connection_detail_model->user_connection_id = $user_connection_id;
        $user_connection_detail_model->store_name = 'Newegg';
        $user_connection_detail_model->store_url = 'https://www.newegg.com';
        $user_connection_detail_model->country = $country_name;
        $user_connection_detail_model->country_code = $country_code;
        $user_connection_detail_model->currency = $currency_code;
        $user_connection_detail_model->currency_symbol = $currency_symbol;
        $user_connection_detail_model->others = json_encode(array(
            'file' => $file_path
        ));
        $user_connection_detail_model->created_at = date('Y-m-d H:i:s', time());
        $user_connection_detail_model->save(false);
    }

    /**
    * save the data in UserConnection table
    * $user_id: id in User table
    * $market id: market id
    * $connection_id: id in Connection table
    * $user_credential: credential for a channel connection
    * $fulfillment_list: fulfillment list
    */
    public function importUserInfo(
        $user_id,
        $market_id,
        $connection_id,
        $user_credential,
        $fulfillment_list
    ) {
        $fulfillment = 0;
        if(count($fulfillment_list) > 1) {
            $fulfillment = 2;
        } else {
            $fulfillment = $fulfillment_list[0];
        }
        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where([
            'connection_id' => $connection_id,
            'user_id' => $user_id,
            'market_id' => $market_id])->one();

        date_default_timezone_set("UTC");

        if(empty($user_connection_row)) {
            // create new UserConnection
            $new_user_connection_model = new UserConnection();
            $new_user_connection_model->user_id = $user_id;
            $new_user_connection_model->connection_id = $connection_id;
            $new_user_connection_model->market_id = $market_id;
            $new_user_connection_model->connection_info = json_encode($user_credential);
            $new_user_connection_model->import_status = UserConnection::IMPORT_STATUS_FAIL;
            $new_user_connection_model->connected = UserConnection::CONNECTED_NO;
            $new_user_connection_model->smartling_status = 0;
            $new_user_connection_model->fulfillment_list_id = $fulfillment;
            $new_user_connection_model->created_at = date('Y-m-d h:i:s', time());
            $new_user_connection_model->save(false);
            $saved_user_connection_id = $new_user_connection_model->id;
        } else {
            //update the UserConnection row
            $user_connection_row->user_id = $user_id;
            $user_connection_row->connection_id = $connection_id;
            $user_connection_row->market_id = $market_id;
            $user_connection_row->connection_info = json_encode($user_credential);
            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_FAIL;
            $user_connection_row->connected = UserConnection::CONNECTED_NO;
            $user_connection_row->smartling_status = 0;
            $user_connection_row->fulfillment_list_id = $fulfillment;
            $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
            $user_connection_row->save(false);
            $saved_user_connection_id = $user_connection_row->id;
        }

        return $saved_user_connection_id;
    }

    /**
    * upload zip file to a server
    * @$file: file information object
    */
    private function uploadZipFile($file) {
        // get curret timestamp based on UTC timezone
        $date = new \DateTime();
        $date->format("U");
        $timestamp = $date->getTimestamp();

        $directory_path = join(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@storage'),
            'web',
            'source',
            'new_egg',
            $timestamp
        ]);

        if(!is_dir($directory_path)) {
            mkdir($directory_path, 0777, true);
        }

        $file_path = join(DIRECTORY_SEPARATOR, [
            $directory_path,
            uniqid().'-'.$file->name
        ]);
        
        $save_file = $file->saveAs($file_path);

        if($save_file == true) {
            return $file_path;
        }

        throw new \Exception('Fail to upload the file. Please try again.');
    }
}