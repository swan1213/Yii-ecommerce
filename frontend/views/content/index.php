<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Content;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ShipstationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Content Pages';
$this->params['breadcrumbs'][] = ['label' => 'Content', 'url' => ['/content/index']];
$this->params['breadcrumbs'][] = 'View All';
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
<div class="contentLoader be-loading">
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading">
                <div class="tools">
                    <a href="<?php //echo $new_basepath  ?>javascript:void(0)"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                        <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                <table id="content_view_table" class="table table-striped table-hover table-fw-widget">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                     
                            </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!--<div>
     <a href="/shipstation/instruction"><h4>Instructions for Setup ShipStation </h4></a>
</div>-->
<!--<style>
    .main-content{
        padding:0px;
    }
</style>-->
<!--<div class="shipstation-index">
   <iframe src="https://s86.co/htmlbuilder/builder_front/build/" class="ship-iframe" id="" name="<?php //echo time(); ?>">
   </iframe>

</div>-->
