<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Breadcrumbs;
use backend\models\AttributeType;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = 'Attribute : ' . ucfirst($model->attribute_name);
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = ucfirst($model->attribute_name);
$attr_type_obj = AttributeType::find()->where(['elliot_user_id' => Yii::$app->user->identity->id, 'attribute_type_id' => $model->attribute_type])->one();
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
                        <table id="attr" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <tr>
                                    <td width="35%">Attribute Name </td>
                                    <td width="65%">
                                        <a id="update-attr-name" href="javascript:" data-type="text" data-value="<?php echo $model->attribute_name; ?>" data-title="Please Enter Value"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%">Attribute Type</td>
                                    <td width="65%">
                                        <a id="update-attr-type" href="javascript:" data-title="Please Select" data-type="select" data-value="<?= ucfirst($attr_type_obj->attribute_type_name); ?>" data-pk="1" class="editable editable-click" data-source="/attributes/get-attr_type"><?= ucfirst($attr_type_obj->attribute_type_name); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%">Label</td>
                                    <td width="65%">
                                        <a id="update-attr-label" href="javascript:" data-type="text" data-value="<?php echo $model->attribute_label; ?>" data-title="Please Enter Value"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="35%">Description</td>
                                    <td width="65%">
                                        <div id="update-attr-desc"><?= $model->attribute_description; ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a class="btn btn-space btn-default" href="/attributes">Cancel</a> 
                        <button class="btn btn-space btn-primary" onclick="updateattr(<?= $model->attribute_id ?>)">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
