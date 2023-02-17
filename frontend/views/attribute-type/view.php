<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = 'Attribute Type : ' . ucfirst($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = ['label' => 'Attribute Types', 'url' => ['/attribute-type']];
$this->params['breadcrumbs'][] = ucfirst($model->name);
?>
<div class="attribute-type-view">
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
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <tr>
                                    <td width="35%">Attribute Type Name </td>
                                    <td width="65%">
                                        <a id="update-attr-type-name" href="javascript:" data-type="text" data-value="<?php echo $model->name; ?>" data-title="Please Enter Value"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%">Label</td>
                                    <td width="65%">
                                        <a id="update-attr-type-label" href="javascript:" data-type="text" data-value="<?php echo $model->label; ?>" data-title="Please Enter Value"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%">Description</td>
                                    <td width="65%">
                                        <div id="update-attr-type-desc"><?= $model->description; ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a class="btn btn-space btn-default" href="/attribute-type">Cancel</a> 
                        <button class="btn btn-space btn-primary" onclick="updateattr_type(<?= $model->id ?>)">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
