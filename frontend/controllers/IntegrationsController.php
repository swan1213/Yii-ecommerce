<?php

namespace frontend\controllers;

use frontend\models\search\IntegrationSearch;
use Yii;
use common\models\Integrations;
use common\models\CurrencySymbol;
use common\models\Smartling;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\Country;
use common\models\UserConnection;
use common\models\Integration;
use common\models\UserIntegration;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\ProductAbbrivation;
use common\models\Products;
use common\models\UserConnectionDetails;
use frontend\components\ConsoleRunner;

/**
 * BusinesstoolsController implements the CRUD actions for Integrations model.
 */
class IntegrationsController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ['access' => [
                'class' => AccessControl::className(),
                'only' => ['netsuite','erplist','facebook','translation-all','pos-all','accounting', 'inventory','email','translations', 'square','smartling','Writefile','netsuiteupdate'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['netsuite', 'erplist','facebook','translation-all','pos-all','accounting','inventory','email','translations', 'square','smartling','Writefile','netsuiteupdate'],
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
     * Disable CSRF Validation
     * @param type $action
     * @return type
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Lists all Integrations models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new IntegrationsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * NetSuit Wizard
     */
    public function actionNetsuite() {

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            $user_id = Yii::$app->user->identity->id;
            if (isset($post)) {
                foreach ($post as $key => $data) {
                    if ($key == "app_url") {
                        $categoryModel = new Integrations();
                        $categoryModel->name = "NetSuite";
                        $categoryModel->user_id = $user_id;
                        $categoryModel->key_name = 'app_url';
                        $categoryModel->value = $data;
                        $created_date = date('Y-m-d H:i:s');
                        $categoryModel->created_at = $created_date;
                        //Save Elliot User id
                        $categoryModel->updated_at = $created_date;
                        $categoryModel->save(false);
                    }
                    if ($key == "email") {
                        $categoryModel = new Integrations();
                        $categoryModel->name = "NetSuite";
                        $categoryModel->user_id = $user_id;
                        $categoryModel->key_name = 'email';
                        $categoryModel->value = $data;
                        $created_date = date('Y-m-d H:i:s');
                        $categoryModel->created_at = $created_date;
                        //Save Elliot User id
                        $categoryModel->updated_at = $created_date;
                        $categoryModel->save(false);
                    }
                    if ($key == "password") {
                        $categoryModel = new Integrations();
                        $categoryModel->name = "NetSuite";
                        $categoryModel->user_id = $user_id;
                        $categoryModel->key_name = 'password';
                        $categoryModel->value = $data;
                        $created_date = date('Y-m-d H:i:s');
                        $categoryModel->created_at = $created_date;
                        //Save Elliot User id
                        $categoryModel->updated_at = $created_date;
                        $categoryModel->save(false);
                    }
                }
            }
        }
        $searchModel = new IntegrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('netsuite', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

		/**
     * NetSuit Wizard setting update
     */
	 public function actionNetsuiteupdateurl() {
		  $elliotresturl = $_POST['elliotresturl'];
		  $user_id = Yii::$app->user->identity->id;
		  $key = 'netsuite_elliot_url';
		  $keyvalue = $elliotresturl;
		  $Integrations = Integrations::find()->where(['user_id'=>$user_id,'key_name'=>$key])->one();
		   if(!empty($Integrations)){ 
		   $Integrations->name = 'NetSuite';
					$Integrations->key_name = $key;
					$Integrations->user_id = $user_id;
					$Integrations->value = $keyvalue;
					$Integrations->created_at = date('Y-m-d H:i:s');
					$Integrations->save();
		   } else{
			   $Integrations = new Integrations();
				$Integrations->name = 'NetSuite';
					$Integrations->key_name = $key;
					$Integrations->user_id = $user_id;
					$Integrations->value = $keyvalue;
					$Integrations->created_at = date('Y-m-d H:i:s');
					$Integrations->save();
		   }
		   return true;
	 }
	 public function actionNetsuiteupdate() {
		 //echo '<pre>'; print_r($_POST); echo '</pre>';
		 $compnayId = $_POST['netsuitecompanyid'];
		 $email = $_POST['netsuiteempemail'];
		 $password = $_POST['netsuitemppass'];
		 
		echo  $data = 'NLAuth nlauth_email='.$email.', nlauth_signature='.$password.', nlauth_account='.$compnayId;
		 
		 $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://rest.netsuite.com/rest/roles",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$data
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
                        echo '<pre>'; print_r($response); echo '</pre>'; die("uu");
						$response = json_decode($response);
		 //echo '<pre>'; print_r($response); echo '</pre>'; 
		 $nameofroleGet = '';
		 foreach($response as $resdata){
			 $role  = $resdata->role;
			  $nameofrole = $role->name;
			 $nameofroleGet .= '<option value ="'.$nameofrole.'">'.$nameofrole.'</option>';
		 }
		 $user_id = Yii::$app->user->identity->id;
		 for($i=0;$i<4;$i++){
					if($i == 0){
						$key = 'netsuite_compnay_id';
						$keyvalue = $compnayId;
					} else if($i == 1){
						$key = 'netsuite_email';
						$keyvalue = $email;
					}
					else if($i == 2){
						$key = 'netsuite_password';
						$keyvalue = $password;
					}
					else{
						$key = 'netsuite_role';
						$keyvalue = $nameofrole;
					}
					 $Integrations = Integrations::find()->where(['user_id'=>$user_id,'key_name'=>$key])->one();
					 if(!empty($Integrations)){
						 $Integrations = Integrations::find()->where(['user_id'=>$user_id,'key_name'=>$key])->one();
						 $Integrations->name = 'NetSuite';
					$Integrations->key_name = $key;
					$Integrations->user_id = $user_id;
					$Integrations->value = $keyvalue;
					$Integrations->created_at = date('Y-m-d H:i:s');
					$Integrations->save();
					 } else{
						 $Integrations = new Integrations();
						 $Integrations->name = 'NetSuite';
					$Integrations->key_name = $key;
					$Integrations->user_id = $user_id;
					$Integrations->value = $keyvalue;
					$Integrations->created_at = date('Y-m-d H:i:s');
					$Integrations->save();
					 }
					
					
					
						
				}
			 
			 
		
		 
		 
		 
		 return $nameofroleGet;
	 }
    /**
     * Displays a single Integrations model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Integrations model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Integrations();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Integrations model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Integrations model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Integrations model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Integrations the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Integrations::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionFacebook() {
        return $this->render('facebook_info');
    }

    public function actionCurrencies() {
        $currencies = CurrencySymbol::find()->asArray()->all();
        $final_array=[];
        if (isset($currencies) and ! empty($currencies)) {
            foreach ($currencies as $single) {
                $single_array['value']= strtoupper($single['name']);
                $single_array['text']= strtoupper($single['name']);
                $final_array[]=$single_array;
            }
        }
        echo json_encode($final_array);
        die;
    }
    
    public function actionTimezones() {
        $tzlist = \DateTimeZone::listIdentifiers();
        $timezones =array();
        if(isset($tzlist) and !empty($tzlist)) {
            foreach($tzlist as $key => $val) {
                $one['value'] =  $val;
                $one['text'] = $val;
                $timezones[] = $one;
            }
        }
        echo json_encode($timezones);
        die();
        
    }
    
    

    public function actionErplist() {
        return $this->render('erp');
    }

    public function actionTranslationAll() 
    {
        return $this->render('translation_all');
    }

    public function actionPosAll() {
        $user_id = Yii::$app->user->identity->id;
        $connection_rows = Connection::find()->where([
            'type_id' => 4
        ])->all();

        $pos_connections = [];

        if(!empty($connection_rows) and count($connection_rows)) {
            foreach ($connection_rows as $single_connection_instance) {
                $connection_detail = [
                    'connection_id' => $single_connection_instance->id,
                    'connection_parent_id' => $single_connection_instance->parent_id,
                    'name' => $single_connection_instance->name,
                    'status' => 'Get Connected',
                    'link' => '/'.strtolower(trim($single_connection_instance->name)).'?id='.$single_connection_instance->id
                ];

                if(!empty($single_connection_instance->userConnections) and count($single_connection_instance->userConnections) > 0) {
                    foreach ($single_connection_instance->userConnections as $single_user_connection) {
                        if(
                            !empty($single_user_connection) and
                            $single_user_connection->user_id == $user_id
                        ) {
                            if(
                                $single_user_connection->import_status == UserConnection::IMPORT_STATUS_PROCESSING or
                                $single_user_connection->connected == UserConnection::CONNECTED_YES
                            ) {
                                $connection_detail['status'] = 'Connected';
                            }
                        }   
                    }
                }

                $pos_connections[] = $connection_detail;
            }
        }

        return $this->render('pos_all', [
            'pos_connections' => $pos_connections
        ]);
    }

    public function actionAccounting() {

        return $this->render('accounting');
    }

    public function actionInventory() {
        return $this->render('inventory');
    }

    public function actionEmail() {

        return $this->render('email');
    }
	 public function actionSmartling() {
        return $this->render('smartling');
    }
	public function actionTranslationsave(){
		$connected = $_POST['smartlingyes'];
		$translation_type = $_POST['translation_type'];
		$user_id = Yii::$app->user->identity->id;
		$smartlingData = Smartling::find()->Where(['elliot_user_id' => $user_id])->one();
			if(!empty($smartlingData)){
					$smartlingData->elliot_user_id = $user_id;
					$smartlingData->connected = $connected;
					$smartlingData->translation_type = $translation_type;
					$smartlingData->created_at = date('Y-m-d H:i:s');
					$smartlingData->save(false);
				echo 'success';
			} else{
			
				$smartlingData = new Smartling();
				$smartlingData->elliot_user_id = $user_id;
				$smartlingData->connected = $connected;
				$smartlingData->translation_type = $translation_type;
				$smartlingData->created_at = date('Y-m-d H:i:s');
				$smartlingData->save(false);
				echo 'success';
		}
		
	}
	
	public function Writefile($storename,$file,$id){
		//echo $storename; echo '==='; echo $file; die('dsfas');
		//$user_id = Yii::$app->user->identity->id;
		$check_abbrivation =  ProductAbbrivation::find()->where(['channel_accquired' => $storename])->all();
		$i=0;
		$user_id = $id;
		$productArray = array();
		foreach($check_abbrivation as $data){
			
			 $id = $data['id'];
			$productData = Products::find()->where(['id' => $id])->one();
		//	echo '<pre>'; print_r($productData); echo '</pre>'; 
			$id = $id ;
			  $name = @$productData->product_name;
			$description = @$productData->description;
			$condition = @$productData->condition;
			$gender = @$productData->gender;
			$stock_level = @$productData->stock_level;
			$product_status = @$productData->product_status;
			$productArray[$i]['id'] = $id;
			$productArray[$i]['name'] = $name;
			$productArray[$i]['description'] = $description;
			$productArray[$i]['condition'] = $condition;
			$productArray[$i]['gender'] = $gender;
			$productArray[$i]['stock_level'] = $stock_level;
			$productArray[$i]['product_status'] = $product_status; 
			$i++;
			
		}
		//echo '<pre>'; print_r($productArray); echo '</pre>';
		$jsonProductData = json_encode($productArray);
		$fileName = $file.'id'.$user_id.'.json';
		$pathfile = '/var/www/html/img/uploaded_files/'.$fileName;
		$myfile = fopen($pathfile, "w");
		if(file_exists($pathfile)){
			fwrite($myfile, $jsonProductData);
		}
		else {
			fwrite($pathfile, $jsonProductData);
		}
		self::Translationstore($pathfile,$fileName);
		//die('asd11111111fasd');
	}
	public function Translationstore($filePath,$translate_filename){
		
	$ds = DIRECTORY_SEPARATOR;
        $basedir = Yii::getAlias('@basedir');
			
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

				$autoloader = $basedir.'/smartling-api/vendor/autoload.php';

				if (!file_exists($autoloader) || !is_readable($autoloader)) {
				  echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
				  exit;
				}
				else {
				  require_once $basedir.'/smartling-api/vendor/autoload.php';
				}
				
				

				$projectId = $options['project-id'];
				$userIdentifier = $options['user-id'];
				$userSecretKey = $options['secret-key'];

				$fileName = $translate_filename;
				$fileUri = $filePath;
				$fileRealPath = realpath($fileUri);
				$fileType = 'json';
				$newFileName = 'new_test_file.json';
				$retrievalType = 'published';
				$content = file_get_contents(realpath($fileUri));
				$fileContentUri = $translate_filename;
				$translationState = 'PUBLISHED';
				$locale = 'zh-CN';
				$locales_array = [$locale];

				self::resetFiles($userIdentifier, $userSecretKey, $projectId, [ $fileName, $newFileName]);
			
									
					/**
					 * Upload file example
					 */
					try { 
					  //echo '::: File Upload Example :::' . PHP_EOL;
					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $result = $fileApi->uploadFile($fileRealPath, $fileName, $fileType);

					 // echo 'File upload result:' . PHP_EOL;
					  echo var_export($result, true);
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while uploading file.';

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
					/* try {
					  echo '::: File Download Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $params = new \Smartling\File\Params\DownloadFileParameters();
					  $params->setRetrievalType($retrievalType);

					  $result = $fileApi->downloadFile('product1.xml', 'zh-CN', $params);

					  echo 'File download result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while downloading file.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */

					/**
					 * Getting file status example
					 */
					/* try {
					  echo '::: Get File Status Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $result = $fileApi->getStatus($fileName, $locale);

					  echo 'Get File Status result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while getting file status.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */

					/**
					 * Getting Authorized locales for file
					 */
					/* try {
					  echo '::: Get File Authorized Locales Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $result = $fileApi->getAuthorizedLocales($fileName);

					  echo 'Get File Authorized Locales result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while getting file authorized locales.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */

					/**
					 * Listing Files
					 */
					/* try {
					  echo '::: List Files Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $params = new \Smartling\File\Params\ListFilesParameters();
					  $params
						  ->setFileTypes($fileType)
						  ->setUriMask('test')
						  ->setLimit(5);

					  $result = $fileApi->getList($params);

					  echo 'List Files result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while getting file list.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */

					/**
					 * Importing Files
					 */
					/* try {
					  echo '::: File Import Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $result = $fileApi->import($locale, $fileName, $fileType, $fileRealPath, $translationState, true);

					  echo 'File Import result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while importing file.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */

					/**
					 * Renaming Files
					 */
					/* try {
					  echo '::: Rename File Example :::' . PHP_EOL;

					  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

					  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

					  $result = $fileApi->renameFile($fileName, $newFileName);

					  echo 'Rename File result:' . PHP_EOL;
					  echo var_export($result, true) . PHP_EOL . PHP_EOL;
					}
					catch (\Smartling\Exceptions\SmartlingApiException $e) {
					  $messageTemplate = 'Error happened while renaming files.' . PHP_EOL
						  . 'Response code: %s' . PHP_EOL
						  . 'Response message: %s' . PHP_EOL;

					  echo vsprintf(
						  $messageTemplate, [
						$e->getCode(),
						$e->getMessage(),
						  ]
					  );
					} */
			
			
			}
			function resetFiles($userIdentifier, $userSecretKey, $projectId, $files = []) {
		  $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

		  foreach ($files as $file) {
			try {
			  $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
			  $fileApi->deleteFile($file);
			}
			catch (\Smartling\Exceptions\SmartlingApiException $e) {
			  
			}
		  }
		}

}
