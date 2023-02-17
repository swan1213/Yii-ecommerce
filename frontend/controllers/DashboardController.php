<?php

namespace frontend\controllers;

use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\UserConnection;
use common\models\UserPermission;
use common\models\UserProfile;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\components\BaseController;
use Stripe\Stripe;
use Stripe\Plan;
use common\models\User;
use common\models\Notification;
use common\models\CurrencySymbol;
use common\models\Country;
use common\models\State;
use common\models\City;
use common\models\UserConnectionDetails;
use common\models\Smartling;
use common\models\Customer;
use common\models\CustomerAddress;
use common\models\OrderProduct;
use common\models\Product;
use common\models\TrialPeriod;
use yii\db\ActiveQuery;
use Bigcommerce\Api\Client as Bigcommerce;
use common\models\BillingInvoice;

use yii\web\Session;
use yii\helpers\ArrayHelper;

/**
 * Dashboard controller
 */
class DashboardController extends BaseController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'error', 'order', 'cron-account-status',
                    'export-user', 'general', 'destroysession', 'get-countries', 'get-states', 'get-cities',
                    'save-cover-image', 'save-general-info', 'get-languages',
                    'get-timezone', 'translations', 'request-password-reset',
                    'request-password-reset-token', 'save-smartling-api', 'integrate-transaltion',
                    'clear-notification', 'dashboard-graph', 'dashboard-graph-orders', 'ship-station',
                    'testoauth', 'createhook', 'dashboard-graph-product-sold', 'dashboard-graph-average-order-value',
                    'help-page', 'system-updates', 'system-status', 'terms-conditions', 'corporate-documents',
                    'upload-documents', 'newdashboard', 'areachartondashboard', 'import-status'],
                'rules' => [
                    [
                        'actions' => ['testoauth', 'testload', 'createhook'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'error', 'order', 'cron-account-status',
                            'export-user', 'general', 'destroysession', 'get-countries', 'get-states', 'get-cities',
                            'save-cover-image', 'save-general-info', 'get-languages', 'get-timezone',
                            'translations', 'save-smartling-api', 'integrate-transaltion',
                            'clear-notification', 'dashboard-graph', 'dashboard-graph-orders', 'ship-station', 'testoauth',
                            'dashboard-graph-product-sold', 'dashboard-graph-average-order-value', 'help-page', 'system-updates',
                            'system-status', 'corporate-documents', 'terms-conditions', 'upload-documents', 'newdashboard',
                            'areachartondashboard', 'import-status'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {

        $user = Yii::$app->user->identity;
        $user_id = $user->id;
        $currency = $user->currency;
        $currency = strtolower($currency);
        $symbol = CurrencySymbol::find()->select(['symbol'])->where(['name' => $currency])->one();

        $products_by_volume = OrderProduct::find()
            ->select(['sum(qty) AS qty', 'product_id'])
            ->where(['user_id' => $user_id])
            //->andFilterWhere(['between', 'date(created_at)', $previous_date, $current_date])
            ->groupBy(['product_id'])
            ->orderBy(['qty' => SORT_DESC])
            ->with([
                'product' => function(ActiveQuery $query) {
                    $query->where(['<>', 'status', Product::STATUS_INACTIVE]);
                    $query->select('id, price, name');
                },
                'product.productImages' => function(ActiveQuery $query) {
                    $query->select('product_id, link');
                }])
            ->asArray()
            ->all();

        $products_by_revenue = OrderProduct::find()
            ->select(['sum(price) AS price', 'product_id'])
            ->where(['user_id' => $user_id])
            //->andFilterWhere(['between', 'date(created_at)', $previous_date, $current_date])
            ->groupBy(['product_id'])
            ->orderBy(['price' => SORT_DESC])
            ->with(['product' => function(ActiveQuery $query) {
                $query->where(['<>', 'status', Product::STATUS_INACTIVE]);
                $query->select('id, price, name, country_code');
            },
                'product.productImages' => function(ActiveQuery $query) {
                    $query->select('product_id, link');
                }])
            ->asArray()
            ->all();

        $countries = UserConnectionDetails::find()->select(['user_connection_id', 'country_code', 'country'])->distinct()->asArray()->all();
        $countries = ArrayHelper::map($countries, 'country_code', 'country');

        $products_by_revenue_count = 0;
        if (isset($products_by_revenue) and ! empty($products_by_revenue)) {
            foreach ($products_by_revenue as $single_revenue) {
                if (isset($single_revenue->product) and ! empty($single_revenue->product)) {
                    $products_by_revenue_count++;
                }
            }
        }
        $products_by_volume_count = 0;
        if (isset($products_by_volume) and ! empty($products_by_volume)) {
            foreach ($products_by_volume as $single_volume) {
                if (isset($single_volume->product) and ! empty($single_volume->product)) {
                    $products_by_volume_count++;
                }
            }
        }

        $enabledStoreConnections = Connection::find()
            ->where(['type_id' => Connection::CONNECTION_TYPE_STORE, 'enabled' => Connection::CONNECTED_ENABLED_YES])
            ->orderBy(['name' => SORT_ASC])->all();

        return $this->render('index', [
                'products_by_volume' => $products_by_volume,
                'products_by_revenue' => $products_by_revenue,
                'products_by_volume_count' => $products_by_volume_count,
                'products_by_revenue_count' => $products_by_revenue_count,
                'countries' => $countries,
                'user' => $user,
                'symbol' => $symbol->symbol,
                'enabledStores' => $enabledStoreConnections,
            ]
        );
    }



    /**
     * Displays Genral Page.
     *
     * @return string
     */
    public function actionHelpPage() {
        return $this->render('help');
    }


    public function actionTermsConditions() {
        return $this->render('termscondition');
    }

    /* For Important Documents */

    public function actionCorporateDocuments() {
        return $this->render('importdocument');
    }

    public function actionUploadDocuments() {
        $user_id = Yii::$app->user->identity->id;
        $ds = DIRECTORY_SEPARATOR;
        $basedir = Yii::getAlias('@basedir');

        $maxsize = 26214400;
        /* For validation */
        $tax_val = $_POST['tax_id'];
        $channel_name = $_POST['channel_name'];
        $buisness_file = $_FILES['file-2'];
        $incorporation_file = $_FILES['file-3'];

        $array_msg = array();
        $error_msg = '';
        $validate = TRUE;
        if ($tax_val == '' || $tax_val == "Empty"):
            $error_msg .= '<li>Please Fill Your Tax ID</li>';
            $validate = FALSE;
        endif;
        if ($channel_name == ''):
            $error_msg .= '<li>Please Fill Your Channel Name</li>';
            $validate = FALSE;
        endif;

        /* Buisness_file Type check */
        $buisnees_allowed = array('pdf', 'jpg', 'png');
        $buisnees_filename = $buisness_file['name'];
        $buisnees_ext = pathinfo($buisnees_filename, PATHINFO_EXTENSION);
        if ($buisnees_filename == "") {
            $error_msg .= '<li>Please Upload Business License</li>';
            $validate = FALSE;
        } else {
            if (!in_array($buisnees_ext, $buisnees_allowed)) {
                $error_msg .= '<li>Extension is not valid</li>';
                $validate = FALSE;
            }
            if (($buisness_file['size'] >= $maxsize) || ($buisness_file["size"] == 0)) {
                $error_msg .= '<li>File too large. File must be less than 25 MB.</li>';
                $validate = FALSE;
            }
        }
        /* End  Buisness_file Type check */

        /* incorporation_file Type check */
        $incorporation_allowed = array('pdf', 'jpg', 'png');
        $incorporation_filename = $incorporation_file['name'];
        $incorporation_filename_ext = pathinfo($incorporation_filename, PATHINFO_EXTENSION);
        if ($incorporation_filename == "") {
            $error_msg .= '<li>Please Upload Business Incorporation Papers</li>';
            $validate = FALSE;
        } else {
            if (!in_array($incorporation_filename_ext, $incorporation_allowed)) {
                $error_msg .= '<li>Extension is not valid</li>';
                $validate = FALSE;
            }
            if (($incorporation_file['size'] >= $maxsize) || ($incorporation_file["size"] == 0)) {
                $error_msg .= '<li>File too large. File must be less than 25 MB.</li>';
                $validate = FALSE;
            }
        }
        if ($validate == FALSE) {
            $array_msg['error_msg'] = "<ul>" . $error_msg . "</ul>";
            return json_encode($array_msg);
        }
        /* If validation is true then upload document and save value in database */
        if ($validate == TRUE) {
            /* Base path of corporate Documents */
            $upload_docs_dir = Yii::getAlias('@important_documents');

            /* Starts buisness_file Document Upload */
            $buisness_tempFile = $buisness_file['tmp_name'];
            $buisness_file_name = $buisness_file['name'];
            $buisness_final_name = uniqid() . '_' . $buisness_file_name;
            $buisness_targetFile = $upload_docs_dir . $ds . $buisness_final_name;
            $buisness_file_upload = move_uploaded_file($buisness_tempFile, $buisness_targetFile);
            /* End Starts buisness_file Document Upload */

            /* Starts buisness_file Document Upload */
            $incorporation_file_tempFile = $incorporation_file['tmp_name'];
            $incorporation_file_name = $incorporation_file['name'];
            $incorporation_final_name = uniqid() . '_' . $incorporation_file_name;
            $incorporation_targetFile = $upload_docs_dir . $ds . $incorporation_final_name;
            $incorporation_file_upload = move_uploaded_file($incorporation_file_tempFile, $incorporation_targetFile);
            /* End Starts buisness_file Document Upload */

            /* Save Value in corporate Documents */
            $corporate_data = CorporateDocuments::find()->Where(['user_id' => $user_id, 'channel' => $channel_name])->one();
            if (!empty($corporate_data)):

                $file_delete1 = unlink($basedir . '/' . $corporate_data->Business_License);
                $file_delete2 = unlink($basedir . '/' . $corporate_data->Business_Papers);

                $corporate_data->Business_License = 'img/important_documents/' . $buisness_final_name;
                $corporate_data->Business_Papers = 'img/important_documents/' . $incorporation_final_name;
                $corporate_data->channel = $channel_name;
                $corporate_data->Tax_ID = $tax_val;
                $corporate_data->save(false);

            else:
                $CorporateDocuments = new CorporateDocuments();
                $CorporateDocuments->user_id = Yii::$app->user->identity->id;
                $CorporateDocuments->Business_License = 'img/important_documents/' . $buisness_final_name;
                $CorporateDocuments->Business_Papers = 'img/important_documents/' . $incorporation_final_name;
                $CorporateDocuments->channel = $channel_name;
                $CorporateDocuments->Tax_ID = $tax_val;
                $CorporateDocuments->created_at = date('Y-m-d h:i:s', time());
                $CorporateDocuments->save(false);
            endif;

            $array_msg['success_msg'] = "Document Upload SuccesFully";
            return json_encode($array_msg);
        }
    }

    /**
     * Displays Translations Page.
     *
     * @return string
     */
    public function actionTranslations() {
        return $this->render('translations');
    }

    /**
     * Displays Orders Page.
     *
     * @return string
     */
    public function actionOrder() {
        return $this->render('order');
    }

    /**
     * Subscription payment action.
     *
     * @return string
     */
    public function actionSubscriptionpay() {
        /* get current user login id */
        $user_id = '1983';
        $user_domain = 'smart61';
        //use for base directory//

        $customer_token = 'cus_BY5DiKgNeZr2VE';

        echo $invoice_name = $user_id . '_' . $user_domain . '_' . $customer_token;

        //   $name = $request->post('name');
        $basedir = Yii::getAlias('@basedir');
        //use for baseurl//
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        try {
            // Charge the Customer instead of the card:
            $charge = \Stripe\Charge::create(array(
                "amount" => 1000,
                "currency" => "usd",
                "customer" => $customer_token
            ));
            echo $stripe_payment_Status = $charge->status;
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function actionSubscriptionpaymentcustom() {
        $user_id = Yii::$app->user->identity->id;
        $user_domain = Yii::$app->user->identity->domain;
        Stripe::setApiKey(env('STRIPE_API_KEY'));

        $total_value_cost = $_POST['total_value_cost'];
        $amount = $total_value_cost*100;
        $price_per_word = $_POST['price_per_word'];
        $total_word_count = $_POST['total_word_count'];
        $user_connection_id = $_POST['user_connection_id'];

        //Payment via customer token
        if(array_key_exists('customer_token', $_POST)){
            $customer_token = Yii::$app->user->identity->access_token;
            $user_email = Yii::$app->user->identity->email;
            $invoice_name = $user_id . '_' . $user_domain . '_' . $customer_token;
            if($customer_token!=''){
                $charge = \Stripe\Charge::create(array(
                    "amount" => $amount,
                    "currency" => "usd",
                    "customer" => $user_id
                ));
                $stripe_payment_Status = $charge->status;
                $stripe_plan_id = $charge->id;
                if ($stripe_payment_Status == 'succeeded') {
                    /* Starts Save Billing Invoice */
                    $billing_invoice_model = new BillingInvoice();
                    $billing_invoice_model->user_id = $user_id;
                    $billing_invoice_model->stripe_id = $stripe_plan_id;
                    $billing_invoice_model->total_word_count = $total_word_count;
                    $billing_invoice_model->pric = $price_per_word;
                    $billing_invoice_model->customer_email = $user_email;
                    $billing_invoice_model->invoice_name = $invoice_name;
                    $billing_invoice_model->user_connection_id = $user_connection_id;
                    $billing_invoice_model->amount = $amount / 100;
                    $billing_invoice_model->status = $stripe_payment_Status;
                    $billing_invoice_model->created_at = date('Y-m-d h:i:s', time());
                    $billing_invoice_model->save(false);
                }
                return $stripe_payment_Status;
            }
        }
        else{
            // Payment via New Customer
            try {
                $creditemail = $_POST['creditemail'];
                $creditcart = $_POST['creditcart'];
                $creditdate = $_POST['creditdate'];
                $monyear = explode('/', $creditdate);
                $creditcvc = $_POST['creditcvc'];

                $token = \Stripe\Token::create(array(
                    "card" => array(
                        "number" => $creditcart,
                        "exp_month" => $monyear[0],
                        "exp_year" => $monyear[1],
                        "cvc" => $creditcvc
                    )
                ));
                $customer = \Stripe\Customer::create(array(
                    'email' => $creditemail,
                    'source' => $token->id,
                    'customer' => $user_domain . '_' . $user_id,
                    "metadata" => array("order_id" => $user_id)
                ));

                $customer_token = $customer->id;

                //Charge Payment
                $charge = \Stripe\Charge::create(array(
                    "amount" => $amount, // amount in cents, again
                    "currency" => "usd",
                    "customer" => $customer->id,
                    "description" => $user_domain . '_' . $user_id,
                    "metadata" => array("order_id" => $user_id)
                ));
                $stripe_plan_id = $charge->id;
                $invoice_name = $user_id . '_' . $user_domain . '_' . $customer_token;
                $stripe_payment_Status = $charge->status;
                if($stripe_payment_Status = 'succeeded'){
                    $billing_invoice_model = new BillingInvoice();
                    $billing_invoice_model->user_id = $user_id;
                    $billing_invoice_model->stripe_id = $stripe_plan_id;
                    $billing_invoice_model->total_word_count = $total_word_count;
                    $billing_invoice_model->price_per_word = $price_per_word;
                    $billing_invoice_model->customer_email = $creditemail;
                    $billing_invoice_model->invoice_name = $invoice_name;
                    $billing_invoice_model->user_connection_id = $user_connection_id;
                    $billing_invoice_model->amount = $amount / 100;
                    $billing_invoice_model->status = $stripe_payment_Status;
                    $billing_invoice_model->created_at = date('Y-m-d h:i:s', time());
                    $billing_invoice_model->save(false);
                }


                return $stripe_payment_Status = $charge->status;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        //Create Customer
    }

    /**
     * Cron for checking User Account Status.
     */
    public function actionCronAccountStatus() {

        /* get All users data */
        $users_data = User::find()->All();
        foreach ($users_data as $user) {

            $user_id = $user->id;

            $user_create_date = $user->created_at;

            $trial_days_data = TrialPeriod::find()->one();
            $trial_period_days = $trial_days_data->trial_days;

            /* convert trial period days to hour */
            $trial_days_hours = $trial_period_days * 24;

            /* get current date */
            $current_date = date("Y-m-d h:i:s");

            /* Date Diff */
            $diff = ((strtotime($current_date) - strtotime($user_create_date)) / 3600);

            /* Update User Trail Period Status */
            if ($diff > $trial_days_hours) {
                $user->trial_period_status = 'deactivate';
                $user->save(false);
                echo 'User Id : ' . $user_id . ' - Trail Period has been expired.<br>';
            }
        }
    }


    /**
     * Save General setting account details through ajax.
     *
     * @return string
     */
    public function actionSaveGeneralInfo() {

        $request = Yii::$app->request;
        $userid = Yii::$app->user->identity->id;
        if (Yii::$app->request->post()) {
            $user = User::find()->Where(['id' => $userid])->one();
            $user->email = Yii::$app->request->post('AccountOwner');
            $user->company = Yii::$app->request->post('general_company');
            //$user->currency = Yii::$app->request->post('currency');

            $userdata = UserProfile::find()->Where(['user_id' => $userid])->one();
            //$userdata->email = Yii::$app->request->post('AccountOwner');
            $userdata->corporate_addr_street1 = Yii::$app->request->post('general_corporate_street1');
            $userdata->corporate_addr_street2 = Yii::$app->request->post('general_corporate_street2');
            $userdata->phoneno = Yii::$app->request->post('general_phone_number');
            //$userdata->general_company = Yii::$app->request->post('general_company');
            $userdata->corporate_addr_country = Yii::$app->request->post('general_corporate_country');
            $userdata->corporate_addr_state = Yii::$app->request->post('general_corporate_state');
            $userdata->corporate_addr_city = Yii::$app->request->post('general_corporate_city');
            $userdata->corporate_addr_zipcode = Yii::$app->request->post('general_corporate_zip');
            $userdata->billing_addr_street1 = Yii::$app->request->post('subscription_billing_street1');
            $userdata->billing_addr_street2 = Yii::$app->request->post('subscription_billing_street2');
            $userdata->billing_addr_country = Yii::$app->request->post('subscription_billing_country');
            $userdata->billing_addr_state = Yii::$app->request->post('subscription_billing_state');
            $userdata->billing_addr_city = Yii::$app->request->post('subscription_billing_city');
            $userdata->billing_addr_zipcode = Yii::$app->request->post('subscription_billing_zip');
            $userdata->tax_rate = Yii::$app->request->post('general_TaxRate');
            $userdata->language = Yii::$app->request->post('general_Language');
            $userdata->weight_preference = Yii::$app->request->post('general_WeightPreference');
            $userdata->timezone = Yii::$app->request->post('general_Timezone');

            if ($userdata->save(false)) {
                $response = ['status' => 'success', 'data' => 'General info saved'];
                Yii::$app->session->setFlash('success', 'Success! General info has been updated.');
            } else {
                $response = ['status' => 'error', 'data' => 'profile info not saved'];
                Yii::$app->session->setFlash('danger', 'Error! General info has not been updated.');
            }
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Save General setting account details through ajax.
     *
     * @return string
     */


    public function actionIntegrateTransaltion() {

        return $this->render('smartlingTranslate');
    }

    /**
     * Export MErchant user Action.
     *
     * @return string
     */
    public function actionExportUser() {

        $user_id = Yii::$app->user->identity->id;
        $merchant_user = User::find()->where(['parent_id' => $user_id, 'role' => User::role_merchant_user])->all();

        $html = '<table>
                <tr>
                     <th>Name</th>
                     <th>User Role</th>
                     <th>Date Created</th>
                     <th>Date Last Logged In</th>
                  </tr>';

        foreach ($merchant_user as $users_data) {

            $html .= '<tr>';
            $html .= '<td>';
            $html .= $users_data->first_name . '' . $users_data->last_name;
            $html .= '</td>';


            $html .= '<td>';
            $html .= $users_data->role;
            $html .= '</td>';

            $html .= '<td>';
            $html .= $users_data->created_at;
            $html .= '</td>';

            $html .= '<td>';
            $html .= $users_data->date_last_login;
            $html .= '</td>';
        }

        $html .= '</table>';


        $file = 'Users_Reports-' . date('d/m/Y') . '.xls';
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$file");
        echo $html;
    }

    /**
     * Destroy a session.
     */
    public function actionDestroysession() {

        $session = Yii::$app->session;
        // destroys all data registered to a session.
        $session->destroy();
    }

    /* Action for Get Countries */

    public function actionGetCountries() {
        $query = $_REQUEST['query'];
        $sql = "SELECT name FROM countries WHERE name LIKE '{$query}%'";
        $countries = Country::findBySql($sql)->all();
        $cat_array = array();
        foreach ($countries as $country_name) {
            $country_array[] = $country_name->name;
        }
        //RETURN JSON ARRAY
        return json_encode($country_array);
    }

    /* ACTION for Get States */

    public function actionGetStates() {
        $query = $_REQUEST['query'];
        $sql = "SELECT name FROM states WHERE name LIKE '{$query}%'";
        $states = Country::findBySql($sql)->all();
        $cat_array = array();
        foreach ($states as $states_name) {
            $states_array[] = $states_name->name;
        }
        //RETURN JSON ARRAY
        return json_encode($states_array);
    }

    /* Action for Get States */

    public function actionGetCities() {
        $query = $_REQUEST['query'];
        $sql = "SELECT name FROM cities WHERE name LIKE '{$query}%'";
        $cities = Country::findBySql($sql)->all();
        $cat_array = array();
        foreach ($cities as $cities_name) {
            $cities_array[] = $cities_name->name;
        }
        //RETURN JSON ARRAY
        return json_encode($cities_array);
    }

    /* Action for Get languages */

    public function actionGetLanguages() {
        $query = $_REQUEST['query'];
        $sql = "SELECT name FROM languages WHERE name LIKE '{$query}%'";
        $languages = Languages::findBySql($sql)->all();
        $cat_array = array();
        foreach ($languages as $languages_name) {
            $languages_array[] = $languages_name->name;
        }
        //RETURN JSON ARRAY
        return json_encode($languages_array);
    }

    /* Action for Get Timezone */

    public function actionGetTimezone() {
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        //$a="{'M': 'male', 'F': 'female'}";
        echo json_encode($zones_array);
    }

    /* save cover iamge action */

    public function actionSaveCoverImage() {
        $basedir = Yii::getAlias('@basedir');
        $basepath_cover_images = Yii::getAlias('@cover_images');
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = $basepath_cover_images;
        $tempFile = $_FILES['file']['tmp_name'];
        $imageName = $_FILES['file']['name'];
        $RandomImageId = uniqid();
        //Generate Unique Name for each Image Uploaded
        $new_imageName = $RandomImageId . '_' . $imageName;
        //Path Setting
        $targetPath = $storeFolder . $ds;
        $targetFile = $targetPath . $new_imageName;
        //Save the Uploaded File
        $imageFile = move_uploaded_file($tempFile, $targetFile);

        //Image Thumbnail
        //Image::thumbnail(Yii::getAlias('@cover_images/' . $new_imageName), 300, 300)->save(Yii::getAlias('@cover_images/thumbnails/thumb_' . $new_imageName), ['quality' => 100]);

        //Temp entry into Product_images Table
        $userid = Yii::$app->user->identity->id;
        $userdata = User::find()->Where(['id' => $userid])->one();
        $userdata->cover_img = $new_imageName;
        if ($userdata->save(false)) {
            $response = ['status' => 'success', 'data' => 'Cover image has been changed successfully'];
            Yii::$app->session->setFlash('success', 'Success! Cover image has been updated.');
        } else {
            $response = ['status' => 'success', 'data' => 'Failed to update Cover Image'];
            Yii::$app->session->setFlash('danger', 'Error! Failed to update Cover Image.');
        }
        echo json_encode($response);
        exit;
    }


    //Action Show graph by order base
    public function actionDashboardGraphOrders() {

        $requestData = Yii::$app->request->post();
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }

        $products_count = count(Product::find()->where(['permanent_hidden' => Product::PRODUCT_PERMANENT_NO])->all());

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if ((!empty($filtered_connection_id))) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $order_ids_query = implode(",", $order_ids);
        /* Show Data According to Month */
        $whole_revenue_amount = 0;
        $new_arr = array();
        if (Yii::$app->request->post('data') == 'month') :

            $current_day = date('d');
            $current_m = date('Y-m');
            $currentmonth = date('m');
            $currentyear = date('Y');
            $count_orders_data = Order::find()->andWhere(['user_id' => $user_id])->andWhere(['=', 'month(created_at)', $currentmonth])
                ->andWhere(['=', 'year(created_at)', $currentyear])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $count_orders_data = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'month(order_date)', $currentmonth])
                    ->andWhere(['=', 'year(order_date)', $currentyear])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            for ($j = 0; $j <= $current_day; $j++) {
                $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                $check_month = date('Y-m', strtotime($previous_date));
                //get previous date
                if ($current_m == $check_month) {
                    $orders_data_current = Order::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'date(order_date)', $previous_date])
                        ->andWhere(['=', 'month(order_date)', $currentmonth])
                        ->$andWhere([
                            'and',
                            ['in', 'id', $order_ids],
                        ])
                        ->asArray()
                        ->all();
                    $single_count = 0;
                    if (isset($orders_data_current) and ! empty($orders_data_current)) {
                        foreach ($orders_data_current as $single) {
                            $single_count += $single['total_amount'];
                        }
                    }
                    $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                    $whole_revenue_amount += $single_count;
                }
            }

            //for cornerdata percent
            if ($count_orders_data != 0 && !empty($count_orders_data)):
                $Value = $count_orders_data * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');

            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        endif;

        /* Show Data According to Week */
        if (Yii::$app->request->Post('data') == 'week'):
            //current date
            $current_date = date('Y-m-d', time());
            //6 days ago date
            $first_monday_date = date('Y-m-d', strtotime('this week last monday', strtotime($current_date)));
            $count_orders_data = count(Order::find()->Where(['between', 'order_date', $first_monday_date, $current_date])->$andWhere([
                'and',
                ['in', 'id', $order_ids],
            ])->all());

            for ($j = 0; $j <= 6; $j++) {
                //get previous date
                $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                if ($previous_date < $first_monday_date)
                    break;

                $orders_data_current = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'date(order_date)', $previous_date])
                    ->$andWhere([
                        'and',
                        ['in', 'id', $order_ids],
                    ])
                    ->asArray()
                    ->all();

                $single_count = 0;
                if (isset($orders_data_current) and ! empty($orders_data_current)) {
                    foreach ($orders_data_current as $single) {
                        $single_count += $single['total_amount'];
                    }
                }

                $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                $whole_revenue_amount += $single_count;
            }
            $new_arr = array_reverse($new_arr);
            //for cornerdata percent
            if (!empty($count_orders_data)):
                $Value = $count_orders_data * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        endif;


        /* Show Data According to Today */
        if (Yii::$app->request->post('data') == 'today'):
            //get orders data acc to channel
            $current_date = date('Y-m-d', time());
            $j = 0;
            $new_arr = array();
            for ($i = 1; $i <= 24; $i++) {

                if ($i == 1) {
                    $current_date_hour = date('Y-m-d h:i:s', time());
                } else {
                    $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                }
                $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                $date_check = date('Y-m-d', strtotime($previous_hour));
                if ($date_check == $current_date) {
                    $orders_data_current = Order::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['between', 'order_date', $previous_hour, $current_date_hour])
                        ->$andWhere([
                            'and',
                            ['in', 'id', $order_ids],
                        ])
                        ->asArray()
                        ->all();
                    $single_count = 0;
                    if (isset($orders_data_current) and ! empty($orders_data_current)) {
                        foreach ($orders_data_current as $single) {
                            $single_count += $single['total_amount'];
                        }
                    }
                    $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                    $whole_revenue_amount += $single_count;
                }
                $j++;
            }
            //for data count at the corner of the graph
            $current_date_today = date('Y-m-d', time());
            $count_orders_data = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'date(order_date)', $current_date_today])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $count_orders_data = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'date(order_date)', $current_date_today])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            if ($count_orders_data != 0 && !empty($count_orders_data)):
                $Value = $count_orders_data * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            $i++;
            return json_encode($new_array);
        endif;
        /* Show Data According to Year */
        if (Yii::$app->request->post('data') == 'year'):

            $current_d = date('m');
            $current_y = date('Y');
            $month = (int) $current_d;
            for ($month; $month >= 1; $month--) {

                $orders_data_current = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'month(order_date)', $month])
                    ->andWhere(['=', 'year(order_date)', $current_y])
                    ->$andWhere([
                        'and',
                        ['in', 'id', $order_ids],
                    ])
                    ->asArray()
                    ->all();
                $single_count = 0;
                if (isset($orders_data_current) and ! empty($orders_data_current)) {
                    foreach ($orders_data_current as $single) {
                        $single_count += $single['total_amount'];
                    }
                }
                $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                $whole_revenue_amount += $single_count;
            }

            $orders_count = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'YEAR(order_date)', $current_y])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $orders_count = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'YEAR(order_date)', $current_y])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            if ($orders_count != 0 && !empty($orders_count)):
                $Value = $orders_count * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'annual'):

            $current_y = date('Y');
            for ($i = 0; $i < 3; $i++) {
                $previous_y = date('Y', strtotime('-' . $i . ' years'));
                $orders_data_current = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'year(order_date)', $previous_y])
                    ->$andWhere([
                        'and',
                        ['in', 'id', $order_ids],
                    ])
                    ->asArray()
                    ->all();
                $single_count = 0;
                if (isset($orders_data_current) and ! empty($orders_data_current)) {
                    foreach ($orders_data_current as $single) {
                        $single_count += $single['total_amount'];
                    }
                }
                $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                $whole_revenue_amount += $single_count;
            }

            $previous_y = date('Y', strtotime('-2 years'));
            $orders_count = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $orders_count = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            if ($orders_count != 0 && !empty($orders_count)):
                $Value = $orders_count * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'quarter') {
            $current_month = date('m');
            $current_year = date('Y', time());
            if ($current_month < 4)
                $loop_val = 1;
            else if ($current_month < 7)
                $loop_val = 2;
            else if ($current_month < 10)
                $loop_val = 3;
            else
                $loop_val = 4;

            for ($i = 0; $i < $loop_val; $i++) {

                $orders_data_current = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['between', 'month(order_date)', $i * 3 + 1, ($i + 1) * 3])
                    ->andWhere(['=', 'year(order_date)', $current_year])
                    ->$andWhere([
                        'and',
                        ['in', 'id', $order_ids],
                    ])
                    ->asArray()
                    ->all();
                $single_count = 0;
                if (isset($orders_data_current) and ! empty($orders_data_current)) {
                    foreach ($orders_data_current as $single) {
                        $single_count += $single['total_amount'];
                    }
                }
                $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                $whole_revenue_amount += $single_count;
            }
            $current_time = mktime(0, 0, 0, 1, 1, $current_year);
            $first_date = date('Y-m-d', $current_time);
            $end_date = date('Y-m-d');
            $orders_count = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(order_date)', $first_date, $end_date])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $orders_count = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['between', 'date(order_date)', $first_date, $end_date])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            if ($orders_count != 0 && !empty($orders_count)):
                $Value = $orders_count * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        }

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        // for daterange
        if (Yii::$app->request->post('data') == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $startdate = date('Y-m-d', strtotime($date[0]));
            $enddate = date('Y-m-d', strtotime($date[1]));
            $datediff = strtotime($enddate) - strtotime($startdate);
            $numOfdays = floor($datediff / (60 * 60 * 24));
            if ($numOfdays <= 30) {

                for ($i = $numOfdays; $i >= 0; $i--) {
                    $date_current = date('Y-m-d', strtotime('-' . $i . ' days', strtotime($enddate)));
                    $orders_data_current = Order::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'date(order_date)', $date_current])
                        ->$andWhere([
                            'and',
                            ['in', 'id', $order_ids],
                        ])
                        ->asArray()
                        ->all();
                    $single_count = 0;
                    if (isset($orders_data_current) and ! empty($orders_data_current)) {
                        foreach ($orders_data_current as $single) {
                            $single_count += $single['total_amount'];
                        }
                    }
                    $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                    $whole_revenue_amount += $single_count;
                }
            }
            if ($numOfdays > 30 && $numOfdays < 150) {
                $loop_val = floor($numOfdays / 7);
                $current_date = $startdate;
                for ($i = 1; $i <= $loop_val; $i++) {
                    if ($i == 1) {
                        $date_current = date('W', strtotime($current_date));
                        $year_current = date('Y', strtotime($current_date));
                    } else {
                        $date_current = date('W', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                        $year_current = date('Y', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                    }

                    $orders_data_current = Order::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'week(order_date)', $date_current])
                        ->andWhere(['=', 'year(order_date)', $year_current])
                        ->$andWhere([
                            'and',
                            ['in', 'id', $order_ids],
                        ])
                        ->asArray()
                        ->all();
                    $single_count = 0;
                    if (isset($orders_data_current) and ! empty($orders_data_current)) {
                        foreach ($orders_data_current as $single) {
                            $single_count += $single['total_amount'];
                        }
                    }
                    $new_arr[] = array('count' => number_format($single_count,2, '.', ''));
                    $whole_revenue_amount += $single_count;
                }
            }

            $orders_count = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                ->count();
            if (isset($order_ids) and ! empty($order_ids)) {
                $orders_count = Order::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->count();
            }
            if ($orders_count != 0 && !empty($orders_count)):
                $Value = $orders_count * 100 / $products_count;
                $Value = number_format($Value, 0, '', ',');
            else:
                $Value = 0;
            endif;
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => '$' . number_format($whole_revenue_amount, 2), 'ordercountsales' => $Value);
            return json_encode($new_array);
        }
    }

    //Action Show graph for product Sold
    public function actionDashboardGraphProductSold() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if ((!empty($filtered_connection_id))) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $order_ids_query = implode(",", $order_ids);
        /* Show Data According to Month */
        $whole_revenue_amount = 0;
        $new_arr = array();
        /* Show Data According to Week */
        if (Yii::$app->request->Post('data') == 'week'):
            $current_date = date('Y-m-d', time());
            $first_monday_date = date('Y-m-d', strtotime('monday this week', strtotime($current_date)));
            for ($j = 0; $j <= 6; $j++) {
                //get previous date
                $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                if ($previous_date < $first_monday_date)
                    break;

                $orders_data_current = OrderProduct::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'date(created_at)', $previous_date])
                    ->$andWhere([
                        'and',
                        ['in', 'order_id', $order_ids],
                    ])
                    ->count();
                $single_count = $orders_data_current;

                $new_arr[] = array('count' => $single_count);
                $whole_revenue_amount += $single_count;
            }
            $new_arr = array_reverse($new_arr);
            $new_orders = array_column($new_arr, 'count');
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'month') :
            $current_day = date('d');
            $current_month = date('m');
            $current_m = date('Y-m');
            for ($j = 0; $j <= $current_day; $j++) {
                //get previous date
                $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                $check_month = date('Y-m', strtotime($previous_date));

                if ($current_m == $check_month) {
                    $orders_data_current = OrderProduct::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'date(created_at)', $previous_date])
                        ->andWhere(['=', 'month(created_at)', $current_month])
                        ->$andWhere([
                            'and',
                            ['in', 'order_id', $order_ids],
                        ])
                        ->count();
                    $single_count = $orders_data_current;
                    $new_arr[] = array('count' => $single_count);
                    $whole_revenue_amount += $single_count;
                }
            }
            $new_orders = array_column($new_arr, 'count');
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'today'):
            //get orders data acc to channel
            $current_date = date('Y-m-d', time());
            $j = 0;
            for ($i = 1; $i <= 24; $i++) {

                if ($i == 1) {
                    $current_date_hour = date('Y-m-d h:i:s', time());
                } else {
                    $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                }
                $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                $date_check = date('Y-m-d', strtotime($previous_hour));
                if ($date_check == $current_date) {
                    $orders_data_current = OrderProduct::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['between', 'created_at', $previous_hour, $current_date_hour])
                        ->$andWhere([
                            'and',
                            ['in', 'order_id', $order_ids],
                        ])
                        ->count();
                    $single_count = 0;
                    $single_count = $orders_data_current;
                    $new_arr[] = array('count' => $single_count);
                    $whole_revenue_amount += $single_count;
                }
                $j++;
            }
            //for data count at the corner of the graph
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            $i++;
            return json_encode($new_array);
        endif;

        /* Show Data According to Year */

        if (Yii::$app->request->post('data') == 'year'):
            $current_d = date('m');
            $current_y = date('Y');
            $month = (int) $current_d;
            for ($month; $month >= 1; $month--) {

                $orders_data_current = OrderProduct::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'month(created_at)', $month])
                    ->andWhere(['=', 'year(created_at)', $current_y])
                    ->$andWhere([
                        'and',
                        ['in', 'order_id', $order_ids],
                    ])
                    ->count();
                $single_count = 0;
                $single_count = $orders_data_current;

                $new_arr[] = array('count' => $single_count);
                $whole_revenue_amount += $single_count;
            }
            // for count data at the corner of the graph

            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'annual'):

            for ($i = 0; $i < 3; $i ++) {
                $previous_y = date('Y', strtotime('-' . $i . ' years'));
                $orders_data_current = OrderProduct::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['=', 'year(created_at)', $previous_y])
                    ->$andWhere([
                        'and',
                        ['in', 'order_id', $order_ids],
                    ])
                    ->count();
                $single_count = 0;
                $single_count = $orders_data_current;

                $new_arr[] = array('count' => $single_count);
                $whole_revenue_amount += $single_count;
            }
            // for count data at the corner of the graph

            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        endif;

        if (Yii::$app->request->post('data') == 'quarter') {
            $current_month = date('m');
            $current_year = date('Y', time());
            if ($current_month < 4)
                $loop_val = 1;
            else if ($current_month < 7)
                $loop_val = 2;
            else if ($current_month < 10)
                $loop_val = 3;
            else
                $loop_val = 4;

            for ($i = 0; $i < $loop_val; $i++) {
                $orders_data_current = OrderProduct::find()
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['between', 'month(created_at)', $i * 3 + 1, ($i + 1) * 3])
                    ->andWhere(['=', 'year(created_at)', $current_year])
                    ->$andWhere([
                        'and',
                        ['in', 'order_id', $order_ids],
                    ])
                    ->count();
                $single_count = 0;
                $single_count = $orders_data_current;
                $new_arr[] = array('count' => $single_count);
                $whole_revenue_amount += $single_count;
            }
            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        }

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        // for daterange
        if (Yii::$app->request->post('data') == 'dateRange' and !empty($daterange)) {
            $date = explode('-', $daterange);
            $startdate = date('Y-m-d', strtotime($date[0]));
            $enddate = date('Y-m-d', strtotime($date[1]));
            $datediff = strtotime($enddate) - strtotime($startdate);
            $numOfdays = floor($datediff / (60 * 60 * 24));
            if ($numOfdays <= 30) {

                $current_date = date('d');
                $prev_year = date('Y');

                for ($i = $numOfdays; $i >= 0; $i--) {
                    $days_array_current_year = [];
                    $days_array_last_year = [];
                    $date_current = date('Y-m-d', strtotime('-' . $i . ' days', strtotime($enddate)));
                    $orders_data_current = OrderProduct::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'date(created_at)', $date_current])
                        ->$andWhere([
                            'and',
                            ['in', 'order_id', $order_ids],
                        ])
                        ->count();
                    $single_count = 0;
                    $single_count = $orders_data_current;
                    $new_arr[] = array('count' => $single_count);
                    $whole_revenue_amount += $single_count;
                }
            }
            if ($numOfdays > 30 && $numOfdays < 150) {
                $loop_val = floor($numOfdays / 7);
                $current_date = $startdate;
                for ($i = 1; $i <= $loop_val; $i++) {
                    if ($i == 1) {
                        $date_current = date('W', strtotime($current_date));
                        $year_current = date('Y', strtotime($current_date));
                    } else {
                        $date_current = date('W', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                        $year_current = date('Y', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                    }

                    $orders_data_current = OrderProduct::find()
                        ->andWhere(['user_id' => $user_id])
                        ->andWhere(['=', 'week(created_at)', $date_current])
                        ->andWhere(['=', 'year(created_at)', $year_current])
                        ->$andWhere([
                            'and',
                            ['in', 'order_id', $order_ids],
                        ])
                        ->count();
                    $single_count = 0;
                    $single_count = $orders_data_current;
                    $new_arr[] = array('count' => $single_count);
                    $whole_revenue_amount += $single_count;
                }
            }

            $new_orders = array_column($new_arr, 'count');
            $new_orders_count = count($new_orders);
            $new_array = array('data' => $new_orders, 'ordercount' => $whole_revenue_amount);
            return json_encode($new_array);
        }
    }

    //Action Show graph for Average Order Value
    public function actionDashboardGraphAverageOrderValue() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if (!empty($filtered_connection_id)) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $order_ids_query = implode(",", $order_ids);
        /* Show Data According to Month */
        $whole_revenue_amount = 0;
        $new_arr = array();

        if (Yii::$app->request->Post('data') == 'week'):
            $current_date = date('Y-m-d', time());
            $first_monday_date = date('Y-m-d', strtotime('this week last monday', strtotime($current_date)));
            for ($j = 0; $j <= 6; $j++) {
                //get previous date
                $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                if ($previous_date < $first_monday_date)
                    break;

                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['=', 'date(order_date)', $current_date])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['=', 'date(order_date)', $current_date])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            }
            $new_arr = array_reverse($new_arr);
            // for avg count corner of graph data
            $current_date = date('Y-m-d h:i:s', time());
            $month_previous_date_orders = date('Y-m-d h:i:s', strtotime('-6 days'));

            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['between', 'order_date', $month_previous_date_orders, $current_date])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'order_date', $month_previous_date_orders, $current_date])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);
        endif;

        /* Show Data According to Month */
        if (Yii::$app->request->post('data') == 'month') :

            $current_day = date('d');
            $current_m = date('Y-m');
            $current_month = date('m');
            $current_year = date('Y');
            for ($i = 1; $i <= $current_day; $i++):
                $current_date = date('Y-m-d', strtotime('-' . $i . ' days'));
                $check_month = date('Y-m', strtotime($current_date));
                if ($current_m == $check_month) :

                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['=', 'date(order_date)', $current_date])
                        ->andWhere(['=', 'year(order_date)', $current_year])
                        ->all();
                    if (isset($order_ids) and ! empty($order_ids)) {
                        $order_averages = Order::find()
                            ->select(['total_amount'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'date(order_date)', $current_date])
                            ->andWhere(['=', 'year(order_date)', $current_year])
                            ->andWhere(['and', ['in', 'id', $order_ids_query]])
                            ->all();
                    }
                    $_order_sum = 0;
                    foreach ($order_averages as $order_average) {
                        $_order_sum += $order_average->total_amount;
                    }
                    $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
                endif;
            endfor;

            //for avg data count
            $current_month = date('m');
            $current_year = date('Y');
            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'month(order_date)', $current_month])
                ->andWhere(['=', 'year(order_date)', $current_year])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['=', 'month(order_date)', $current_month])
                    ->andWhere(['=', 'year(order_date)', $current_year])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);
        endif;

        /* Show Data According to Today */
        if (Yii::$app->request->post('data') == 'today') :
            //get orders data acc to channel
            $current_date = date('Y-m-d', time());
            $j = 0;
            $new_arr = array();
            for ($i = 1; $i <= 24; $i++) {

                if ($i == 1) {
                    $current_date_hour = date('Y-m-d h:i:s', time());
                } else {
                    $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                }

                $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                $date_check = date('Y-m-d', strtotime($previous_hour));
                if ($date_check == $current_date) {

                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['between', 'order_date', $previous_hour, $current_date_hour])
                        ->andWhere(['=', 'date(order_date)', $current_date])
                        ->all();
                    if (isset($order_ids) and ! empty($order_ids)) {
                        $order_averages = Order::find()
                            ->select(['total_amount'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['between', 'order_date', $previous_hour, $current_date_hour])
                            ->andWhere(['=', 'date(order_date)', $current_date])
                            ->andWhere(['and', ['in', 'id', $order_ids_query]])
                            ->all();
                    }
                    $_order_sum = 0;
                    foreach ($order_averages as $order_average) {
                        $_order_sum += $order_average->total_amount;
                    }
                    $new_arr[] = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
                }

                $j++;
            }

            $current_date = date('Y-m-d', time());
            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'date(order_date)', $current_date])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['=', 'date(order_date)', $current_date])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);

        endif;

        /* Show Data According to Year */
        if (Yii::$app->request->post('data') == 'year'):

            $current_d = date('m');
            $current_y = date('Y');
            $month = (int) $current_d;
            for ($month; $month >= 1; $month--) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['=', 'month(order_date)', $month])
                    ->andWhere(['=', 'year(order_date)', $current_y])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['=', 'month(order_date)', $month])
                        ->andWhere(['=', 'year(order_date)', $current_y])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            }

            $current_y = date('Y');
            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'year(order_date)', $current_y])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['=', 'year(order_date)', $current_y])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);

        endif;

        if (Yii::$app->request->post('data') == 'annual'):

            $current_y = date('Y');
            for ($i = 0; $i < 3; $i ++) {
                $previous_y = date('Y', strtotime('-' . $i . ' years'));
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            }

            $previous_y = date('Y', strtotime('-2 years'));
            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'year(order_date)', $previous_y, $current_y])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);

        endif;


        if (Yii::$app->request->post('data') == 'quarter') {
            $current_month = date('m');
            $current_year = date('Y', time());
            if ($current_month < 4)
                $loop_val = 1;
            else if ($current_month < 7)
                $loop_val = 2;
            else if ($current_month < 10)
                $loop_val = 3;
            else
                $loop_val = 4;
            for ($i = 0; $i < $loop_val; $i++) {

                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'month(order_date)', $i * 3 + 1, ($i + 1) * 3])
                    ->andWhere(['=', 'year(order_date)', $current_year])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['between', 'month(order_date)', $i * 3 + 1, ($i + 1) * 3])
                        ->andWhere(['=', 'year(order_date)', $current_year])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
            }

            $current_time = mktime(0, 0, 0, 1, 1, $current_year);
            $first_date = date('Y-m-d', $current_time);
            $end_date = date('Y-m-d');
            $order_averages = Order::find()
                ->select(['total_amount'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['between', 'date(order_date)', $first_date, $end_date])
                ->all();
            if (isset($order_ids) and ! empty($order_ids)) {
                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'date(order_date)', $first_date, $end_date])
                    ->andWhere(['and', ['in', 'id', $order_ids_query]])
                    ->all();
            }
            $_order_sum = 0;
            foreach ($order_averages as $order_average) {
                $_order_sum += $order_average->total_amount;
            }
            $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
            $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
            return json_encode($new_array);
        }

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        // for daterange
        if (Yii::$app->request->post('data') == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $startdate = date('Y-m-d', strtotime($date[0]));
            $enddate = date('Y-m-d', strtotime($date[1]));
            $datediff = strtotime($enddate) - strtotime($startdate);
            $numOfdays = floor($datediff / (60 * 60 * 24));
            if ($numOfdays <= 30) {
                $current_y = date('Y');

                for ($i = $numOfdays; $i >= 0; $i--) {
                    $date_current = date('Y-m-d', strtotime('-' . $i . ' days', strtotime($enddate)));
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['=', 'date(order_date)', $date_current])
                        ->andWhere(['=', 'year(order_date)', $current_y])
                        ->all();
                    if (isset($order_ids) and ! empty($order_ids)) {
                        $order_averages = Order::find()
                            ->select(['total_amount'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'date(order_date)', $date_current])
                            ->andWhere(['=', 'year(order_date)', $current_y])
                            ->andWhere(['and', ['in', 'id', $order_ids_query]])
                            ->all();
                    }
                    $_order_sum = 0;
                    foreach ($order_averages as $order_average) {
                        $_order_sum += $order_average->total_amount;
                    }
                    $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
                }

                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
                $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
                return json_encode($new_array);
            }
            if ($numOfdays > 30 && $numOfdays < 150) {
                $loop_val = floor($numOfdays / 7);
                $current_date = $startdate;
                for ($i = 1; $i <= $loop_val; $i++) {
                    if ($i == 1) {
                        $date_current = date('W', strtotime($current_date));
                        $year_current = date('Y', strtotime($current_date));
                    } else {
                        $date_current = date('W', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                        $year_current = date('Y', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                    }

                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['=', 'week(order_date)', $date_current])
                        ->andWhere(['=', 'year(order_date)', $year_current])
                        ->all();
                    if (isset($order_ids) and ! empty($order_ids)) {
                        $order_averages = Order::find()
                            ->select(['total_amount'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'week(order_date)', $date_current])
                            ->andWhere(['=', 'year(order_date)', $year_current])
                            ->andWhere(['and', ['in', 'id', $order_ids_query]])
                            ->all();
                    }
                    $_order_sum = 0;
                    foreach ($order_averages as $order_average) {
                        $_order_sum += $order_average->total_amount;
                    }
                    $new_arr[] = $_order_sum == 0 ? 0 : number_format((float)$_order_sum/count($order_averages),2, '.', '');
                }

                $order_averages = Order::find()
                    ->select(['total_amount'])
                    ->andWhere(['=', 'user_id', $user_id])
                    ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                    ->all();
                if (isset($order_ids) and ! empty($order_ids)) {
                    $order_averages = Order::find()
                        ->select(['total_amount'])
                        ->andWhere(['=', 'user_id', $user_id])
                        ->andWhere(['between', 'date(order_date)', $startdate, $enddate])
                        ->andWhere(['and', ['in', 'id', $order_ids_query]])
                        ->all();
                }
                $_order_sum = 0;
                foreach ($order_averages as $order_average) {
                    $_order_sum += $order_average->total_amount;
                }
                $order_count = $_order_sum == 0 ? 0 : $_order_sum/count($order_averages);
                $new_array = array('data' => $new_arr, 'ordercount' => '$' . round($order_count));
                return json_encode($new_array);
            }
        }
    }

    /* Action Show graph by order base */

    public function actionCustomerGraphOrders() {

        $customer_id = Yii::$app->request->post('customer_view_id');
        $user_id = '';
        if (isset($_POST['user_id']) and ! empty($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
        }
        $current_d = date('m');
        $current_y = date('Y');
        $month = (int) $current_d;
        $new_arr = array();
        for ($month; $month >= 1; $month--) {
            $orders_count = Order::find()
                ->select(['*'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'customer_id', $customer_id])
                ->andWhere(['=', 'date(created_at)', $month])
                ->andWhere(['=', 'year(created_at)', $current_y])
                ->count();
            $new_arr[] = array('count' => $orders_count);
        }

        $new_orders = array_column($new_arr, 'count');
        $new_orders_count = count($new_orders);
        $new_array = array('data' => $new_orders, 'ordercount' => $new_orders_count);
        return json_encode($new_array);
    }

    /* Action Show graph by order base */

    public function actionCustomerGraphReturns() {

        $customer_id = Yii::$app->request->post('customer_view_id');
        $user_id = '';
        if (isset($_POST['user_id']) and ! empty($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
        }
        $current_d = date('m');
        $current_y = date('Y');
        $month = (int) $current_d;
        $new_arr = array();
        for ($month; $month >= 1; $month--) {
            $orders_count = Order::find()
                ->select(['*'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'customer_id', $customer_id])
                ->andWhere(['=', 'date(created_at)', $month])
                ->andWhere(['=', 'year(created_at)', $current_y])
                ->andWhere(['and', ['in', 'status', ["Cancel","Refunded","Returned"]]])
                ->count();
            $new_arr[] = array('count' => $orders_count);
        }

        $new_orders = array_column($new_arr, 'count');
        $new_orders_count = count($new_orders);
        $new_array = array('data' => $new_orders, 'ordercount' => $new_orders_count);
        return json_encode($new_array);
    }

    /* Action Show graph by customer item purchase */

    public function actionCustomerGraphItemPurchase() {

        $customer_id = Yii::$app->request->post('customer_view_id');
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;
        $current_d = date('m');
        $current_y = date('Y');
        $month = (int) $current_d;
        $new_arr = array();
        for ($month; $month >= 1; $month--) {

            $orders = Order::find()
                ->select(['product_qauntity'])
                ->andWhere(['=', 'user_id', $user_id])
                ->andWhere(['=', 'customer_id', $customer_id])
                ->andWhere(['=', 'date(created_at)', $month])
                ->andWhere(['=', 'year(created_at)', $current_y])
                ->all();
            if (count($orders) > 0) {
                $order_count_1 = 0;
                foreach ($orders as $order) {
                    $order_count_1 += $order->product_qauntity;
                }
            } else {
                $order_count_1 = 0;
            }

            $new_arr[] = array('count' => $order_count_1);
        }

        $new_orders = array_column($new_arr, 'count');
        $new_orders_count = count($new_orders);
        $new_array = array('data' => $new_orders, 'ordercount' => $new_orders_count);
        return json_encode($new_array);
    }


    /* Clears the Notification & Mark the status as Read */

    public function actionClearNotification() {
        $notif_id = $_POST['notif_id'];
        $get_notf = Notification::find()->Where(['id' => $notif_id])->one();
        if (!empty($get_notf)):
            $get_notf->status = Notification::NOTIFICATION_STATUS_READ;
            $get_notf->save(false);
        endif;
        return 'success';
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionTestoauth() {

        Bigcommerce::configure(array(
            'client_id' => 'j9cwuqcvr7px4e8husdzp2zz0dnj9wp',
            'auth_token' => 'ivg4lm2owkjlu67ja8lb8hzq85s3470',
            'store_hash' => '9c4sjk3b'
        ));

        $ping = Bigcommerce::getTime();
    }

    public function actionSavecurrency() {
        if (isset($_POST) and ! empty($_POST) and isset($_POST['currency']) and ! empty($_POST['currency'])) {
            $user = Yii::$app->user->identity;
            $user->currency = $_POST['currency'];
            $user->save(false);
        }


        $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
        if (isset($selected_currency) and ! empty($selected_currency)) {
            $currency_symbol = $selected_currency['symbol'];
        }

        $conversion_rate = 1;
        if (isset($user->currency) and $user->currency != 'USD') {
//	    $username = Yii::$app->params['xe_account_id'];
//	    $password = Yii::$app->params['xe_account_api_key'];
//	    $URL = Yii::$app->params['xe_base_url'] . 'convert_from.json/?from=USD&to=' . $user->currency . '&amount=1';
//
//	    $ch = curl_init();
//	    curl_setopt($ch, CURLOPT_URL, $URL);
//	    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
//	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//	    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
//	    $result = curl_exec($ch);
//	    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
//	    curl_close($ch);
////echo'<pre>';
//	    $result = json_decode($result, true);
//	    if (isset($result) and ! empty($result) and isset($result['to']) and isset($result['to'][0]) and isset($result['to'][0]['quotecurrency'])) {
//		$conversion_rate = $result['to'][0]['mid'];
//		$conversion_rate = number_format((float) $conversion_rate, 2, '.', '');
//	    }
            $conversion_rate = CurrencyConversion::getCurrencyConversionRate('USD', $user->currency);
        }


        /* Save Currency conversion */
        if ($user->currency != '') {

            $conversion_rate = CurrencyConversion::getCurrencyConversionRate($user->currency, 'USD');
        }


        $array = ['annual_revenue' => $user->annual_revenue * $conversion_rate, 'currency_symbol' => $currency_symbol];
        echo json_encode($array);
        die;
    }

    public function actionSingleProductGraph() {

        $graph_type = Yii::$app->request->post('id');
        $product_id = Yii::$app->request->post('product_id');
        //get user id
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
            $user_id = Yii::$app->user->identity->parent_id;

        $currentmonth = date('m');
        $currentyear = date('Y');

        $connecteduser = UserConnection::find()->Where(['user_id' => $user_id])->available()->all();
        $arr = $names = array();

        if (!empty($connecteduser)) :
            $i = 0;
            foreach ($connecteduser as $store_data) :
                $names[] = $store_data->connection->name;
            endforeach;
        endif;

        if (!empty($connecteduser)) {
            $arr = array();
            $country = '';
            foreach ($connecteduser as $connectedstore) {

                $fin_name = $connectedstore->getPublicName();
                $connection = \Yii::$app->db;

                if ($graph_type == 'day') {
                    $Week_previous_date = date('Y-m-d');
                    $orders_count = OrderProduct::find()->select(['*'])
                        ->leftJoin('order', '`order`.`id` = `order_product`.`order_id`')
                        ->andWhere(['order_product.product_id' => $product_id])
                        ->andWhere(['=', 'order_product.user_id', $user_id])
                        ->andWhere(['=', 'date(order.order_date)', $Week_previous_date])
                        ->andWhere(['=', 'order.user_connection_id', $connectedstore->id])
                        ->count();
                    $arr[] = array('name' => $fin_name, 'count' => $orders_count, 'date' => $Week_previous_date);
                }
                if ($graph_type == 'week') {
                    for ($j = 7; $j >= 1; $j--):
                        $Week_previous_date = date('Y-m-d', strtotime('-' . $j . ' days'));
                        $orders_count = OrderProduct::find()->select(['*'])
                            ->leftJoin('order', '`order`.`id` = `order_product`.`order_id`')
                            ->andWhere(['order_product.product_id' => $product_id])
                            ->andWhere(['=', 'order_product.user_id', $user_id])
                            ->andWhere(['=', 'date(order.order_date)', $Week_previous_date])
                            ->andWhere(['=', 'order.user_connection_id', $connectedstore->id])
                            ->count();
                        $arr[] = array('name' => $fin_name, 'count' => $orders_count, 'date' => $Week_previous_date);
                    endfor;
                }
                /*		 * *********************** FOR MONTHLY************************ */
                if ($graph_type == 'month') {
                    $curr_year = date('Y');
                    for ($j = ($currentmonth - 1); $j >= 0; $j--):
                        $Week_previous_date = date('m', strtotime('-' . $j . ' month'));
                        $Week_pre_date = date('M', strtotime('-' . $j . ' month'));
                        $orders_count = OrderProduct::find()->select(['*'])
                            ->leftJoin('order', '`order`.`id` = `order_product`.`order_id`')
                            ->andWhere(['order_product.product_id' => $product_id])
                            ->andWhere(['=', 'order_product.user_id', $user_id])
                            ->andWhere(['=', 'month(order.order_date)', $Week_previous_date])
                            ->andWhere(['=', 'year(order.order_date)', $curr_year])
                            ->andWhere(['=', 'order.user_connection_id', $connectedstore->id])
                            ->count();
                        $arr[] = array('name' => $fin_name, 'count' => $orders_count, 'date' => $Week_pre_date . ' ' . $currentyear);
                    endfor;
                }

                if ($graph_type == 'quarter') {
                    for ($j = 4; $j > 0; $j--):
                        $current_month = date('y-m-d', strtotime('-' . ($j - 1) * 3 . ' month'));
                        $current_month_text = date('M', strtotime('-' . ($j - 1) * 3 . ' month'));
                        $current_month_text_year = date('Y', strtotime('-' . ($j - 1) * 3 . ' month'));

                        $Week_previous_date = date('y-m-d', strtotime('-' . $j * 3 . ' month'));
                        $Week_pre_date_text = date('M', strtotime('-' . $j * 3 . ' month'));
                        $Week_pre_date_text_year = date('Y', strtotime('-' . $j * 3 . ' month'));
                        $orders_count = OrderProduct::find()->select(['*'])
                            ->leftJoin('order', '`order`.`id` = `order_product`.`order_id`')
                            ->andWhere(['order_product.product_id' => $product_id])
                            ->andWhere(['=', 'order_product.user_id', $user_id])
                            ->andWhere(['>', 'date(order.order_date)', $Week_previous_date])
                            ->andWhere(['<=', 'date(order.order_date)', $current_month])
                            ->andWhere(['=', 'order.user_connection_id', $connectedstore->id])
                            ->count();

                        $arr[] = array('name' => $fin_name, 'count' => $orders_count, 'date' => $Week_pre_date_text . ' ' . $Week_pre_date_text_year . ' to ' . $current_month_text . ' ' . $current_month_text_year);

                    endfor;
                }
                /*		 * *********************** FOR YEARLY************************ */
                if ($graph_type == 'year') {
                    for ($j = 9; $j >= 0; $j--):
                        $Week_previous_date = date('Y', strtotime('-' . $j . ' years'));
                        $previous_year = date('Y', $Week_previous_date);
                        $orders_count = OrderProduct::find()->select(['*'])
                            ->leftJoin('order', '`order`.`id` = `order_product`.`order_id`')
                            ->andWhere(['order_product.product_id' => $product_id])
                            ->andWhere(['=', 'order_product.user_id', $user_id])
                            ->andWhere(['=', 'year(order.order_date)', $previous_year])
                            ->andWhere(['=', 'order.user_connection_id', $connectedstore->id])
                            ->count();
                        $arr[] = array('name' => $fin_name, 'count' => $orders_count, 'date' => $Week_previous_date);
                    endfor;
                }
            }
        }
        $array1 = array();
        foreach ($arr as $a):
            /* Check if key exists or not */
            $dates = $a['date'];
            if (array_key_exists($dates, $array1)) {
                $array1[$dates][] = array(
                    'name' => $a['name'],
                    'count' => $a['count'],
                );
            } else {
                $array1[$dates][] = array(
                    'name' => $a['name'],
                    'count' => $a['count'],
                );
            }
        endforeach;
        $data_val_2 = array();
        $data_val = array();
        foreach ($array1 as $_data => $value) {
            foreach ($value as $_value) {
                $data_val['period'] = $_data;
                $data_val[$_value['name']] = $_value['count'];
            }
            $data_val_2[] = $data_val;
        }
        $keyname = array();
        foreach ($data_val as $key => $val) {
            if ($key != 'period') {
                $keyname[] = $key;
            }
        }
        $colorarr = array(
            0 => '#0091ea',
            1 => '#00b0ff',
            2 => '#40c4ff',
            3 => '#80d8ff',
            4 => '#01579b',
            5 => '#0277bd',
            6 => '#039be5',
            7 => '#03a9f4',
            8 => '#b3e5fc',
            9 => '#81d4fa',
            10 => '#29b6f6',
            11 => '#e1f5fe',
            12 => '#b5af79',
            13 => '#914f84',
            14 => '#ce6d87',
            15 => '#e04a72',
        );
        $randcolor = array();
        $c = 0;
        $cc = 0;
        $showcolorhtml = '';
        foreach ($keyname as $color) {
            $randcolor[] = $colorarr[$c++];
            $showcolorhtml .= '<li><span data-color="main-chart-color" class="BigCommerce connected_store_name" style="background-color: ' . $colorarr[$cc++] . ';"></span>' . $color . '</li>';
        }
        $data = array('data' => json_encode($data_val_2), 'ykeyslabels' => json_encode($keyname), 'linecolors' => json_encode($randcolor), 'store_channel_names' => $names, 'showcolorhtml' => $showcolorhtml);
        return \yii\helpers\Json::encode($data);
    }

    /*
     * Action Show Connected Stores In dashboard
     * ajax hit from custom_graph.js
     */

    public function actionAreachartondashboard() {

        $requestData = Yii::$app->request->post();
        if ( isset($requestData['data']) ) {
            $post = $requestData['data'];
        } else {
            $post = "";
        }
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }

        $current_date = date('Y-m-d', time());
        $filtered_connection_id = [];

        if (isset($_POST['connection_id']) and !empty($_POST['connection_id'])) {
            $filtered_connection_id = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }
        $connected_stores = UserConnection::find()->andFilterWhere([
            'and',
            ['in', 'id', $filtered_connection_id],
        ])->andWhere(['user_id' => $user_id])->available()->all();


        if (!empty($connected_stores)) {
            $arr = array();
            $today = array();
            foreach ($connected_stores as $connected_store) {
                /*		 * *********************** FOR WEEKLY************************ */
                if ($post == 'dashboardchartweek' || $post == 'dashboardchartweekmob') {
                    $first_monday_date = date('Y-m-d', strtotime('this week last monday', strtotime($current_date)));
                    for ($j = 0; $j <= 6; $j++) {
                        //get previous date
                        $previous_date = date('Y-m-d', strtotime("-" . $j . " days"));
                        if ($previous_date < $first_monday_date)
                            break;
                        //var_dump($previous_date);
                        $orders_count = Order::find()
                            ->select(['*'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'user_connection_id', $connected_store->id])
                            ->andWhere(['=', 'date(order_date)', $previous_date])
                            ->count();
                        $character = date('D', strtotime($previous_date));
                        $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $character);
                    }
                    $arr = array_reverse($arr);
                    //die;
                }
                /*		 * *********************** FOR MONTHLY************************ */
                if ($post == 'dashboardchartmonth' || $post == 'dashboardchartmonthmob') {
                    $current_day = date('d');
                    for ($j = 1; $j <= $current_day; $j++):
                        $previous_date = date('Y-m-d', strtotime('-' . ($current_day - $j) . ' days'));
                        $orders_count = Order::find()
                            ->select(['*'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'user_connection_id', $connected_store->id])
                            ->andWhere(['=', 'date(order_date)', $previous_date])
                            ->count();
                        $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $j);
                        // $arr[] = array('name' => $name, 'user' => $orders_count, 'date' => $Week_pre_date);
                    endfor;
                }
                /*		 * *********************** FOR YEARLY************************ */
                if ($post == 'dashboardchartyear' || $post == 'dashboardchartyearmob') {
                    $current_year = date('Y');
                    $current_month = date('m');
                    for ($i = 1; $i <= $current_month; $i++):
                        $Week_previous_date = date('m', strtotime('-' . ($current_month - $i) . ' month'));
                        $orders_count = Order::find()
                            ->select(['*'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'user_connection_id', $connected_store->id])
                            ->andWhere(['=', 'month(order_date)', $Week_previous_date])
                            ->andWhere(['=', 'year(order_date)', $current_year])
                            ->count();
                        $character = date('M', strtotime('-' . ($current_month - $i) . ' month'));
                        $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $character);
                    endfor;
                }
                /*		 * *********************** FOR ANNUAL************************ */
                if ($post == 'dashboardchartannual' || $post == 'dashboardchartannualmob') {
                    $first_order = Order::find()->where(['user_id' => $user_id])->orderBy(['order_date' => SORT_ASC])->one();
                    $first_order_date_str = $first_order->order_date;
                    $current_y = date('Y');
                    $previous_y = date('Y', strtotime($first_order_date_str));
                    for ($j = $previous_y; $j <= $current_y; $j ++):
                        $Week_previous_date = date('Y', strtotime('-' . ($current_y - $j) . ' years'));
                        $orders_count = Order::find()
                            ->select(['*'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'user_connection_id', $connected_store->id])
                            ->andWhere(['=', 'year(order_date)', $Week_previous_date])
                            ->count();
                        $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $Week_previous_date);
                    endfor;
                }
                /*		 * *********************** FOR TODAY************************ */
                if ($post == 'dashboardcharttoday' || $post == 'dashboardcharttodaymob') {
                    $current_date = date('Y-m-d', time());
                    $j = 0;

                    for ($i = 1; $i <= 24; $i++):
                        if ($i == 1) {
                            $current_date_hour = date('Y-m-d h:i:s', time());
                        } else {
                            $current_date_hour = date('Y-m-d h:i:s', strtotime('-' . $j . ' hour'));
                        }
                        $previous_hour = date('Y-m-d h:i:s', strtotime('-' . $i . ' hour'));
                        $previous_hour1 = date('h:i:s', strtotime('-' . $i . ' hour'));
                        $date_check = date('Y-m-d', strtotime($previous_hour));
                        if ($date_check <= $current_date):
                            $current_time = date('Y-m-d');
                            $orders_count = Order::find()
                                ->select(['*'])
                                ->andWhere(['=', 'user_id', $user_id])
                                ->andWhere(['=', 'user_connection_id', $connected_store->id])
                                ->andWhere(['between', 'order_date', $previous_hour, $current_date_hour])
                                ->andWhere(['=', 'date(order_date)', $current_date])
                                ->count();
                            $today[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $previous_hour1);
                            // $arr=array_reverse($arr);
                        endif;
                        $j++;
                    endfor;
                }

                /*		 * *********************** FOR QUARTER************************ */
                if ($post == 'dashboardchartquarter' || $post == 'dashboardchartquartermob') {
                    $current_month = date('m');
                    $current_year = date('Y', time());
                    if ($current_month < 4)
                        $loop_val = 1;
                    else if ($current_month < 7)
                        $loop_val = 2;
                    else if ($current_month < 10)
                        $loop_val = 3;
                    else
                        $loop_val = 4;

                    for ($i = 0; $i < $loop_val; $i++) {
                        $orders_count = Order::find()
                            ->select(['*'])
                            ->andWhere(['=', 'user_id', $user_id])
                            ->andWhere(['=', 'user_connection_id', $connected_store->id])
                            ->andWhere(['between', 'month(order_date)', $i * 3 + 1, ($i + 1) * 3])
                            ->andWhere(['=', 'year(order_date)', $current_year])
                            ->count();
                        $previous_time = mktime(0,0,0, $i * 3 + 1, 1, $current_year);
                        $next_time = mktime(0,0,0, ($i + 1) * 3, 1, $current_year);
                        $previous_m = date('M', $previous_time);
                        $next_m = date('M', $next_time);
                        $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $previous_m . " - " . $next_m);
                    }
                }

                if (!empty($_POST['daterange'])) {
                    $daterange = $_POST['daterange'];
                }
                // for daterange
                if ($post == 'dateRange' and ! empty($daterange)) {
                    $date = explode('-', $daterange);
                    $startdate = date('Y-m-d', strtotime($date[0]));
                    $enddate = date('Y-m-d', strtotime($date[1]));
                    $datediff = strtotime($enddate) - strtotime($startdate);
                    $numOfdays = floor($datediff / (60 * 60 * 24));
                    if ($numOfdays <= 30) {
                        for ($i = $numOfdays; $i >= 0; $i--) {
                            $date_current = date('Y-m-d', strtotime('-' . $i . ' days', strtotime($enddate)));
                            $date_current_text = date('M-d', strtotime('-' . $i . ' days', strtotime($enddate)));

                            $orders_count = Order::find()
                                ->select(['*'])
                                ->andWhere(['=', 'user_id', $user_id])
                                ->andWhere(['=', 'user_connection_id', $connected_store->id])
                                ->andWhere(['=', 'date(order_date)', $date_current])
                                ->andWhere(['=', 'year(order_date)', date('Y')])
                                ->count();
                            $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => $date_current_text . ' ' . date('Y'));
                        }
                    }
                    if ($numOfdays > 30 && $numOfdays < 150) {
                        $loop_val = floor($numOfdays / 7);
                        $current_date = $startdate;
                        for ($i = 1; $i <= $loop_val; $i++) {
                            if ($i == 1) {
                                $date_current = date('W', strtotime($current_date));
                                $year_current = date('Y', strtotime($current_date));
                            } else {
                                $date_current = date('W', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                                $year_current = date('Y', strtotime('+' . $i * 7 . 'days', strtotime($current_date)));
                            }

                            $orders_count = Order::find()
                                ->select(['*'])
                                ->andWhere(['=', 'user_id', $user_id])
                                ->andWhere(['=', 'user_connection_id', $connected_store->id])
                                ->andWhere(['=', 'week(order_date)', $date_current])
                                ->andWhere(['=', 'year(order_date)', $year_current])
                                ->count();
                            $arr[] = array('name' => $connected_store->getPublicName(), 'user' => $orders_count, 'date' => 'Week ' . $i);
                        }
                    }
                }
            }
        }

        if (!empty($today)) {
            $arr = array_reverse($today);
        }
        $array1 = array();
        if (!empty($arr)) {
            foreach ($arr as $a):
                /* Check if key exists or not */
                $dates = $a['date'];
                if (array_key_exists($dates, $array1)) {
                    $array1[$dates][] = array(
                        'name' => $a['name'],
                        'user' => $a['user'],
                    );
                } else {
                    $array1[$dates][] = array(
                        'name' => $a['name'],
                        'user' => $a['user'],
                    );
                }
            endforeach;
        }

        $data_val_2 = array();
        foreach ($array1 as $_data => $value) {
            $data_val = array();
            foreach ($value as $_value) {
                $data_val['period'] = $_data;
                //$data_val[$_value['name']]=$_value['name'];
                $data_val[$_value['name'] . "  Orders"] = $_value['user'];
            }
            $data_val_2[] = $data_val;
        }

        $keyname = array();
        if (!empty($data_val)) {
            foreach ($data_val as $key => $val) {
                if ($key == 'period') {

                } else {
                    $keyname[] = $key;
                }
            }
        }

        $keyname_2 = $keyname;

        /*
         * array to store hex color
         */
        $colorarr = array(
            0 => '#0091ea', #e04a72
            1 => '#00b0ff', #83ed1a
            2 => '#40c4ff',
            3 => '#80d8ff',
            4 => '#01579b',
            5 => '#0277bd',
            6 => '#039be5',
            7 => '#03a9f4',
            8 => '#b3e5fc',
            9 => '#81d4fa',
            10 => '#29b6f6',
            11 => '#e1f5fe',
            12 => '#b5af79',
            13 => '#914f84',
            14 => '#ce6d87',
            15 => '#e04a72',
        );
        $randcolor = array();
        $randcolor_2 = array();
        $showcolorhtml = '';
        $c = 0;
        $cc = 0;
        foreach ($keyname as $color) {
            // $randcolor[] = '#' . random_color();
            $randcolor[] = $colorarr[$c++];
            $strname = explode(' ', $color);
            unset($strname[count($strname) - 1]);
            $strname = implode(' ', $strname);

            $showcolorhtml .= '<li><span data-color="main-chart-color" class="BigCommerce connected_store_name" style="background-color: ' . $colorarr[$cc++] . ';"></span>' . $strname . '</li>';
        }

        $randcolor_2 = $randcolor;
        if (!empty($data_val_2)) {
            $data = array('data' => json_encode($data_val_2), 'ykeyslabels' => json_encode($keyname_2), 'linecolors' => json_encode($randcolor_2), 'showcolorhtml' => $showcolorhtml);
        } else {
            $data = 'Invalid';
        }
        return \yii\helpers\Json::encode($data);
    }

    public function actionGetRecentOrders() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }
        $user = User::find()->where(['id' => $user_id])->one();

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if ((!empty($filtered_connection_id))) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $order_ids_query = implode(",", $order_ids);
        $data = '';
        if (isset($_POST['data']) and ! empty($_POST['data'])) {
            $data = $_POST['data'];
        }
        if ($data == 'month') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'month(created_at)', date('m')])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }
        if ($data == 'week') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'week(created_at)', date('W') - 1])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }
        if ($data == 'quarter') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }
        if ($data == 'year') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }
        if ($data == 'annual') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(created_at)', date('Y-m-d', strtotime('-3 years')), date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        if ($data == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $startdate = date('Y-m-d', strtotime($date[0]));
            $enddate = date('Y-m-d', strtotime($date[1]));
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'created_at', date('Y-m-d', strtotime($startdate)), date('Y-m-d', strtotime($enddate))])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }
        if ($data == 'today') {
            $orders_data_REcent = Order::find()
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'date(created_at)', date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])
                ->orderBy(['id' => SORT_DESC,])->limit(5)
                ->all();
        }

        return $this->renderAjax('recentorder', [
                'orders_data_REcent' => $orders_data_REcent,
                'user' => $user
            ]
        );
    }

    public function actionGetLatestEnagagements() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }
        $user = User::find()->where(['id' => $user_id])->one();

        $order_ids = [];
        $product_ids = [];
        $customer_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }

            $connectedstore_products = Product::find()->select('product.id')
                ->joinWith(['productConnections'])
                ->andFilterWhere([
                    'and',
                    ['in', 'product_connection.user_connection_id', $filtered_connection_id],
                ])
                ->andWhere(['product.user_id' => $user_id])
                ->groupBy('product_connection.product_id')
                ->asArray()->all();
            if (isset($connectedstore_products) and ! empty($connectedstore_products)) {
                foreach ($connectedstore_products as $single) {
                    $product_ids[] = $single['id'];
                }
            }
            $connectedstore_customers = Customer::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->asArray()->all();
            if (isset($connectedstore_customers) and ! empty($connectedstore_customers)) {
                foreach ($connectedstore_customers as $single) {
                    $customer_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if ((!empty($filtered_connection_id) or ! empty($filtered_connection_id))) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $order_ids_query = implode(",", $order_ids);
        $data = '';
        if (isset($_POST['data']) and ! empty($_POST['data'])) {
            $data = $_POST['data'];
        }

        if ($data == 'month') {
            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'month(customer_created)', date('m')])
                ->andWhere(['=', 'year(customer_created)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'month(created_at)', date('m')])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'month(created_at)', date('m')])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        if ($data == 'week') {

            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'week(customer_created)', date('W') - 1])
                ->andWhere(['=', 'year(customer_created)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'week(created_at)', date('W') - 1])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'week(created_at)', date('W') - 1])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        if ($data == 'quarter') {

            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(customer_created)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        if ($data == 'year') {
            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(customer_created)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'year(created_at)', date('Y')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        if ($data == 'annual') {
            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(customer_created)', date('Y-m-d', strtotime('-3 years')), date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(created_at)', date('Y-m-d', strtotime('-3 years')), date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'date(created_at)', date('Y-m-d', strtotime('-3 years')), date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        if ($data == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $startdate = date('Y-m-d', strtotime($date[0]));
            $enddate = date('Y-m-d', strtotime($date[1]));
            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'customer_created', date('Y-m-d', strtotime($startdate)), date('Y-m-d', strtotime($enddate))])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['between', 'created_at', date('Y-m-d', strtotime($startdate)), date('Y-m-d', strtotime($enddate))])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()
                ->andWhere(['user_id' => $user_id])->select('id, created_at, user_connection_id')
                ->andWhere(['between', 'created_at', date('Y-m-d', strtotime($startdate)), date('Y-m-d', strtotime($enddate))])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        if ($data == 'today') {
            $customer_eng_data = Customer::find()->select('id, first_name, last_name, customer_created, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'date(customer_created)', date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $customer_ids],
                ])->asArray()->all();
            $products_eng_data = Product::find()->select('id, name, created_at')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'date(created_at)', date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $product_ids],
                ])->asArray()->all();
            $orders_eng_data = Order::find()->select('id, created_at, user_connection_id')
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['=', 'date(created_at)', date('Y-m-d')])
                ->$andWhere([
                    'and',
                    ['in', 'id', $order_ids],
                ])->asArray()
                ->all();
        }
        /* Latest Engagements */
        $customer_eng_data_1 = array();
        if (isset($customer_eng_data) && !empty($customer_eng_data)) {
            foreach ($customer_eng_data as $latest) {
                $element = array();
                $element['customer_id'] = $latest['id'];
                $element['first_name'] = $latest['first_name'];
                $element['last_name'] = $latest['last_name'];
                $element['created_at'] = $latest['customer_created'];
                $customer_eng_data_1[] = $element;
            }
        }
        $products_eng_data_1 = array();
        if (isset($products_eng_data) && !empty($products_eng_data)) {
            $products_eng_data_1 = $products_eng_data;
        }
        $orders_eng_data_1 = array();
        if (isset($orders_eng_data) && !empty($orders_eng_data)) {
            foreach ($orders_eng_data as $latest) {
                $element = array();
                $element['order_id'] = $latest['id'];
                $element['created_at'] = $latest['created_at'];
                $orders_eng_data_1[] = $element;
            }
        }
        $late_engag_arr = array_merge($customer_eng_data_1, $products_eng_data_1, $orders_eng_data_1);

        $sort = array();
        foreach ($late_engag_arr as $k => $v) {
            $sort['created_at'][$k] = $v['created_at'];
        }
        if (!empty($sort['created_at'])) {
            array_multisort($sort['created_at'], SORT_DESC, $late_engag_arr);
        }

        $latest_engagements = array_slice($late_engag_arr, 0, 5);
        return $this->renderAjax('latestengagements', [
                'lates_engagements' => $latest_engagements,
                'user' => $user
            ]
        );
    }

    public function actionImportStatus(){
        $requestData = Yii::$app->request->post();
        if ( isset($requestData['user_id']) ) {
            $userId = $requestData['user_id'];
        } else {
            $userId = "";
        }
        $response = [
            'completed' => false
        ];

        if ( !empty($userId) && isset($userId) ) {
            $currentStatusCount = UserConnection::find()
                ->where(
                [
                    'user_id' => $userId,
                    'import_status' => UserConnection::IMPORT_STATUS_PROCESSING,
                    'connected' => UserConnection::CONNECTED_YES
                ])
                ->available()
                ->count();

            if ( $currentStatusCount == 0 ) {
                $response['completed'] = true;
            } else {
                $response['count'] = $currentStatusCount;
            }
        }

        return json_encode($response);
    }


    function actionGetFilteredProductsByVolume() {
        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }
        $user = User::find()->where(['id' => $user_id])->one();

        $currency = $user->currency;
        $currency = strtolower($currency);
        $symbol = CurrencySymbol::find()->select(['symbol'])->where(['name' => $currency])->one();

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if ((!empty($filtered_connection_id))) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and !empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $filter = [];

        $data_type = Yii::$app->request->Post('data');

        /* Show Data According to Week */
        if ($data_type == 'week') {
            //current date
            $current_date = date('Y-m-d');
            $first_monday_date = date('Y-m-d', strtotime('this week last monday', strtotime($current_date)));
            $filter = ['between', 'date(created_at)', $first_monday_date, $current_date];
        }

        if ($data_type == 'month') {
            //current date
            $current_date = date('Y-m-d');
            $current_day = date('d');
            $previous_date = date('Y-m-d', strtotime('-' . $current_day . ' days'));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        if ($data_type == 'today') {
            //current date
            $previous_date = $current_date = date('Y-m-d');
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        if ($data_type == 'year') {
            //current date
            $current_date = date('Y');
            $filter = ['=', 'year(created_at)', $current_date];
        }
        if ($data_type == 'quarter') {
            //current date
            $current_date = date('Y');
            $filter = ['=', 'year(created_at)', $current_date];
        }
        if ($data_type == 'annual') {
            //current date
            $current_date = date('Y-m-d');
            $previous_date = date('Y-m-d', strtotime('-3 years'));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }
        // for daterange
        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        if ($data_type == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $previous_date = date('Y-m-d', strtotime($date[0]));
            $current_date = date('Y-m-d', strtotime($date[1]));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        $products = OrderProduct::find()
            ->select(['sum(qty) AS qty', 'product_id'])
            ->where(['user_id' => $user_id])
            ->$andWhere([
                'and',
                ['in', 'order_id', $order_ids],
            ])
            ->andFilterWhere($filter)
            ->groupBy(['product_id'])
            ->orderBy(['qty' => SORT_DESC])
            ->with(['product' => function($query) {
                $query->where(['not like', 'status', 'in_active']);
                $query->select('id, price, name');
            },
                'product.productImages' => function($query) {
                    $query->select('product_id,link');
                }])
            ->asArray()
            ->all();

        $products_count = 0;
        if (isset($products) and ! empty($products)) {
            foreach ($products as $single) {
                if (isset($single['product']) and ! empty($single['product'])) {
                    $products_count++;
                }
            }
        }

        return $this->renderAjax('getproducts', [
                'products' => $products,
                'user' => $user,
                'symbol' => $symbol->symbol,
                'products_count' => $products_count,
            ]
        );
    }

    function actionGetFilteredProductsByRevenue() {

        $user_id = Yii::$app->user->identity->id;
        if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $user_id = Yii::$app->user->identity->parent_id;
        }
        $filtered_connection_id = [];
        if (isset($requestData['connection_id']) and !empty($requestData['connection_id'])) {
            $filtered_connection_id[] = $_POST['connection_id'];
        }
        else if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0) {
                    $userConnections = UserConnection::find()
                        ->where(['user_id' => $user_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($userConnections as $userConnection) {
                        $filtered_connection_id[] = $userConnection->id;
                    }
                }
            }
        }
        $user = User::find()->where(['id' => $user_id])->one();
        $currency = $user->currency;
        $currency = strtolower($currency);
        $symbol = CurrencySymbol::find()->select(['symbol'])->where(['name' => $currency])->one();

        $order_ids = [];
        if (!empty($filtered_connection_id)) {
            $connected_store = Order::find()->select('id')->andFilterWhere([
                'and',
                ['in', 'user_connection_id', $filtered_connection_id],
            ])->andWhere(['user_id' => $user_id])->asArray()->all();
            if (isset($connected_store) and ! empty($connected_store)) {
                foreach ($connected_store as $single) {
                    $order_ids[] = $single['id'];
                }
            }
        }
        $andWhere = 'andFilterWhere';
        if (!empty($filtered_connection_id)) {
            $andWhere = 'andWhere';
            if (isset($order_ids) and ! empty($order_ids)) {
                $andWhere = 'andFilterWhere';
            }
        }
        $filter = [];
        $data_type = Yii::$app->request->Post('data');
        /* Show Data According to Week */
        if ($data_type == 'week') {
            //current date
            $current_date = date('Y-m-d', time());
            $first_monday_date = date('Y-m-d', strtotime('this week last monday', strtotime($current_date)));
            $filter = ['between', 'date(created_at)', $first_monday_date, $current_date];
        }

        if ($data_type == 'month') {
            //current date
            $current_date = date('Y-m-d');
            $current_day = date('d');
            $previous_date = date('Y-m-d', strtotime('-' . $current_day . ' days'));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        if ($data_type == 'today') {
            //current date
            $previous_date = $current_date = date('Y-m-d');
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        if ($data_type == 'year') {
            //current date
            $current_date = date('Y');
            $filter = ['=', 'year(created_at)', $current_date];
        }
        if ($data_type == 'quarter') {
            //current date
            $current_date = date('Y');
            $filter = ['=', 'year(created_at)', $current_date];
        }
        if ($data_type == 'annual') {
            //current date
            $current_date = date('Y-m-d');
            $previous_date = date('Y-m-d', strtotime('-3 years'));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }

        // for daterange

        if (!empty($_POST['daterange'])) {
            $daterange = $_POST['daterange'];
        }
        if ($data_type == 'dateRange' and ! empty($daterange)) {
            $date = explode('-', $daterange);
            $previous_date = date('Y-m-d', strtotime($date[0]));
            $current_date = date('Y-m-d', strtotime($date[1]));
            $filter = ['between', 'date(created_at)', $previous_date, $current_date];
        }
        $products = OrderProduct::find()
            ->select(['sum(price) AS price', 'product_id'])
            ->where(['user_id' => $user_id])
            ->$andWhere([
                'and',
                ['in', 'id', $order_ids],
            ])
            ->andFilterWhere($filter)
            ->groupBy(['product_id'])
            ->orderBy(['price' => SORT_DESC])
            ->with([
                'product' => function($query) {
                    $query->where(['not like', 'status', 'in_active']);
                    $query->select('id, price, name');
                },
                'product.productImages' => function($query) {
                    $query->select('product_id,link');
                }])
            ->asArray()
            ->all();
        $products_count = 0;
        if (isset($products) and ! empty($products)) {
            foreach ($products as $single) {
                if (isset($single['product']) and ! empty($single['product'])) {
                    $products_count++;
                }
            }
        }
        return $this->renderAjax('getproducts', [
                'products' => $products,
                'user' => $user,
                'symbol' => $symbol->symbol,
                'products_count' => $products_count,
            ]
        );
    }
}
