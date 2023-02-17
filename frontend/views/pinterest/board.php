<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\Category;
use common\models\ProductCategory;

$this->title = 'Pinterest Feed';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => ['/channels']];
$this->params['breadcrumbs'][] = ['label' => 'Connected Channels', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = 'Pinterest Board';

?>

<div class="page-head">
    <h2 class="page-head-title">Pinterest Board</h2>
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
                        <li data-step="1" class="active">Pinterest Board<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <div data-step="1" class="active">
                            <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-pinterest-board-create">Board Create</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('@web/js/pinterest/pinterest.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
