<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Connected Channels';
$this->params['breadcrumbs'][] = $this->title;


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
        <div class="panel panel-default panel-table">
            <div class="panel-heading"><?php echo $this->title; ?>
                <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
            </div>
            <div class="panel-body">
                <table id="products_table" class="table table-striped table-hover table-fw-widget">
                    <thead>
                        <tr>
                            <th>Store Type</th>
                            <th>Store Hash</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty(user_connections)) :
                            
                          foreach (user_connections as $connection) :
                            $st_id = $connection->id;

                            ?>
                            <tr class="store_row">
                                <td><a href="#"></a></td>
                                <td><a href="#"></a></td>
                                <td>Connected</td>
                            </tr>
                            <?php
                          endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>





