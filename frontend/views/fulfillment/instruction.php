<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ShipstationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Instructions';
$this->params['breadcrumbs'][] = ['label' => 'Fulfillment', 'url' => ['/warehouse']];
$this->params['breadcrumbs'][] = $this->title;

$user_id = Yii::$app->user->identity->id;
?>


<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>


<!--    <div class="be-content">-->
<div class="main-content container-fluid">
    <div class="row">
        <div class="col-xs-12 col-md-7 col-md-offset-2">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-heading panel-heading-divider">
                    <span class="title" style="float: left; width: 100%; float: left; padding: 1px;">ShipStation - Instructions for Connecting to Elliot</span>
                    <span class="title panel-heading" style="margin-left: 0px;">Creating the Custom Store and Connection</span>
                </div>
            
                <div class="panel-body">
                    <ol>
                        <li><h4>Log in to your ShipStation account.</h4></li>
                        <li><h4>From the Welcome page, click Connect a channel.</h4></li>
                        <li><h4>Using the Search box, search for Custom Store, and then click the Custom Store icon.</h4>
                        </li>
                        <li><h4>In the Connect your Custom Store box (enter the appropriate data as follows:</h4>

                            <table class="table table-condensed table-hover table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>URL to Custom XML Page</td>
                                        <?php
                                            $url = 'fulfillment/genratexml?shipid='.$user_id;
                                        ?>

                                        <td><?php echo $url; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Username</td>
                                        <td>Shipstaton Username</td>
                                    </tr>
                                    <tr>
                                        <td>Password</td>
                                        <td>Shipstaton password</td>
                                    </tr>
                                    <tr>
                                        <td>Unpaid Status</td>
                                        <td>Incomplete</td>
                                    </tr>
                                    <tr>
                                        <td>Paid Status</td>
                                        <td>Completed</td>
                                    </tr>
                                    <tr>
                                        <td>Shipped Status</td>
                                        <td>Shipped</td>
                                    </tr>
                                    <tr>
                                        <td>Cancelled Status</td>
                                        <td>Cancelled</td>
                                    </tr>
                                    <tr>
                                        <td>On-Hold Status</td>
                                        <td>On Hold</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                        <li><h4>Click Test Connection. ShipStation should respond with a confirmation</h4></li>
                        <li><h4>Click Connect to save the configuration and close the dialogue box.</h4></li>

                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
