<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Connection;
use yii\widgets\Breadcrumbs;
use common\models\UserConnection;
use common\models\Order;
use frontend\components\Helpers;

$this->title = 'Facebook Feeds';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/channels']];
$this->params['breadcrumbs'][] = 'Facebook Feeds';

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
    <div class="row wizard-row">
        <div class="col-md-12 fuelux wechat12">
            <div class="block-wizard panel panel-default">
                <div id="wizard1" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active" >Pinterest Connection<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <div data-step="1" class="active">
                            <h4>Instructions: </h4>
                            <ol>
                                <li>Firstly create a catalog in your Facebook Business Account.</li>
                                <li>Then Add a Product feed. You will have to add Url <b>https://elliot.global/feed/facebook?u_id=XXXXXX</b> in Feed Url.</li>
                                Please see <a target="_blank" href="https://www.facebook.com/business/help/1397294963910848">Follow</a> this link for futher detail
                            </ol>
                            <button class="btn btn-space btn-primary" style="float: right"  onclick="location.href='/facebook/create'">Create New Feed</button>
                        </div>
                    </div>
                </div>
                <?php if(!empty($userfeed_models)){ ?>
                    <div style="padding: 1px 16px;">
                        <div class="panel panel-default panel-table" >
                            <div class="panel-body">
                                <table id="fbfeeds_tbl_view" class="table table-striped table-hover table-fw-widget">
                                    <thead>

                                    <tr>
                                        <th>Feed Name</th>
                                        <th>Feed Url</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($userfeed_models as $feed_model) {
                                        ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php
                                                echo $feed_model->name;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                echo $feed_model->link;
                                                ?>
                                            </td>
                                            <td class="actions">
                                                <a href="/facebook/update?id=<?=$feed_model->id?>" class="icon"><i class="mdi mdi-edit"></i></a>
                                                <a href="/facebook/delete?id=<?=$feed_model->id?>" class="icon" style="margin-left: 8px;"><i class="mdi mdi-delete"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>



