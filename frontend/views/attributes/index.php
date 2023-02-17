<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Attribution;
use common\models\AttributionType;


$this->title = 'Product Attributes';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = 'View All';
//$this->title
$id = Yii::$app->user->identity->id;
$attributes_data = Attribution::find()->Where(['user_id' => $id])->all();
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
    <?= Html::a('Add Attribute', ['create'], ['class' => 'btn btn-primary']) ?>
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
                            <th>Attribute Name</th>
                            <th>Attribute Label</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($attributes_data as $attributes) :
                          ?>
                          <tr>
                              <td><?= $attributes->name; ?></td>
                              <td><?= $attributes->label; ?></td>
                              <td class="center">
                                  <?=
                                  Html::a('', ['delete', 'class' => "icon", 'id' => $attributes->id], [
                                      'class' => 'mdi mdi-delete',
                                      'data' => [
                                          'confirm' => 'Are you sure you want to delete this item?',
                                          'method' => 'post',
                                      ],
                                  ]);
                                  ?>
                                  <?php
                                  echo '&nbsp;&nbsp;&nbsp;&nbsp;'.Html::a('', ['attributes/update/', 'id' => $attributes->id], [
                                          // Url::to(['user/delete', 'id' => $user_value->id]),
                                          'class' => 'mdi mdi-edit',
                                      ]);
                                  ?>
                              </td>
                          </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>






