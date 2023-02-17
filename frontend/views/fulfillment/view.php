<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Channels;
use backend\models\Shipstation;
use yii\widgets\Breadcrumbs;
use yii\backend\Fulfillment;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ChannelsSerach */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Fulfillment';
$this->params['breadcrumbs'][] = $this->title;
$user_id=Yii::$app->user->identity->id;

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

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading">
                <div class="tools">
                    <a href="<?php //echo $new_basepath ?>/export-user"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="channels_view_table" class="table table-striped table-hover table-fw-widget">
                    <thead>

                        <tr>
                            <th>Fulfillment</th>
                            <!--<th>Products in Fulfillment</th>
                            <th>Orders Pending in Fulfillment</th>!-->
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $Fulfillment=Fulfillment::find()->Where(['elliot_user_id'=>$user_id])->all();
                            if(!empty($Fulfillment)):
                                $status=$Fulfillment->connected;
                            else :
                                $status='No';
                            endif;
                            
                            
                        ?>
                        <?php foreach ($Fulfillment as $wh) : ?>
                            <tr class="odd gradeX">
                                <td><?=$Fulfillment->name; ?></td>
                                <!--<td></td>
                                <td></td>-->
                            <?php if($status=='Yes'): ?>
                            <td><a href="/shipstation">Connected</a></td>
                            <?php else: ?>
                            <td><a href="/shipstation">Get Connected</a></td>   
                            <?php endif; ?>
                            </tr>
                      <?php  endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



