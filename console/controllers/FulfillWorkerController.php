<?php
namespace console\controllers;

use common\models\Fulfillment;
use common\models\FulfillmentList;
use common\models\Notification;
use common\models\Order;
use common\models\Product;
use common\models\ProductImage;
use common\models\UserConnection;
use common\models\UserProfile;
use frontend\components\CustomFunction;
use frontend\components\ShipstationComponent;
use frontend\controllers\SfexpressController;
use frontend\controllers\ShipheroController;
use Yii;
use yii\console\Controller;
use common\commands\SendEmailCommand;
use frontend\components\ShipstationClient;
use frontend\controllers\TplCentralController;
use frontend\components\ShiphawkComponent;


class FulfillWorkerController extends Controller
{

    public function actionIndex(){
        echo "This is fulfill-worker.";
    }

    public function actionShipstationSendOrder($user_fulfillment_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $shipstationConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);

        if (!empty($shipstationConection)) {

            $connection_user = $shipstationConection->user;
            $user_email = $connection_user->email;
            $user_id = $connection_user->id;
            $fulfillment_list_id = $shipstationConection->fulfillment_list_id;

            $shipstation_userConnection = UserConnection::findAll(['user_id' => $user_id, 'fulfillment_list_id' => $fulfillment_list_id ]);

            if ( !empty($shipstation_userConnection) ) {
                foreach ($shipstation_userConnection as $each_userConnection) {

                    $user_connection_id = $each_userConnection->id;

                    ShipstationComponent::sendOrder($user_connection_id, $user_fulfillment_id);
                }
            }

            /* Send Email */
            $email_message = 'Success, Your Orders sent to ShipStation.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Orders Sent'),
                'view' => '@common/mail/template',
                'to' => $user_email,
                'params' => [
                    'title' => 'Orders Sent',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "ShipStation";

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your Orders data sent to ' . $notif_type . ' successfully.';
            $notification_model->save(false);


            return true;
        }

        return false;


    }

    public function actionTplcentral()
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        TplCentralController::fullfill();
    }

    public function actionSfexpressSendOrder($user_connection_id, $user_notify = true){

        $user_connection = UserConnection::find()->where(['id' => $user_connection_id])->one();
        if (!empty($user_connection)) {

            $connection_user = $user_connection->user;
            $user_email = $connection_user->email;
            $user_id = $connection_user->id;

            if (SfexpressController::sendOrder($user_connection_id)) {
                if ($user_notify) {
                    $email_message = 'Success, Your Orders sent to Sf express.';
                    Yii::$app->commandBus->handle(new SendEmailCommand([
                        'subject' => Yii::t('common', 'Orders Sent'),
                        'view' => '@common/mail/template',
                        'to' => $user_email,
                        'params' => [
                            'title' => 'Orders Sent',
                            'content' => $email_message,
                            'server' => env('SEVER_URL')
                        ]
                    ]));

                    //Drop-Down Notification for User
                    $notif_type = "Sf express";

                    $notification_model = new Notification();
                    $notification_model->user_id = $user_id;
                    $notification_model->title = $notif_type;
                    $notification_model->message = 'Your Orders data sent to ' . $notif_type . ' successfully.';
                    $notification_model->save(false);
                }
            }
        }
    }

    public function actionShipheroSendOrder($user_fulfillment_id, $user_notify = true){

        $shipstationConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);
        if (!empty($shipstationConection)) {

            $connection_user = $shipstationConection->user;
            $user_email = $connection_user->email;
            $user_id = $connection_user->id;

            if (ShipheroController::sendOrder($user_fulfillment_id)) {
                if ($user_notify) {
                    $email_message = 'Success, Your Orders sent to Shiphero.';
                    Yii::$app->commandBus->handle(new SendEmailCommand([
                        'subject' => Yii::t('common', 'Orders Sent'),
                        'view' => '@common/mail/template',
                        'to' => $user_email,
                        'params' => [
                            'title' => 'Orders Sent',
                            'content' => $email_message,
                            'server' => env('SEVER_URL')
                        ]
                    ]));

                //Drop-Down Notification for User
                    $notif_type = "Shiphero";

                    $notification_model = new Notification();
                    $notification_model->user_id = $user_id;
                    $notification_model->title = $notif_type;
                    $notification_model->message = 'Your Orders data sent to ' . $notif_type . ' successfully.';
                    $notification_model->save(false);
                }
            }
        }
    }

    public function actionShiphawk($user_id, $fulfillment_list_id) {
        try {
            $fulfillment_row = Fulfillment::find()->where([
                'fulfillment_list_id' => $fulfillment_list_id, 
                'user_id' => $user_id
            ])->one();
            $connection_info = $fulfillment_row->connection_info;

            if(!isset($connection_info['product_key']) or empty($connection_info['product_key'])) {
                throw new \Exception('The product key is not set.');
            }

            $shiphawk_instance = new ShiphawkComponent(
                $user_id,
                $connection_info['product_key'],
                $fulfillment_list_id
            );
            $shiphawk_instance->sendOrders();

            $connection_user = $fulfillment_row->user;
            $user_email = $connection_user->email;
            $email_message = 'Success, Your Orders sent to Shiphawk.';
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'subject' => Yii::t('common', 'Orders Sent'),
                'view' => '@common/mail/template',
                'to' => $user_email,
                'params' => [
                    'title' => 'Orders Sent',
                    'content' => $email_message,
                    'server' => env('SEVER_URL')
                ]
            ]));

            //Drop-Down Notification for User
            $notif_type = "Shiphawk";

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = 'Your Orders data sent to ' . $notif_type . ' successfully.';
            $notification_model->save(false);
        } catch (\Exception $e) {
            $notif_type = "Shiphawk";

            $notification_model = new Notification();
            $notification_model->user_id = $user_id;
            $notification_model->title = $notif_type;
            $notification_model->message = $e->getMessage();
            $notification_model->save(false);
        }
    }
}