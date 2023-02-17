<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Create Page';
$this->params['breadcrumbs'][] = ['label' => 'Content', 'url' => ['/content/index']];
$this->params['breadcrumbs'][] = 'Add New';

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

<div class="page_create">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">
                    <form action="" method="post" style="border-radius: 0px;" class="form-horizontal group-border-dashed" novalidate="">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Page Title</label>
                            <div id="page_title" class="col-sm-9 ">
                                <input  name="content_page_title"  type="text" class="form-control content_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Page Description</label>
                            <div id="content_page_description" class="col-sm-9 ">
                               <div id="page_description"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-11">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="Content_submit" value="Create">
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    
<!--  <div class="table-responsive">
       <table id="create_page_form_table" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                    <tr>
                                   <td width="35%">Page Title</td>
                                   <td width="65%">
                                       <input type="text" name="page_title" value="">
                                   </td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Description</td>
                                        <td width="65%">
                                            <div id="page_description"></div>
                                        </td>
                                    </tr>
                                </tbody>
       </table>
      <div class="up_btn_div">
            <button class="btn btn-space btn-primary">Create</button>
        </div>
  </div>-->
</div>