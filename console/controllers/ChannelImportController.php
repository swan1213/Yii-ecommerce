<?php
namespace console\controllers;

use common\models\Crontask;
use frontend\components\ChannelFlipkartComponent;
use frontend\components\ChannelJetComponent;
use frontend\components\ChannelShipheroComponent;
use frontend\models\RakutenConnectionForm;
use Yii;
use yii\console\Controller;
use common\models\Country;
use common\models\CurrencyConversion;
use common\models\User;
use common\models\Notification;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\UserIntegration;
use frontend\components\ChannelNeweggComponent;
use frontend\components\ChannelWechatComponent;
use frontend\components\ChannelShiphawkComponent;
use frontend\components\ChannelLazadaComponent;
use frontend\components\ChannelAmazonComponent;
use frontend\components\PosSquareComponent;
use frontend\components\CustomFunction;
use common\commands\SendEmailCommand;


class ChannelImportController extends Controller {
    public function actionNewegg($user_connection_id, $previous_connection_status = false) {
    	// get one row from UserConnection table with connection id of the Connection table
    	$user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
    	$user_id = $user_connection_row->user_id;

        try {
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $store_name = $user_connection_row->userConnectionDetails->store_name;
            $file_info = $user_connection_row->userConnectionDetails->others;

        	$user_credential = $user_connection_row->connection_info;
        	if(!isset($user_credential['type']) or empty($user_credential['type'])) {
        		throw new \Exception('The credential type is not set.');
        	}

        	if(!isset($user_credential['market_id']) or empty($user_credential['market_id'])) {
        		throw new \Exception('The credential market id is not set.');
        	}

        	if(!isset($user_credential['api_key']) or empty($user_credential['api_key'])) {
        		throw new \Exception('The credential api key is not set.');
        	}

        	if(!isset($user_credential['secret_key']) or empty($user_credential['secret_key'])) {
        		throw new \Exception('The credential secret key is not set.');
        	}

            // parse item report
            $zip_products = ChannelNeweggComponent::read_zip_file($file_info['file']);
            $product_response = ChannelNeweggComponent::getNeweggProducts($user_credential);
            $analyze_result = ChannelNeweggComponent::analyze_products($product_response);

            $filter_response = ChannelNeweggComponent::filterNeweggProducts(
                $zip_products,
                $analyze_result
            );

            if($filter_response['is_update'] == true) {
                $email_message = 'Your batch item zip file is old. Please upload new one.';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Newegg Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Newegg Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $msg = 'For the ' . $store_name . ', your batch item zip file is old. Please upload new one.';
                Notification::saveMessage($user_id, "Channel ".$store_name, $msg);
            }

            ChannelNeweggComponent::importNeweggProducts(
                $filter_response['products'],
                $user_id,
                $user_connection_id,
                $user_credential
            );

            $order_response = ChannelNeweggComponent::getNeweggOrders($user_credential);
            if(!is_null($order_response) && !empty($order_response->OrderInfo)) {
                ChannelNeweggComponent::importNeweggOrders(
                    $order_response->OrderInfo,
                    $user_id,
                    $user_connection_id,
                    1
                );
            }

            if($previous_connection_status == false) {
                $email_message = 'Your newegg channel import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Newegg Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Newegg Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'NeweggCron-'.$user_connection_id;
                $task->action = 'channel-import/newegg';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_connection_row->save(true, ['import_status', 'connected']);
                $msg = 'Your ' . $store_name . ' channel data has been successfully imported.';
                Notification::saveMessage($user_id, "Channel ".$store_name, $msg);
            }
        } catch(\InvalidArgumentException $e) {
        	UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        } catch (\Exception $e) {
        	UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }

    public function actionWalkthechat($user_connection_id, $previous_connection_status = false) {
        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
        $user_id = $user_connection_row->user_id;
        $user_connection_detail_row = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        $store_name = $user_connection_detail_row->store_name;

        try {
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $user_credential = $user_connection_row->connection_info;
            if(!isset($user_credential['type']) or empty($user_credential['type'])) {
                throw new \Exception('The credential type is not set.');
            }

            if(!isset($user_credential['username']) or empty($user_credential['username'])) {
                throw new \Exception('The credential username is not set.');
            }

            if(!isset($user_credential['password']) or empty($user_credential['password'])) {
                throw new \Exception('The credential password is not set.');
            }

            $url = 'https://cms-api.walkthechat.com/login/admin';
            $post_data = array(
                'username' => $user_credential['username'],
                'password' => $user_credential['password']
            );

            $response_data = CustomFunction::curlHttp($url, $post_data, 'POST');
            $json_data = json_decode($response_data, true);

            if(isset($json_data['error']) and $json_data['error']['success'] == false) {
                throw new \Exception($json_data['error']['message']);
            }

            $token = $json_data['token']['token'];

            $url = 'https://cms-api.walkthechat.com/projects/';
            $header = array(
                "x-access-token: $token"
            );
            $response_data = CustomFunction::curlHttp($url, null, 'GET', $header);
            $json_data = json_decode($response_data, true);

            if(isset($json_data['projects']) and count($json_data['projects'])) {
                foreach ($json_data['projects'] as $project) {
                    $project_id = $project['_id'];
                    $header = array(
                        "x-access-token: $token",
                        "x-id-project: $project_id"
                    );

                    ChannelWechatComponent::checkProjectAvalability(
                        $header,
                        $user_id,
                        $user_connection_id,
                        $user_connection_detail_row
                    );
                }
            }

            if($previous_connection_status == false) {
                $email_message = 'Your walkthechat channel import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Walkthechat Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Walkthechat Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'WalktheChatCron-'.$user_connection_id;
                $task->action = 'channel-import/walkthechat';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_connection_row->save(true, ['import_status', 'connected']);
                $msg = 'Your ' . $store_name . ' channel data has been successfully imported via walkthechat.';
                Notification::saveMessage($user_id, "Channel ".$store_name, $msg);
            }
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }

    public function actionWechat($user_connection_id, $previous_connection_status = false) {
        // get one row from UserConnection table with connection id of the Connection table
        $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
        $user_id = $user_connection_row->user_id;
        $user_connection_detail_row = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
        $store_name = $user_connection_detail_row->store_name;

        try {
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $user_credential = $user_connection_row->connection_info;
            if(!isset($user_credential['type']) or empty($user_credential['type'])) {
                throw new \Exception('The credential type is not set.');
            }

            if(!isset($user_credential['market_id']) or empty($user_credential['market_id'])) {
                throw new \Exception('The credential market id is not set.');
            }

            if(!isset($user_credential['client_id']) or empty($user_credential['client_id'])) {
                throw new \Exception('The credential client id is not set.');
            }

            if(!isset($user_credential['client_secret']) or empty($user_credential['client_secret'])) {
                throw new \Exception('The credential client secret is not set.');
            }

            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$user_credential['client_id'].'&secret='.$user_credential['client_secret'];  
            $response = CustomFunction::curlHttp($url);//here cannot use file_get_contents  
            $json_response = json_decode($response, true);
            
            if(!(isset($json_response['access_token']) and $json_response['access_token'])) {
                throw new \Exception($json_response['errmsg']);
            }
            $token = $json_response['access_token'];

            $conversion_rate = CurrencyConversion::getCurrencyConversionRate($user_connection_detail_row->country_code, 'USD');

            if(empty($conversion_rate)) {
                $conversion_rate = 1;
            }

            ChannelWechatComponent::importWechatCategories(
                $token,
                $user_id,
                $user_connection_id
            );
            ChannelWechatComponent::importWechatProducts(
                $token,
                $user_id,
                $user_connection_id,
                $conversion_rate
            );
            ChannelWechatComponent::importWechatOrders(
                $token,
                $user_id,
                $user_connection_id,
                $conversion_rate
            );

            if($previous_connection_status == false) {
                $email_message = 'Your wechat channel import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Wechat Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Wechat Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'WeChatCron-'.$user_connection_id;
                $task->action = 'channel-import/wechat';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_connection_row->save(true, ['import_status', 'connected']);
                $msg = 'Your ' . $store_name . ' channel data has been successfully imported via wechat.';
                Notification::saveMessage($user_id, "Channel ".$store_name, $msg);
            }
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }

    public function actionSquare($user_connection_id, $is_import = false) {
        try {
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            
            $user_id = $user_connection_row->user_id;
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $user_credential = $user_connection_row->connection_info;
            
            if(!isset($user_credential['location_id']) or empty($user_credential['location_id'])) {
                throw new \Exception('There is no location id in your credential.');
            }

            if(!isset($user_credential['access_token']) or empty($user_credential['access_token'])) {
                throw new \Exception('There is no access token in your credential.');
            }

            \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($user_credential['access_token']);
            \SquareConnect\Configuration::getDefaultConfiguration()->setSSLVerification(FALSE);

            PosSquareComponent::importCustomers($user_id, $user_connection_id);
            PosSquareComponent::importObjects(
                $user_id,
                $user_connection_id,
                $user_credential['location_id'],
                $user_connection_row->userConnectionDetails->country_code,
                $user_connection_row->userConnectionDetails->currency
            ); 
            PosSquareComponent::importOrders(
                $user_id,
                $user_connection_id,
                $user_credential['location_id'],
                $user_connection_row->userConnectionDetails->country_code,
                $user_connection_row->userConnectionDetails->currency
            );

            if($is_import == false) {
                $email_message = 'Your Square pos import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Square Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Square Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'SquareCron-'.$user_connection_id;
                $task->action = 'channel-import/square';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_integration_instance->save(true, ['connected']);
                $msg = 'Your Square Channel data has been successfully imported.';
                Notification::saveMessage($user_id, "Square POS integration", $msg);
            }
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump('Square POS: '.$e->getMessage());
        }
    }

    public function actionShiphawk($user_connection_id) {
        try {
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            $user_id = $user_connection_row->user_id;
            $user_credential = $user_connection_row->connection_info;

            ChannelShiphawkComponent::importCustomers(
                $user_credential['product_key'],
                $user_id,
                $user_connection_id
            );

            ChannelShiphawkComponent::importProducts(
                $user_credential['product_key'],
                $user_id,
                $user_connection_id
            );

            ChannelShiphawkComponent::importOrders(
                $user_credential['product_key'],
                $user_id,
                $user_connection_id
            );

            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $user_connection_row->connected = UserConnection::CONNECTED_YES;
            $user_connection_row->save(true, ['import_status', 'connected']);
            $msg = 'Your Shiphawk channel data has been successfully imported.';
            Notification::saveMessage($user_id, "Shiphawk channel integration", $msg);
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }

    public function actionShiphero($user_connection_id) {
        try {
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            $user_id = $user_connection_row->user_id;
            $user_credential = $user_connection_row->connection_info;

            ChannelShipheroComponent::importCustomers(
                $user_credential['api_key'],
                $user_id,
                $user_connection_id
            );

            ChannelShipheroComponent::importProducts(
                $user_credential['api_key'],
                $user_id,
                $user_connection_id
            );

            ChannelShipheroComponent::importOrders(
                $user_credential['api_key'],
                $user_id,
                $user_connection_id
            );

            $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $user_connection_row->connected = UserConnection::CONNECTED_YES;
            $user_connection_row->save(true, ['import_status', 'connected']);
            $msg = 'Your Shiphero channel data has been successfully imported.';
            Notification::saveMessage($user_id, "Shiphero channel integration", $msg);
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            Notification::saveMessage($user_id, "Shiphero channel integration", $e->getMessage());
        }
    }

    public function actionLazada($user_connection_id, $previous_connection_status = false) {
        try {
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            if(empty($user_connection_row)) {
                throw new \Exception('Invalid id for a UserConnection table');
            }

            $user_id = $user_connection_row->user_id;
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $user_credential = $user_connection_row->connection_info;
            if(!isset($user_credential['market_id']) or empty($user_credential['market_id'])) {
                throw new \Exception('The market id(user id) is not set in UserConnection table');
            }

            if(!isset($user_credential['api_url']) or empty($user_credential['api_url'])) {
                throw new \Exception('The api url is not set in UserConnection table');
            }

            if(!isset($user_credential['api_key']) or empty($user_credential['api_key'])) {
                throw new \Exception('The api key is not set in UserConnection table');
            }
            $user_connection_detail_model = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
            if(empty($user_connection_detail_model)) {
                throw new \Exception('Invalid id for a UserConnectionDetails table');
            }

            $country_code = $user_connection_detail_model->country_code;
            $currency_code = $user_connection_detail_model->currency;
            $store_name = $user_connection_detail_model->store_name;

            ChannelLazadaComponent::importProducts(
                $user_id,
                $user_connection_id,
                $country_code,
                $currency_code,
                $user_credential
            );
            ChannelLazadaComponent::importOrders(
                $user_id,
                $user_connection_id,
                $currency_code,
                $user_credential
            );

            if($previous_connection_status == false) {
                $email_message = 'Your lazada channel import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Lazada Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Lazada Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'LazadaCron-'.$user_connection_id;
                $task->action = 'channel-import/lazada';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_connection_row->save(true, ['import_status', 'connected']);
                $msg = 'Your '.$store_name.' channel data has been successfully imported.';
                Notification::saveMessage($user_id, $store_name." channel integration", $msg);
            }
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }



    public function actionJet($user_connection_id, $user_notify=true) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $jet_Connection = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($jet_Connection)) {

            $importUser = $jet_Connection->user;
            $channel_connection_details = $jet_Connection->userConnectionDetails;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';


            $channel_currency_code = $channel_connection_details->currency;

            if ($channel_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($channel_currency_code, $userCurrency);
            }


            ChannelJetComponent::jetProductImporting($user_connection_id, $conversion_rate);

            ChannelJetComponent::jetOrderImporting($user_connection_id, 'orders/complete', $conversion_rate);

            ChannelJetComponent::jetOrderImporting($user_connection_id, 'orders/created', $conversion_rate);

            ChannelJetComponent::jetOrderImporting($user_connection_id, 'orders/ready', $conversion_rate);

            ChannelJetComponent::jetOrderImporting($user_connection_id, 'orders/acknowledged', $conversion_rate);

            ChannelJetComponent::jetOrderImporting($user_connection_id, 'orders/inprogress', $conversion_rate);



            if ( $user_notify ){

                /* Send Email */
                $email_message = 'Success, Your Jet Channel importing has done.';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Jet Import'),
                    'view' => '@common/mail/template',
                    'to' => $email,
                    'params' => [
                        'title' => 'Jet Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                //Drop-Down Notification for User
                $notif_type = "Jet";

                $notification_model = new Notification();
                $notification_model->user_id = $user_id;
                $notification_model->title = $notif_type;
                $notification_model->message = 'Your ' . $notif_type . ' channel data has been successfully imported.';
                $notification_model->save(false);

                $cronTaskName = 'JetCron-'.$user_connection_id;
                $cronTask = Crontask::findOne(['name' => $cronTaskName]);
                if ( empty($cronTask) ){

                    $cronTask = new Crontask();

                    $cronTask->name = $cronTaskName;
                    $cronTask->action = "channel-import/jet";
                    $cronParam = [$user_connection_id, false];
                    $cronTask->params = @json_encode($cronParam, JSON_UNESCAPED_UNICODE);
                    $cronTask->enabled = Crontask::ENABLED_YES;
                    $cronTask->completed = Crontask::COMPLETED_YES;
                    $cronTask->save(false);


                }
            }


            $jet_Connection->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $jet_Connection->save(true, ['import_status']);

            return true;
        }

        return false;

    }

    public function actionFlipkart($user_connection, $user_notify=true){
        ini_set("memory_limit", "-1");
        set_time_limit(0);

    }

    public function actionFlipkartParseProducts($user_connection_id, $xls_file_path, $user_notify=true) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $flipkart_Connection = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($flipkart_Connection)) {

            $importUser = $flipkart_Connection->user;
            $channel_connection_details = $flipkart_Connection->userConnectionDetails;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'INR';

            $channel_currency_code = $channel_connection_details->currency;

            if ($channel_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($channel_currency_code, $userCurrency);
            }

            $result = ChannelFlipkartComponent::ImportProductsFromXls($user_connection_id, $xls_file_path, $conversion_rate);
            if ( $result ) {
                ChannelFlipkartComponent::OrderImport($user_connection_id, $conversion_rate);
            }


            if ( $user_notify ) {

                $notif_type = "Flipkart";

//                $email_message = 'Failed, Your Flipkart channel products importing has failed.';
//                $notify_message = 'Your ' . $notif_type . ' channel products importing has been unfortunately failed, try again.';
                $email_message = 'Failed, Your Flipkart channel importing has failed.';
                $notify_message = 'Your ' . $notif_type . ' channel importing has been unfortunately failed, try again.';

                if ( $result ){
//                    $email_message = 'Success, Your Flipkart Channel importing has done.';
//                    $notify_message = 'Your ' . $notif_type . ' channel Products data has been successfully imported.';
                    $email_message = 'Success, Your Flipkart Channel importing has done.';
                    $notify_message = 'Your ' . $notif_type . ' channel data has been successfully imported.';
                }
                /* Send Email */
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Flipkart Import'),
                    'view' => '@common/mail/template',
                    'to' => $email,
                    'params' => [
                        'title' => 'Flipkart Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));
//                Yii::$app->commandBus->handle(new SendEmailCommand([
//                    'subject' => Yii::t('common', 'Flipkart Products Import'),
//                    'view' => '@common/mail/template',
//                    'to' => $email,
//                    'params' => [
//                        'title' => 'Flipkart Products Import',
//                        'content' => $email_message,
//                        'server' => env('SEVER_URL')
//                    ]
//                ]));

                //Drop-Down Notification for User

                $notification_model = new Notification();
                $notification_model->user_id = $user_id;
                $notification_model->title = $notif_type;
                $notification_model->message = $notify_message;
                $notification_model->save(false);

                $cronTaskName = 'FlipkartOrderCron-'.$user_connection_id;
                $cronTask = Crontask::findOne(['name' => $cronTaskName]);

                if ( empty($cronTask) ){

                    $cronTask = new Crontask();

                    $cronTask->name = $cronTaskName;
                    $cronTask->action = "channel-import/flipkart-order";
                    $cronParam = [$user_connection_id];
                    $cronTask->params = @json_encode($cronParam, JSON_UNESCAPED_UNICODE);
                    $cronTask->enabled = Crontask::ENABLED_YES;
                    $cronTask->completed = Crontask::COMPLETED_YES;
                    $cronTask->save(false);


                }


            }

            $flipkart_Connection->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $flipkart_Connection->save(true, ['import_status']);

            return true;
        }

        return false;

    }

    public function actionFlipkartOrder($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $flipkart_Connection = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($flipkart_Connection)) {

            $importUser = $flipkart_Connection->user;
            $channel_connection_details = $flipkart_Connection->userConnectionDetails;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'INR';

            $channel_currency_code = $channel_connection_details->currency;

            if ($channel_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($channel_currency_code, $userCurrency);
            }

            ChannelFlipkartComponent::OrderImport($user_connection_id, $conversion_rate);

            return true;
        }

        return false;



    }

    public function actionFlipkartParseOrders($user_connection_id, $csv_file_path, $user_notify=true) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $flipkart_Connection = UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($flipkart_Connection)) {

            $importUser = $flipkart_Connection->user;
            $channel_connection_details = $flipkart_Connection->userConnectionDetails;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'INR';

            $channel_currency_code = $channel_connection_details->currency;

            if ($channel_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($channel_currency_code, $userCurrency);
            }


            $result = ChannelFlipkartComponent::ImportOrdersFromCsv($user_connection_id, $csv_file_path, $conversion_rate);

            if ( $user_notify ){

                $notif_type = "Flipkart";

                $email_message = 'Failed, Your Flipkart channel orders importing has failed.';
                $notify_message = 'Your ' . $notif_type . ' channel orders importing has been unfortunately failed, try again.';

                if ( $result ){
                    $email_message = 'Success, Your Flipkart channel orders importing has done.';
                    $notify_message = 'Your ' . $notif_type . ' channel orders importing has been successfully imported.';
                }

                /* Send Email */

                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Flipkart Orders Import'),
                    'view' => '@common/mail/template',
                    'to' => $email,
                    'params' => [
                        'title' => 'Flipkart Orders Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                //Drop-Down Notification for User

                $notification_model = new Notification();
                $notification_model->user_id = $user_id;
                $notification_model->title = $notif_type;
                $notification_model->message = $notify_message;
                $notification_model->save(false);

            }


            $flipkart_Connection->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $flipkart_Connection->save(true, ['import_status']);

            return true;
        }

        return false;

    }

    public function actionAmazon($user_connection_id, $previous_connection_status = false) {
        try {
            $user_connection_row = UserConnection::find()->where(['id' => $user_connection_id])->one();
            if(empty($user_connection_row)) {
                throw new \Exception('Invalid id for a UserConnection table');
            }

            $user_id = $user_connection_row->user_id;
            $user_row = User::find()->where(['id' => $user_id])->one();
            if(empty($user_row)) {
                throw new \Exception('Invalid id for a User table');
            }

            $user_credential = $user_connection_row->connection_info;
            if(!isset($user_credential['market_id']) or empty($user_credential['market_id'])) {
                throw new \Exception('The market id is not set when importing amazon data');
            }

            if(!isset($user_credential['access_key']) or empty($user_credential['access_key'])) {
                throw new \Exception('The access key is not set when importing amazon data');
            }

            if(!isset($user_credential['secret_key']) or empty($user_credential['secret_key'])) {
                throw new \Exception('The secret key is not set when importing amazon data');
            }

            if(!isset($user_credential['marketplace_id']) or empty($user_credential['marketplace_id'])) {
                throw new \Exception('The marketplace id is not set when importing amazon data');
            }

            $user_connection_detail_instance = UserConnectionDetails::find()->where(['user_connection_id' => $user_connection_id])->one();
            if(empty($user_connection_detail_instance)) {
                throw new \Exception('Invalid id of the UserConnectionDetails table when import amazon data');
            }

            $country_code = $user_connection_detail_instance->country_code;
            $currency_code = $user_connection_detail_instance->currency;
            $store_name = $user_connection_detail_instance->store_name;

            ChannelAmazonComponent::importProducts(
                $user_credential,
                $user_id,
                $user_connection_id,
                $user_connection_detail_instance->country_code,
                $user_connection_detail_instance->currency
            );

            ChannelAmazonComponent::importOrders(
                $user_credential,
                $user_id,
                $user_connection_id,
                $user_connection_detail_instance->currency
            );

            if($previous_connection_status == false) {
                $email_message = 'Your '.$store_name.' channel import is successful!';
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('common', 'Amazon Import'),
                    'view' => '@common/mail/template',
                    'to' => $user_row->email,
                    'params' => [
                        'title' => 'Amazon Import',
                        'content' => $email_message,
                        'server' => env('SEVER_URL')
                    ]
                ]));

                $task = new Crontask();
                $task->name = 'AmazonCron-'.$user_connection_id;
                $task->action = 'channel-import/amazon';
                $param = [$user_connection_id, true];
                $task->params = @json_encode($param, JSON_UNESCAPED_UNICODE);
                $task->completed = Crontask::COMPLETED_YES;
                $task->save(false);

                $user_connection_row->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
                $user_connection_row->connected = UserConnection::CONNECTED_YES;
                $user_connection_row->save(true, ['import_status', 'connected']);
                $msg = 'Your '.$store_name.' channel data has been successfully imported.';
                Notification::saveMessage($user_id, $store_name." channel integration", $msg);
            }
        } catch(\Exception $e) {
            UserConnection::setFailStatus($user_connection_id);
            var_dump($e->getMessage());
        }
    }

    public function actionRakuten($user_connection_id) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $userConnection= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($userConnection)) {
            $importUser = $userConnection->user;
            $userConnectionDetail = $userConnection->userConnectionDetails;
            $channelName = $userConnectionDetail->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $currency_code = $userConnectionDetail->currency;

            $conversion_rate = 0;
            if ($currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency_code, $userCurrency);
            }
            $channelInfo = $userConnection->connection_info;

            $rakutenModel = new RakutenConnectionForm();
            $rakutenModel->sevice_secret = $channelInfo['sevice_secret'];
            $rakutenModel->license_key = $channelInfo['license_key'];

            $rakutenModel->getCategories($userConnection, $user_id);
            $rakutenModel->getProducts($userConnection, $user_id, $conversion_rate);

            /* Send Email */
            $email_message = 'Success, Your Rakuten channel data has been successfully imported.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Rakuten Import'),
                'view' => '@common/mail/template',
                'to' => $email,
                'params' => [
                    'title' => 'Rakuten Import',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Rakuten";

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = $email_message;
            $notification_model->save(false);


            $userConnection->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
            $userConnection->save(true, ['import_status']);

            return true;
        }
        return false;
    }
}