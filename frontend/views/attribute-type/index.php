<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\AttributionType;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Attribute Types';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/attribute-type']];
$this->params['breadcrumbs'][] = 'View All';
$id = Yii::$app->user->identity->id;
$attributes_type_data = AttributionType::find()->Where(['user_id' => $id])->all();
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
    <?= Html::a('Add Attribute Type', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table">
            <div class="panel-heading"><?php echo $this->title; ?>
                <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
            </div>
            <div class="panel-body">
                <table id="variations_table" class="table table-striped table-hover table-fw-widget">
                    <thead>
                        <tr>
                            <th>Attribute Type</th>
                            <th>Label</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($attributes_type_data as $attribute_type) :
                            ?>
                        <tr>
                            <td><a href="attribute-type/update?id=<?= $attribute_type->id;  ?>"><?= $attribute_type->name;?></a></td>
                            <td><?= $attribute_type->label;?></td>
                            <td><?= $attribute_type->description;?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>






