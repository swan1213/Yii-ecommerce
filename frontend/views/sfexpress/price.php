<?php

use common\models\Fulfillment;
use common\models\SfexpressRate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;

$this->title = 'Your SF Express fulfillment';
$this->params['breadcrumbs'][] = 'SF Express';
$checkConnection = '';
$Fulfillment_check = Fulfillment::find()->where(['name' => 'SF Express'])->one();
//echo  '<pre>'; print_r($Fulfillment_check); echo '</pre>';
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
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
				<li data-step="1" class="active">Price <span class="chevron"></span></li>
                </ul>
                <div class="step-content">
                    <div data-step="1" class="step-pane active">
                        <div class="panel panel-default">
                            <div class="panel-heading">Price List</div>
                            <div class="tab-container">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#hongkong" data-toggle="tab">Hong Kong</a></li>
                                    <li><a href="#Macau" data-toggle="tab">Macao</a></li>
                                    <li><a href="#Taiwan" data-toggle="tab">Taiwan</a></li>
                                    <li><a href="#MainlandChina" data-toggle="tab">Mainland China</a></li>
                                    <li><a href="#Singapore" data-toggle="tab">Singapore</a></li>
                                    <li><a href="#Malaysia" data-toggle="tab">Malaysia</a></li>
                                    <li><a href="#Japan" data-toggle="tab">Japan</a></li>
                                    <li><a href="#SouthKorea" data-toggle="tab">South Korea</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="hongkong" class="tab-pane active cont">
                                        <h4>Hong Kong</h4>
                                        <?php
						                    $SfexpressRate = SfexpressRate::find()->all();
						                ?>
						                <div class="panel-body">
                                            <table id="tablesf1" class="table table-striped table-hover table-fw-widget">
                                                <thead>
                                                    <tr>
                                                        <th>Weight(lbs)</th>
                                                        <th>USD</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    foreach($SfexpressRate as $sfpricedata){
                                                ?>
                                                    <tr class="odd gradeX">
                                                        <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                        <td class="center"><?php $hongkong =   $sfpricedata->hongkong;
                                                        $hongkongprice = ($hongkong*0.1) + $hongkong ;
                                                        echo number_format($hongkongprice,2);
                                                        ?></td>
                                                    </tr>
						                        <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="Macau" class="tab-pane cont">
					                    <h4>Macau</h4>
						                <div class="panel-body">
                                            <table id="tablesf2" class="table table-striped table-hover table-fw-widget">
                                                <thead>
                                                    <tr>
                                                        <th>Weight(lbs)</th>
                                                        <th>USD</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    foreach($SfexpressRate as $sfpricedata){
                                                ?>
                                                    <tr class="odd gradeX">
                                                        <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>

                                                        <td class="center"><?php $macau = $sfpricedata->macau;
                                                        $macauprice =  ($macau*0.1) + $macau;
                                                        echo number_format($macauprice,2);
                                                        ?></td>
                                                    </tr>
						                        <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="Taiwan" class="tab-pane">
					                    <h4>Taiwan</h4>
						                <div class="panel-body">
                                            <table id="tablesf3" class="table table-striped table-hover table-fw-widget">
                                                <thead>
                                                    <tr>
                                                        <th>Weight(lbs)</th>
                                                        <th>USD</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    foreach($SfexpressRate as $sfpricedata){
                                                ?>
                                                    <tr class="odd gradeX">
                                                        <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                        <td class="center"><?php $taiwan =   $sfpricedata->taiwan;
                                                        $taiwanprice =  ($taiwan*0.1) + $taiwan;
                                                            echo number_format($taiwanprice,2);
                                                        ?></td>
                                                    </tr>
						                        <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
					                <div id="MainlandChina" class="tab-pane">
					                    <h4>Mainland China</h4>
						                <div class="panel-body">
                                            <table id="tablesf4" class="table table-striped table-hover table-fw-widget">
                                                <thead>
                                                    <tr>
                                                        <th>Weight(lbs)</th>
                                                        <th>USD</th>
                                                    </tr>
                                                </thead>
                                            <tbody>
                                            <?php
                                                foreach($SfexpressRate as $sfpricedata){
                                            ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                    <td class="center"><?php $mainlandchina =   $sfpricedata->mainlandchina;
                                                    $mainlandchinaprice =  ($mainlandchina*0.1) + $mainlandchina;
                                                        echo number_format($mainlandchinaprice,2);
                                                    ?></td>
                                                </tr>
						                    <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
					            <div id="Singapore" class="tab-pane">
					                <h4>Singapore</h4>
						            <div class="panel-body">
                                        <table id="tablesf5" class="table table-striped table-hover table-fw-widget">
                                            <thead>
                                                <tr>
                                                    <th>Weight(lbs)</th>
                                                    <th>USD</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                foreach($SfexpressRate as $sfpricedata){
                                            ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                    <td class="center"><?php $singapore =   $sfpricedata->singapore;
                                                    $singaporeprice =  ($singapore*0.1) + $singapore;
                                                    echo number_format($singaporeprice,2);
                                                    ?></td>
                                                </tr>
						                    <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
					            <div id="Malaysia" class="tab-pane">
					                <h4>Malaysia</h4>
						            <div class="panel-body">
                                        <table id="tablesf6" class="table table-striped table-hover table-fw-widget">
                                            <thead>
                                                <tr>
                                                    <th>Weight(lbs)</th>
                                                    <th>USD</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                foreach($SfexpressRate as $sfpricedata){
                                            ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                    <td class="center"><?php $malaysia = $sfpricedata->malaysia;
                                                    $malaysiaprice =  ($malaysia*0.1) + $malaysia;
                                                    echo number_format($malaysiaprice,2);
                                                    ?></td>
                                                </tr>
						                    <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
					            <div id="Japan" class="tab-pane">
					                <h4>Japan</h4>
                                    <div class="panel-body">
                                    <table id="tablesf7" class="table table-striped table-hover table-fw-widget">
                                        <thead>
                                            <tr>
                                                <th>Weight(lbs)</th>
                                                <th>USD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            foreach($SfexpressRate as $sfpricedata){
                                        ?>
                                            <tr class="odd gradeX">
                                                <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>
                                                <td class="center"><?php $japan =   $sfpricedata->japan;
                                                $japanprice =  ($japan*0.1) + $japan;
                                                echo number_format($japanprice,2);
						                        ?></td>
                                            </tr>
						                <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
					        <div id="SouthKorea" class="tab-pane">
					            <h4>South Korea</h4>
						        <div class="panel-body">
                                    <table id="tablesf8" class="table table-striped table-hover table-fw-widget">
                                        <thead>
                                            <tr>
                                                <th>Weight(lbs)</th>
                                                <th>USD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            foreach($SfexpressRate as $sfpricedata){
                                        ?>
                                            <tr class="odd gradeX">
                                                <td class="center"><?php echo $weight = $sfpricedata->weight;  ?></td>

                                                <td class="center"><?php $southkorea =   $sfpricedata->southkorea;
                                                $southkoreaprice =  ($southkorea*0.1) + $southkorea;
                                                echo number_format($southkoreaprice,2);

                                                ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>
<div id="SfExpress_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close SfExpress_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="SfExpress_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <a href="sfexpress">Next</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in SfExpress_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close SfExpress_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='ajax_header_error_msg'></h3>
                    <p id="SfExpress_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                      <button type="button" data-dismiss="modal" class="btn btn-space btn-default SfExpress_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
