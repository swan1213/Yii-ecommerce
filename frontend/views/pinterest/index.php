<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\Category;
use common\models\ProductCategory;

$this->title = 'Add Your Pinterest';
$this->params['breadcrumbs'][] = 'Add Your Pinterest';

?>
<style type="text/css">
    .googleshopping-connection-container,
    .googleshopping-feed-container {
        margin: 0;
    }

    #create_google_feed_modal .modal-footer {
        padding: 0 20px;
    }
</style>
<div class="page-head">
    <h2 class="page-head-title">Add Your Pinterest</h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [], ]);
        ?>
    </ol>
</div>

<input type="hidden" id="p_token" value=""/>
<div class="main-content container-fluid">
    <div class="row wizard-row">
        <div class="col-md-12 fuelux wechat12">
            <div class="block-wizard panel panel-default">
                <div id="wizard1" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active">Pinterest Connection<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <div data-step="1" class="step-pane active">
                            <div class="form-group">
                                <button class="btn btn-space btn-primary" onclick="location.href='/pinterest/create'">Create New Board</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(!empty($board_models)){ ?>
                    <div style="padding: 1px 16px;">
                        <div class="panel panel-default panel-table" >
                            <div class="panel-body">
                                <table id="fbfeeds_tbl_view" class="table table-striped table-hover table-fw-widget">
                                    <thead>

                                    <tr>
                                        <th>Board Name</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($board_models as $board_model) {
                                        ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php
                                                echo $board_model->name;
                                                ?>
                                            </td>
                                            <td class="actions">
                                                <a href="/pinterest/update?id=<?=$board_model->id?>" class="icon"><i class="mdi mdi-edit"></i></a>
                                                <a href="/pinterest/delete?id=<?=$board_model->id?>" class="icon" style="margin-left: 8px;"><i class="mdi mdi-delete"></i></a>
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
</div>
<div id="pinterest_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close pinterest_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="pinterest_ajax_header_msg">Success!</h3>
                    <p id="pinterest_ajax_msg">Success! Your pinterest has been connected successfully</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default pinterest_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in pinterest_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close pinterest_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='pinterest_ajax_header_error_msg'>Error</h3>
                    <p id="pinterest_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default pinterest_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<?php
    $this->registerJsFile('@web/js/pinterest/pinterest.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
