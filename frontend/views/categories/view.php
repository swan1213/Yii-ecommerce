<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Categories */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="categories-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'category_ID',
            'category_name',
            'parent_category_ID',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
<div class="user-info-list panel panel-default">
    <div class="panel-body">
        <div class="table-responsive">
            <table id="user" style="clear: both" class="table table-striped table-borderless">
                <tbody>
                    <tr>
                        <td width="35%">Category name </td>
                        <td width="65%">
                            <a id="add_cat_name" href="javascript:" data-type="text" data-title="Enter Category Name"><?php echo isset($categories)?$categories:''; ?></a>
                            <span id="span_err_add_cat_name" style="color: red;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td width="35%">Parent Category</td>
                        <td width="65%">
                            <a id="add_parent_cat_name" href="javascript:" data-title="Select Parent Category" data-type="select" data-value="Bath" data-pk="1" class="editable editable-click" data-source="get-parentcat"><?php echo isset($parent_cat)?$parent_cat:''; ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <button class="btn btn-space btn-primary" onclick="updatecategory()">Save</button>
        </div>
    </div>
</div>