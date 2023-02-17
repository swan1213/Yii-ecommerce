<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Channels;
use backend\models\Shipstation;
use yii\widgets\Breadcrumbs;
use common\models\Fulfillment;
use common\models\FulfillmentList;


$this->title = 'FulFillment';
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

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading">
                <div class="tools">
                    <a href="<?php //echo $new_basepath  ?>/export-user"><span class="icon mdi mdi-download"></span></span></a>
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
                        <?php $Fulfillment = Fulfillment::find()->Where(['user_id' => $user_id])->all(); ?>
                        <?php
						$fullfilledArray = array();
						 foreach ($Fulfillment as $wh){
							 $fullfilledArray[] = $wh->name;
						 }
						 $fullfilledArray = array_unique($fullfilledArray);
						 //echo '<pre>'; print_r($fullfilledArray);
						$FulFillmentList = FulfillmentList::find()->all();
						foreach($FulFillmentList as $fulfillment){ ?>
							<tr class="odd gradeX">
                                <td><?php echo $fulfillment->name; ?></td>
                                <td><a href="<?php echo $fulfillment->fulfillment_link; ?>"><?php if(in_array($fulfillment->name,$fullfilledArray)) {  echo 'Connected'; } else { echo 'Get Connected'; } ?></a></td>
                            </tr>
						<?php }
                       ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



