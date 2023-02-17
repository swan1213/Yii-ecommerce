<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018-01-29
 * Time: 11:57 PM
 */

namespace console\controllers;


use common\models\ProductTranslation;
use common\models\Smartling;
use common\models\SmartlingPrice;
use common\models\User;
use common\models\UserConnection;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\File\FileApi;
use Smartling\File\Params\DownloadFileParameters;
use Yii;
use yii\console\Controller;
use common\commands\SendEmailCommand;
use common\models\Product;
use common\models\Notification;

class SmartlingSyncController extends Controller
{
    public function actionCheckDownload(){
        $basedir = Yii::getAlias('@base');
        $autoloader = $basedir . '/example-jobs-api/vendor/autoload.php';

        if (!file_exists($autoloader) || !is_readable($autoloader)) {
            echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
            exit;
        } else {
            /** @noinspection UntrustedInclusionInspection */
            require_once $basedir . '/example-jobs-api/vendor/autoload.php';
        }

        print_r("start smartling checking");
        $smartling = Smartling::find()->Where(['<>', 'job_download_status', 'COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED'])->all();
        foreach ($smartling as $smartling_data) {
            $date1 = $smartling_data->created_at;
            $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($date1);
            $min = $seconds / 60;

            $user_id		= $smartling_data->user_id;
            $token_name		= $smartling_data->token;
            $userInfo = User::findOne(["id"=>$user_id]);
            $domain_name = $userInfo->domain;
            $folder_name = $domain_name . '_' . $user_id;
            $projectId		= $smartling_data->project_id;
            $locale		= $smartling_data->target_locale;
            $userSecretKey	= $smartling_data->secret_key;
            $userIdentifier	= $smartling_data->sm_user_id;
            $fileName		= $smartling_data->job_callbackUrl;
            $jobId		= $smartling_data->job_translationJobUid;
            $project_type_value = $smartling_data->project_type_value;

            $price = $smartling_data->translation_type['price'];
            $PriceTaken = $smartling_data->translation_type['type'];


            $retrievalType = 'published';
            $basedir = Yii::getAlias('@storage');


            $domain_folder_path = $basedir . '/smartling_files/' . $folder_name;
            if (!file_exists($domain_folder_path)) {
                mkdir($domain_folder_path, 0777, true);
            }

            $create_token_folder = $domain_folder_path . '/' . $token_name;
            $token_file = $create_token_folder . '/' . $token_name . '.json';

            if($smartling_data->job_download_status == "pending") {
                if (!file_exists($create_token_folder)) {
                    mkdir($create_token_folder);
                    $create_token_file = fopen($token_file, "w") or die("Unable to open file!");
                    /* Call Smartling file Download Function */
                    $this->SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId, $smartling_data);
                } else {
                    $this->SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId, $smartling_data);
                }
            }

            if($smartling_data->job_download_status == "COMPLETED-DOWNLOADED"){
                try{
                    if ($token_name != '') {
                        $file_decode_data = self::getTranslatedData($token_file);
                        if ($file_decode_data != 'no data') {
                            foreach ($file_decode_data as $file_value) {
                                if (isset($file_value['p_id'])) {
                                    $sm_id = $smartling_data->id;
                                    $product_id = $file_value['p_id'];
                                    $desctription = $file_value['description'];
                                    $product_name = $file_value['name'];
                                    $brand = $file_value['brand'];
                                    $product_translation = ProductTranslation::findOne(['product_id'=>$product_id, 'smartling_id'=>$sm_id]);
                                    if(empty($product_translation)){
                                        $product_translation = new ProductTranslation();
                                        $product_translation->smartling_id = $sm_id;
                                        $product_translation->product_id = $product_id;
                                    }
                                    $product_translation->name = $product_name;
                                    $product_translation->description = $desctription;
                                    $product_translation->brand = $brand;
                                    $product_translation->save(false);
//                                    $product = Product::findOne(['id'=>$product_id]);
//                                    var_dump($product_id);
//                                    if(!empty($product)) {
//                                        $product->name = $product_name;
//                                        $product->description = $desctription;
//                                        $product->brand = $brand;
//                                        $product->save(true, ['name', 'description', 'brand']);
//                                        var_dump("updated");
//                                    }
//                                    else{
//                                        var_dump("no found");
//                                    }
                                }
                            }
                            $smartling_data->translated_data_process = 1;
                            $smartling_data->job_download_status = "COMPLETED-DOWNLOADED-PRODUCT-STATUS-CHANGED";
                            $smartling_data->save(true, ['job_download_status', 'translated_data_process']);
                        }
                    }
                }
                catch(Exception $ex){
                    echo("error");
                    return;
                }
            }
//            $job_referenceNumber = $smartling_data->job_referenceNumber;
//            if (empty($job_referenceNumber)) {
//                if (!empty($project_type_value)) {
//                    if ($PriceTaken == 'Google MT with Edit') {
//                        $selectRate = 'rate2';
//                        $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $locale])->one();
//                        $priceUnitCost = $SmartlingPrice->post_edit;
//                    } else if ($PriceTaken == 'Translation with Edit') {
//                        $selectRate = 'editing';
//                        $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $locale])->one();
//                        $priceUnitCost = $SmartlingPrice->editing;
//                    } else {
//                        $priceUnitCost = 1;
//                    }
//                    $data = $price;
//                    //$this->Subscriptionpaymentcustom($userIdentifier, $userSecretKey, $project_id, $jobId, $customer_token, $email, $user_domain, $id, $priceUnitCost, $project_type_value, $token_name);
//                }
//                $smartling_data->job_referenceNumber = 'Done';
//                $smartling_data->save(true, ['job_referenceNumber']);
//            }
        }
    }

    public function SmartlingFileDownload($userIdentifier, $userSecretKey, $projectId, $retrievalType, $fileName, $locale, $token_file, $jobId, $smartlingModel) {
        /* Check File Status  */
        $file_download_status = $this->checkFileTranslationStatus($userIdentifier, $userSecretKey, $projectId, $jobId);
        echo $file_download_status;
        if ($file_download_status != 'curl_error') {
            if ($file_download_status == "COMPLETED") {
                /** Download file example */
                try {
                    $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
                    $fileApi = FileApi::create($authProvider, $projectId);
                    $params = new DownloadFileParameters();
                    $params->setRetrievalType($retrievalType);
                    $file_data = $fileApi->downloadFile($fileName, $locale, $params);
                    file_put_contents($token_file, $file_data);
                    $smartlingModel->job_download_status = "COMPLETED-DOWNLOADED";
                    $smartlingModel->save(true, ['job_download_status']);
                    return $file_data;
                } catch (\Smartling\Exceptions\SmartlingApiException $e) {
                    $messageTemplate = 'Error happened while downloading file.' . PHP_EOL
                        . 'Response code: %s' . PHP_EOL
                        . 'Response message: %s' . PHP_EOL;
                    echo vsprintf(
                        $messageTemplate, [
                            $e->getCode(),
                            $e->getMessage(),
                        ]
                    );
                }
            }
        }
    }

    public function checkFileTranslationStatus($userIdentifier, $userSecret, $projectId, $translationJobUid) {

        $data_string = '{
                    "userIdentifier": "' . $userIdentifier . '",
                    "userSecret": "' . $userSecret . '"
                  }';
        $curl = curl_init();
        $url = 'https://api.smartling.com/auth-api/v2/authenticate';
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => 900,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "X-Auth-Token: BfcqrhanfJghxYZQe8PtDhUMuQHaPtqqRuQZtpZE&jkfnkCfeRBVXLsiBqFdXDaB",
                "content-type: application/json"
            ),
        ));
        $resultAuth = curl_exec($curl);
        $authData = json_decode($resultAuth);

        $access_token = $authData->response->data->accessToken;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.smartling.com/jobs-api/v3/projects/" . $projectId . "/jobs/" . $translationJobUid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "Authorization: Bearer " . $access_token,
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return 'curl_error';
        } else {
            $result = json_decode($response);
            //echo "<pre>"; print_r($result);
            return $result->response->data->jobStatus;
        }
    }

    public static function getTranslatedData($token_file) {
        if (file_exists($token_file)) {
            $file_data = file_get_contents($token_file);
            $file_decode_data = json_decode($file_data, true);
            if (strlen($file_data) == 0) {
                return 'no data';
            } else {
                return $file_decode_data;
            }
        } else {
            return 'no data';
        }
    }
}