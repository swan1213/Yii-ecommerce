<?php
use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Content;
use yii\widgets\Breadcrumbs;


$this->title = 'Edit Conetnt'. $model->id;
$this->params['breadcrumbs'][] = $this->title;
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

<div class="page_update">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">
                    <form action="" method="post" style="border-radius: 0px;" class="form-horizontal group-border-dashed" novalidate="">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Page Title</label>
                            <div id="page_title_update" class="col-sm-9 ">
                              <input  name="page_title_update"  type="text" value="<?php echo $model->title; ?>" class="form-control content_validate" placeholder="Please Enter Value" value="">
                              <input type="hidden" name="update_page_ID" id="update_page_ID" value="<?php echo $model->id; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Page Description</label>
                            <div id="content_page_description" class="col-sm-9 ">
                               <div id="page_description_update">
                                   <?php echo $model->description; ?>
                               </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-11">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="Content_update_submit" value="Update">
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>