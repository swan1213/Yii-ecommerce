<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Categories */

$this->title = 'Add Categories';
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
echo "<pre>";
print_r($categories);
echo "</pre>";
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
                            <a id="add_parent_cat_name" href="javascript:" data-title="Select Parent Category" data-type="select" data-value="" data-pk="1" class="editable editable-click" data-source="get-parentcat"><?php echo isset($parent_cat)?$parent_cat:''; ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <button class="btn btn-space btn-primary" onclick="addcategories()">Save</button>
        </div>
    </div>
</div>

