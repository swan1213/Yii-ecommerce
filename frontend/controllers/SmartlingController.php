<?php

namespace frontend\controllers;

use common\models\ProductTranslation;
use common\models\User;
use common\models\Connection;
use common\models\SmartlingPrice;
use common\models\Product;
use common\models\Smartling;
use common\models\UserConnection;
use Yii;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\Params\UploadFileParameters;
use Smartling\File\FileApi;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
class SmartlingController extends \yii\web\Controller{


    public function actionTranslation() {

        /* $upload_docs_dir = Yii::getAlias('@upload_files');
          echo $upload_docs_dir;
          die; */

        //require_once('/var/www/html/smartling-api/vendor/composer/autoload_real.php');
        //$user_id = Yii::$app->user->identity->id;
        $ds = DIRECTORY_SEPARATOR;
        $basedir = Yii::getAlias('@basedir');


        $translateFile = $_FILES['smart-2'];
        $translate_filename = $translateFile['name'];
        $upload_docs_dir = Yii::getAlias('@upload_files');



        /* Starts buisness_file Document Upload */
        $buisness_tempFile = $translateFile['tmp_name'];
        $buisness_file_name = $translateFile['name'];
        $buisness_final_name = uniqid() . '_' . $buisness_file_name;
        $buisness_targetFile = $upload_docs_dir . $ds . $buisness_final_name;
        $buisness_file_upload = move_uploaded_file($buisness_tempFile, $buisness_targetFile);
        $filePath = $upload_docs_dir . '/' . $buisness_final_name;




        $options = [
            'project-id' => '7289bfb87',
            'user-id' => 'bhabaskqrnadgqgarxhuhawydiicml',
            'secret-key' => '2anhc34el124ngi7pd46epc21kVM_ui3vgsmu1usvlshgfnva3viu6j',
        ];


        if (
            !array_key_exists('project-id', $options) || !array_key_exists('user-id', $options) || !array_key_exists('secret-key', $options)
        ) {
            echo 'Missing required params.' . PHP_EOL;
            exit;
        }

        $autoloader = $basedir . '/smartling-api/vendor/autoload.php';

        if (!file_exists($autoloader) || !is_readable($autoloader)) {
            echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
            exit;
        } else {
            require_once $basedir . '/smartling-api/vendor/autoload.php';
        }



        $projectId = $options['project-id'];
        $userIdentifier = $options['user-id'];
        $userSecretKey = $options['secret-key'];

        $fileName = $translate_filename;
        $fileUri = $filePath;
        $fileRealPath = realpath($fileUri);
        $fileType = 'xml';
        $newFileName = 'new_test_file.xml';
        $retrievalType = 'published';
        $content = file_get_contents(realpath($fileUri));
        $fileContentUri = $translate_filename;
        $translationState = 'PUBLISHED';
        $locale = 'zh-CN';
        $locales_array = [$locale];

        $this->resetFiles($userIdentifier, $userSecretKey, $projectId, [$fileName, $newFileName]);


        /**
         * Upload file example
         */
        try {
            echo '::: File Upload Example :::' . PHP_EOL;

            $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

            $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
            $uploadParams = new \Smartling\File\Params\UploadFileParameters();
            $uploadParams->setCallbackUrl(env('SEVER_URL') . 'smartling/callback/');


            $translate_paths = [];
            $translate_paths[] = array("path"=> "/description");
            $translate_paths[] = array("path"=> "/name");
            $translate_paths[] = array("path"=> "/brand");

            $uploadParams->set("smartling.translate_paths", json_encode($translate_paths));
            $uploadParams->set("smartling.string_format_paths", "html: /description");
            //$uploadParams->set("smartling.string_format_paths", json_encode(array("html"=>"/description")));


            $result = $fileApi->uploadFile($fileRealPath, $fileName, $fileType, $uploadParams);
            $params = new UploadFileParameters();
            echo 'File upload result:' . PHP_EOL;
            echo var_export($result, true) . PHP_EOL . PHP_EOL;
        } catch (\Smartling\Exceptions\SmartlingApiException $e) {
            $messageTemplate = 'Error happened while uploading file.' . PHP_EOL
                . 'Response code: %s' . PHP_EOL
                . 'Response message: %s' . PHP_EOL;

            echo vsprintf(
                $messageTemplate, [
                    $e->getCode(),
                    $e->getMessage(),
                ]
            );
        }


        /**
         * Download file example
         */
        try {
            echo '::: File Download Example :::' . PHP_EOL;

            $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

            $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

            $params = new \Smartling\File\Params\DownloadFileParameters();
            $params->setRetrievalType($retrievalType);

            $result = $fileApi->downloadFile('test.xml', 'zh-CN', $params);

            echo 'File download result:' . PHP_EOL;
            echo var_export($result, true) . PHP_EOL . PHP_EOL;
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

    public function actionSaveSmartlingApi() {

        $request = Yii::$app->request;
        $userid = Yii::$app->user->identity->id;
        if (Yii::$app->request->post()) {
            $smartling = new Smartling();
            $smartling->project_id = Yii::$app->request->post('smrt_project_id');
            $smartling->sm_user_id = Yii::$app->request->post('sm_user_id');
            $smartling->secret_key = Yii::$app->request->post('secret_key');
            $smartling->created_at = date('Y-m-d h:i:s', time());

            if ($smartling->save(false)) {
                $response = ['status' => 'success', 'data' => 'Smartling Api Saved'];
            } else {
                $response = ['status' => 'error', 'data' => 'Smartling Api  not Saved'];
            }
        }
        echo json_encode($response);
        exit;
    }

    function resetFiles($userIdentifier, $userSecretKey, $projectId, $files = []) {
        $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

        foreach ($files as $file) {
            try {
                $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
                $fileApi->deleteFile($file);
            } catch (\Smartling\Exceptions\SmartlingApiException $e) {

            }
        }
    }

    public function actionCallback() {

        $user = User::find()->where(['id' => 1453])->one();
        $user_domain = $user->domain;
        $config = yii\helpers\ArrayHelper::merge(
            require($_SERVER['DOCUMENT_ROOT'] . '/common/config/main.php'), require($_SERVER['DOCUMENT_ROOT'] . '/common/users_config/' . $user_domain . '/main-local.php'), require($_SERVER['DOCUMENT_ROOT'] . '/backend/config/main.php'), require($_SERVER['DOCUMENT_ROOT'] . '/backend/config/main-local.php'));
        $application = new yii\web\Application($config);


        $locale = $_GET['locale'];
        $fileUri = $_GET['fileUri'];
        echo $locale . '--' . $fileUri;
        $OrderFullfillmentAlready = OrderFullfillment::find()->Where(['id' => '529'])->one();

        $OrderFullfillmentAlready->data = $locale . '--' . $fileUri;
        if ($OrderFullfillmentAlready->save(false)) {
            die('save');
        } else {
            echo'<pre>';
            print_r($OrderFullfillmentAlready->getErrors());
        }
        die('end here');
    }

    public function Workflowuid($projectId, $access_token) {
        $curl = curl_init();
        $url = ' https://api.smartling.com/workflows-api/v2/projects/' . $projectId . '/workflows';
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => 900,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            /* CURLOPT_POSTFIELDS => $data_string, */
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "Authorization: Bearer " . $access_token,
                "content-type: application/json"
            ),
        ));

        $result = curl_exec($curl);
        return $result;
    }

    public function actionTranslationchannelsettingdisable() {
        $user_connection_id = $_POST['user_connection_id'];
        $userConnectionModel = UserConnection::findOne(["id"=>$user_connection_id]);
        $userConnectionModel->smartling_status = UserConnection::SMARTLING_DISABLED;
        $userConnectionModel->save(true, ['smartling_status']);
        $smartlingData = Smartling::findAll(['user_connection_id' => $user_connection_id]);
        foreach ($smartlingData as $single){
            ProductTranslation::deleteAll(['smartling_id' => $single->id]);
        }
        Smartling::deleteAll(['user_connection_id' => $user_connection_id]);
        echo $userConnectionModel->getPublicName();
    }

    public function actionTranslationchannelcost() {
        $user_connection_id = $_POST['user_connection_id'];
        $selectTranslation = $_POST['selectTranslation'];
        $language = $_POST['language'];
        if ($selectTranslation == 'Google MT with Edit') {
            $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $language])->one();
            $priceUnitCost = $SmartlingPrice->post_edit;
        } else if ($selectTranslation == 'Translation with Edit') {
            $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $language])->one();
            $priceUnitCost = $SmartlingPrice->editing;
        } else {
            $priceUnitCost = 0;
        }
        $priceUnitCost = str_replace('$', '', $priceUnitCost);
        $wordcount = 0;

        //$products_data = Product::findAll(["user_connection_id"=>$user_connection_id]);
        $products_data = Product::find()
            ->joinWith(['productConnections'])
            ->Where(['product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'user_connection_id' => 3])
            ->all();
        foreach ($products_data as $product) {
            $wordcount = $wordcount + str_word_count(htmlspecialchars($product->name));
            $wordcount = $wordcount + str_word_count(htmlspecialchars($product->description));
            $wordcount = $wordcount + str_word_count(htmlspecialchars($product->brand));
        }
        $cost = $priceUnitCost * $wordcount;
        $endData = '
            <h5>Estimate Word Count : ' . $wordcount . '</h5>
            <h5>Tentative Cost : $' . $cost . '</h5>
            <button total_value_cost="'.$cost.'" total_word_count="'.$wordcount.'" price_per_word="'.$priceUnitCost.'" type="button" data-dismiss="modal" class="smartling_enble_controller btn btn-space btn-default  btn-primary btn-lg">Pay $'.$cost.' /-';
        return $endData;
    }

    public function actionTranslationchannelsetting() {
        $users_Id = Yii::$app->user->identity->id;
        $user_connection_id = $_POST['user_connection_id'];
        $userConnectionModel = UserConnection::findOne(["id"=>$user_connection_id]);
        $domain_name = Yii::$app->user->identity->domain;
        $filenameAdd = $domain_name . '_' . $users_Id;
        $enable = $_POST['enable'];
        $catenable = $_POST['smartlingyes'];
        $selectTranslation = $_POST['selectTranslation'];
        $language = $_POST['language'];
        $country = $userConnectionModel->userConnectionDetails->country;
        $namestore = $userConnectionModel->getPublicName();

        $wordcount = 0;
        $arrayNew = array();
        if ($selectTranslation == 'Google MT with Edit') {
            $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $language])->one();
            $priceUnitCost = $SmartlingPrice->post_edit;
        } else if ($selectTranslation == 'Translation with Edit') {
            $SmartlingPrice = SmartlingPrice::find()->Where(['locale_id' => $language])->one();
            $priceUnitCost = $SmartlingPrice->editing;
        } else {
            $priceUnitCost = 0;
        }
        $priceUnitCost = str_replace('$', '', $priceUnitCost);


        $products_data = Product::find()
            ->joinWith(['productConnections'])
            ->Where(['product.permanent_hidden' => Product::PRODUCT_PERMANENT_NO, 'user_connection_id' => 3])
            ->all();
        foreach ($products_data as $product) {
            $proid = $product->id;
            $name = htmlspecialchars($product->name);
            $description = htmlspecialchars($product->description);
            $brand = htmlspecialchars($product->brand);
            $array = array(
                'p_id' => "$proid",
                'name' => "$name",
                'description' => "$description",
                'brand' => "$brand"
            );
            $arrayNew[] = $array;
            $wordcount = $wordcount + str_word_count($name);
            $wordcount = $wordcount + str_word_count($description);
            $wordcount = $wordcount + str_word_count($brand);
        }

        $base_url = Yii::getAlias('@storage');
        $path = $base_url . '/smartling_files/' . $filenameAdd;
        $filemmmmae = $namestore . '.json';
        $pathFileName = $path . '/' . $namestore . '.json';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $data = json_encode($arrayNew);
        file_put_contents($pathFileName, $data);
        $price = $priceUnitCost * $wordcount;

        $account_name = $domain_name . '+' . $users_Id . '-Elliot-Global';
        Smartling::deleteAll(['user_connection_id' => $user_connection_id]);

        $smartlingModel = new Smartling();
        $data_string = '{
                            "accountName": "' . $account_name . '",
                            "productUid": "cbd55c56a878"
                        }';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => "https://api.smartling.com/accounts-api/v2/affiliated-accounts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => false,
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

        $result = curl_exec($curl);

        $data = json_decode($result);
        $accountUid = $data->response->data->accountUid;
        $userIdentifier = $data->response->data->apiCredentials->userIdentifier;
        $userSecret = $data->response->data->apiCredentials->userSecret;

        $accountUid = $accountUid;
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
            CURLOPT_TIMEOUT => false,
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
        $domain_name = Yii::$app->user->identity->domain;
        $Project_name = $domain_name . '+' . $users_Id . '-Elliot-Global-' . $namestore;
        $data_string = '{
                            "projectName": "' . $Project_name . '",
                            "projectTypeCode": "APPLICATION_RESOURCES",
                            "sourceLocaleId": "en",
                            "targetLocaleIds": ["' . $language . '"]
                        }';
        $curl = curl_init();
        $url = 'https://api.smartling.com/accounts-api/v2/accounts/' . $accountUid . '/projects';
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => false,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "Authorization: Bearer " . $access_token,
                "content-type: application/json"
            ),
        ));
        $result = curl_exec($curl);
        $data = json_decode($result);

        $projectId = $data->response->data->projectId;
        $projectTypeCode = $data->response->data->projectTypeCode;
        $projectTypeDisplayValue = $data->response->data->projectTypeDisplayValue;
        $targetLocales = $data->response->data->targetLocales;

        $smartlingModel->user_id = $users_Id;
        $smartlingModel->user_connection_id = $user_connection_id;
        $smartlingModel->sm_user_id = $userIdentifier;
        $smartlingModel->token = $namestore;
        $smartlingModel->translation_type = json_encode(array("price" => $price, "type" => $selectTranslation));
        $smartlingModel->account_id = $accountUid;
        $smartlingModel->account_name = $account_name;
        $smartlingModel->secret_key = $userSecret;
        $smartlingModel->created_at = date('Y-m-d', time());
        $smartlingModel->project_name = $Project_name;
        $smartlingModel->project_id = $projectId;
        $smartlingModel->project_type = $projectTypeCode;
        $smartlingModel->project_type_value = $projectTypeDisplayValue;
        $smartlingModel->connected = Smartling::CONNECTED_YES;
        $smartlingModel->job_download_status = "pending";
        $smartlingModel->save(false);
        $token = $namestore;

        $curl = curl_init();
        $url = 'https://api.smartling.com/workflows-api/v2/projects/' . $projectId . '/workflows';
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => false,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "Authorization: Bearer " . $access_token,
                "content-type: application/json"
            ),
        ));

        $result1 = curl_exec($curl);
        $data1 = json_decode($result1);

        $itemsData = $data1->response->data->items;
        if ($selectTranslation == 'Google MT with Edit') {
            $workflowUid = $itemsData['0']->workflowUid;
        } else if ($selectTranslation == 'Translation with Edit') {
            $workflowUid = $itemsData['2']->workflowUid;
        } else {
            $workflowUid = $itemsData['1']->workflowUid;
        }
        $this->Jobcreate($projectId, $userIdentifier, $userSecret, $Project_name, $token, $filemmmmae, $pathFileName, $workflowUid, $access_token, $language);

        $userConnectionModel->smartling_status = UserConnection::SMARTLING_ENABLED;
        $userConnectionModel->save(true, ['smartling_status']);
        $smartlingModel->connected = $enable;
        $smartlingModel->job_download_status = "pending";
        $smartlingModel->save(true, ['connected', 'job_download_status']);

        $elliotUser = User::findOne(['id' => $users_Id]);
        if (!empty($Elli_user)) {
            $elliotUser->smartling_status = User::SMARTLING_ACTIVE;
            $elliotUser->save(false);
        }

        echo 'You have initiated a translations job for ' . $namestore . ' in ' . $language . '.';
    }

    public function Jobcreate($projectId, $userIdentifier, $userSecret, $Project_name, $token, $filemmmmae, $pathFileName, $workflowUid, $access_token, $language) {
        $SmartlingProject = Smartling::find()->where(['project_id' => $projectId])->one();
        $project_id = $projectId;
        $sm_user_id = $userIdentifier;
        $secret_key = $userSecret;
        $project_name = $Project_name;
        $ds = DIRECTORY_SEPARATOR;
        $basedir = Yii::getAlias('@base');

        $options = [
            'project-id' => '7289bfb87',
            'user-id' => 'bhabaskqrnadgqgarxhuhawydiicml',
            'secret-key' => '2anhc34el124ngi7pd46epc21kVM_ui3vgsmu1usvlshgfnva3viu6j',
        ];

        if (!array_key_exists('project-id', $options) || !array_key_exists('user-id', $options) || !array_key_exists('secret-key', $options)
        ) {
            echo 'Missing required params.' . PHP_EOL;
            exit;
        }

        $autoloader = $basedir . '/example-jobs-api/vendor/autoload.php';

        if (!file_exists($autoloader) || !is_readable($autoloader)) {
            echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
            exit;
        } else {
            /** @noinspection UntrustedInclusionInspection */
            require_once $basedir . '/example-jobs-api/vendor/autoload.php';
        }

        $projectId = $project_id;
        $userIdentifier = $sm_user_id;
        $userSecretKey = $secret_key;
        $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

        $jobsAPI = JobsApi::create($authProvider, $projectId);
        $fileAPI = FileApi::create($authProvider, $projectId);

        //try {
            $localeId = $language;
            $fileUri = $pathFileName;
            $fileName = $filemmmmae;

            // Upload file.
            $translate_paths = [];
            $translate_paths[] = array("path"=> "/description");
            $translate_paths[] = array("path"=> "/name");
            $translate_paths[] = array("path"=> "/brand");

            $params = new UploadFileParameters();
            $params->set("smartling.translate_paths", json_encode($translate_paths));
            $params->set("smartling.string_format_paths", "html: /description");
            //$params->setAuthorized($authProvider);
            $rs = $fileAPI->uploadFile($fileUri, $fileName, 'json', $params);

            // Create a job without locales.
            $createParams = new \Smartling\Jobs\Params\CreateJobParameters();
            $createParams->setName($project_name . "--Job--" . time());
            $createParams->setDescription($project_name . "--Job--" . time());

            $job = $jobsAPI->createJob($createParams);

            $SmartlingProject->job_translationJobUid = $job['translationJobUid'];
            $SmartlingProject->job_jobName = $job['jobName'];
            $SmartlingProject->job_referenceNumber = $job['referenceNumber'];
            $SmartlingProject->job_targetLocaleIds = $language;
            $SmartlingProject->target_locale = $language;
            $SmartlingProject->job_callbackUrl = $filemmmmae;
            $SmartlingProject->job_callbackMethod = $job['callbackMethod'];
            $SmartlingProject->job_createdByUserUid = $job['createdByUserUid'];
            $SmartlingProject->job_jobStatus = $job['jobStatus'];
            $SmartlingProject->job_download_status = "pending";
            $SmartlingProject->save(true, ['job_translationJobUid', 'job_jobName', 'job_referenceNumber', 'job_targetLocaleIds', 'target_locale', 'job_callbackUrl', 'job_callbackMethod', 'job_createdByUserUid', 'job_jobStatus', 'job_download_status']);
            $translationJobUid = $job['translationJobUid'];
            $file_response_status = $this->customAddFileToJobSync($fileName, $localeId, $projectId, $translationJobUid, $access_token);

            $data = array(
                "localeWorkflows" => [array(
                    "targetLocaleId" => $localeId,
                    "workflowUid" => $workflowUid,
                )],
            );

            $data_string = json_encode($data);
            $curl = curl_init();
            //​jobs-api/v3/projects/{projectId}/jobs/{translationJobUid}/authorize
            $url = 'https://api.smartling.com/jobs-api/v3/projects/' . $projectId . '/jobs/' . $job['translationJobUid'] . '/authorize';
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
                    "Authorization: Bearer " . $access_token,
                    "content-type: application/json"
                ),
            ));

            $result1 = curl_exec($curl);

            $contentType = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Authorize job.
            //$jobsAPI->authorizeJob($job['translationJobUid']);

            $data_string = '{ "tags": ["test1", "test2"]}';
            $curl = curl_init();
            $url = 'https://api.smartling.com/estimates-api/v2/projects/' . $projectId . '/jobs/' . $job['translationJobUid'] . '/reports/fuzzy';
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
                    "Authorization: Bearer " . $access_token,
                    "content-type: application/json"
                ),
            ));

            $result1 = curl_exec($curl);
            $datareport = json_decode($result1);
            $reportUid = $datareport->response->data->reportUid;


            $SmartlingProject->project_type_value = $reportUid;
            $SmartlingProject->job_download_status = "pending";
            $SmartlingProject->save(true, ['project_type_value', 'job_download_status']);
//        } catch (SmartlingApiException $e) {
//            echo"catch exception";
//            echo'<pre>';
//            print_r($e);
//            die('catch end 1877');
//            // echo '<pre>'; print_r($e); echo '</pre>';
//        }
    }

    public static function customAddFileToJobSync($fileName, $localeId, $projectId, $translationJobUid, $access_token) {

        /* File add through job by curl for testing */

        $file_data = array(
            'fileUri' => $fileName,
            'targetLocaleIds' => [$localeId],
        );
        $file_data_encode = json_encode($file_data);
        $curl = curl_init();
        $url = 'https://api.smartling.com/jobs-api/v3/projects/' . $projectId . '/jobs/' . $translationJobUid . '/file/add';
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
            CURLOPT_POSTFIELDS => $file_data_encode,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "charset: utf-8",
                "Authorization: Bearer " . $access_token,
                "content-type: application/json"
            ),
        ));

        $result1 = curl_exec($curl);
        $result = json_decode($result1, true);
        $response=true;
        if(isset($result['response']['errors'])){
            $response=false;
            $error_msg=$result['response']['errors'][0]['message'];
            $explde_error_msg= explode(':', $error_msg);
            if(strtolower($explde_error_msg[0])== strtolower('File locked')){
                $response=false;
            }
        }

        if($response!=true){
            sleep(100);
            self::customAddFileToJobSync($fileName, $localeId, $projectId, $translationJobUid, $access_token);
        }
        //echo $response;
        return  $response;
    }

    public function actionJobsetting() {
        $token = $_POST['token'];
        $SmartlingProject = Smartling::find()->where(['token' => $token])->one();
        //echo '<pre>'; print_r($SmartlingProject); echo '</pre>';
        $project_id = $SmartlingProject->project_id;
        $sm_user_id = $SmartlingProject->sm_user_id;
        $secret_key = $SmartlingProject->secret_key;
        $project_name = $SmartlingProject->project_name;
        $ds = DIRECTORY_SEPARATOR;
        $basedir = Yii::getAlias('@basedir');
        ///echo '<pre>'; print_r($_POST); echo '</pre>';
        $options = [
            'project-id' => '7289bfb87',
            'user-id' => 'bhabaskqrnadgqgarxhuhawydiicml',
            'secret-key' => '2anhc34el124ngi7pd46epc21kVM_ui3vgsmu1usvlshgfnva3viu6j',
        ];

        if (!array_key_exists('project-id', $options) || !array_key_exists('user-id', $options) || !array_key_exists('secret-key', $options)
        ) {
            echo 'Missing required params.' . PHP_EOL;
            exit;
        }

        $autoloader = $basedir . '/example-jobs-api/vendor/autoload.php';

        if (!file_exists($autoloader) || !is_readable($autoloader)) {
            echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
            exit;
        } else {
            /** @noinspection UntrustedInclusionInspection */
            require_once $basedir . '/example-jobs-api/vendor/autoload.php';
        }

        $projectId = $project_id;
        $userIdentifier = $sm_user_id;
        $userSecretKey = $secret_key;
        $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

        /**
         * Recommended flow.
         *
         * 0. Upload needed file with a help of FileAPI.
         * 1. Create a job without locales.
         * 2. Add needed locales to a job.
         * 3. Attach file to a job.
         * 4. Authorize a job.
         * 5. Cancel job when it's needed.
         *
         * For more demos see jobs-sdk-php/example.php.
         */
        $jobsAPI = JobsApi::create($authProvider, $projectId);
        $fileAPI = FileApi::create($authProvider, $projectId);

        try {
            $localeId = 'zh-CN';
            $fileUri = 'file.xml';
            //  $fileName = 'file.xml';
            // Upload file.
            // $fileAPI->uploadFile($fileUri, $fileName, 'xml');
            // Create a job without locales.
            $createParams = new \Smartling\Jobs\Params\CreateJobParameters();
            $createParams->setName($project_name . "--Job--" . time());
            $createParams->setDescription($project_name . "--Job--" . time());
            //$createParams->setDueDate('Jan 02, 2020 12:49 AM');

            $job = $jobsAPI->createJob($createParams);


            // Add locale to a job.
            $addLocaleParams = new AddLocaleToJobParameters();
            $addLocaleParams->setSyncContent(false);
            $jobsAPI->addLocaleToJobSync($job['translationJobUid'], $localeId, $addLocaleParams);
            echo '<pre>';
            print_r($job);
            echo '</pre>';
            $SmartlingProject->job_translationJobUid = $job['translationJobUid'];
            $SmartlingProject->job_jobName = $job['jobName'];
            $SmartlingProject->job_referenceNumber = $job['referenceNumber'];
            $SmartlingProject->job_callbackUrl = $job['callbackUrl'];
            $SmartlingProject->job_callbackMethod = $job['callbackMethod'];
            $SmartlingProject->job_createdByUserUid = $job['createdByUserUid'];
            $SmartlingProject->job_jobStatus = $job['jobStatus'];
            $SmartlingProject->job_download_status = "pending";
            if ($SmartlingProject->save(false)) {
                echo 'success';
            } else {
                echo 'error';
            }
            // Attach uploaded file to a job.
            /*  $addFileParams = new AddFileToJobParameters();
              $addFileParams->setTargetLocales([$localeId]);
              $addFileParams->setFileUri($fileName);
              $jobsAPI->addFileToJobSync($job['translationJobUid'], $addFileParams); */

            // Authorize job.
            // $jobsAPI->authorizeJob($job['translationJobUid']);
            // Cancel job.
            /*  $cancelParams = new CancelJobParameters();
              $cancelParams->setReason('Some reason to cancel');
              $jobsAPI->cancelJobSync($job['translationJobUid'], $cancelParams); */
        } catch (SmartlingApiException $e) {

            var_dump($e);
        }
    }
}