<?php
namespace console\controllers;

use common\commands\RunTaskCommand;
use common\models\Connection;
use common\models\Crontask;
use common\models\Fulfillment;
use common\models\FulfillmentList;
use common\models\UserConnection;
use frontend\components\ShipstationComponent;
use Yii;
use yii\console\Controller;



class CronTaskController extends Controller
{

    public function actionFulfillShipstation(){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $shipstation_fulfill = FulfillmentList::findOne(['name' => 'ShipStation']);

        if ( !empty($shipstation_fulfill) ) {

            $fulfillment_list_id = $shipstation_fulfill->id;

            $shipstation_userConnection = UserConnection::findAll(['fulfillment_list_id' => $fulfillment_list_id ]);

            if ( !empty($shipstation_userConnection) ) {
                foreach ($shipstation_userConnection as $each_userConnection) {

                    $user_id = $each_userConnection->user_id;
                    $user_connection_id = $each_userConnection->id;

                    $user_fulfillment_connection = Fulfillment::findOne([
                        'user_id' => $user_id,
                        'fulfillment_list_id' => $fulfillment_list_id,
                        'connected' => Fulfillment::CONNECTED_YES
                    ]);

                    if ( !empty($user_fulfillment_connection) ){
                        $user_fulfillment_id = $user_fulfillment_connection->id;

                        ShipstationComponent::sendOrder($user_connection_id, $user_fulfillment_id);

                    }
                }

                return true;
            }



        }

        return false;

    }


    public function actionStoreVtex(){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $Vtex_Connection = Connection::findOne(['name' => 'VTEX']);

        if ( !empty($Vtex_Connection) ) {

            $connection_id = $Vtex_Connection->id;

            $vtex_connections = UserConnection::findAll(['connection_id' => $connection_id ]);

            if ( !empty($vtex_connections) ) {
                foreach ($vtex_connections as $each_userConnection) {

                    $user_connection_id = $each_userConnection->id;

                    Yii::$app->runAction('store-import/vtex', [$user_connection_id, false]);
                }

                return true;
            }



        }

        return false;

    }


    public function actionChannelJet(){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $JetConnection = Connection::findOne(['name' => 'Jet']);

        if ( !empty($JetConnection) ) {

            $connection_id = $JetConnection->id;

            $jetConnections = UserConnection::findAll(['connection_id' => $connection_id ]);

            if ( !empty($jetConnections) ) {
                foreach ($jetConnections as $each_userConnection) {

                    $user_connection_id = $each_userConnection->id;

                    Yii::$app->runAction('channel-import/jet', [$user_connection_id, false]);
                }

                return true;
            }



        }

        return false;

    }

    public function actionChannelFlipkartOrder(){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $FlipkartConnection = Connection::findOne(['name' => 'Flipkart']);

        if ( !empty($FlipkartConnection) ) {

            $connection_id = $FlipkartConnection->id;

            $FlipkartConnections = UserConnection::findAll(['connection_id' => $connection_id ]);

            if ( !empty($FlipkartConnections) ) {
                foreach ($FlipkartConnections as $each_userConnection) {

                    $user_connection_id = $each_userConnection->id;

                    Yii::$app->runAction('channel-import/flipkart-order', [$user_connection_id]);
                }

                return true;
            }



        }

        return false;

    }


    public function actionRunJob(){

        $crontasks = Crontask::find()->enabled()->completed()->all();

        if ( !empty($crontasks) ){

            foreach ($crontasks as $_task){


                $run_id = $_task->id;
                $run_action = $_task->action;
                $run_params = $_task->params;

                $_task->completed = Crontask::COMPLETED_NO;
                $_task->save(true, ['completed']);
                Yii::$app->commandBus->handle(new RunTaskCommand([
                    'taskId' => $run_id,
                    'action' => $run_action,
                    'params' => $run_params
                ]));



            }

        }

    }
}