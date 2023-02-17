<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'API Information';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = $this->title;


$endpointUrl = env('SEVER_URL')."/api/v1/";
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
                <table id="api_client_table" class="table table-striped table-hover table-fw-widget">
                    <thead>
                    <tr>
                        <th>Access Token</th>
                        <th>End Point</th>
                        <th>Authentication</th>

                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><a href="#"><?= $dataProvider->access_token; ?></a></td>
                            <td><a href="#">
                                    <?php
                                        echo  $endpointUrl;
                                    ?>
                                </a>
                            </td>
                            <td>
                                Bearer
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>






