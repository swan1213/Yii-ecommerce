<?php

use common\models\Category;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\UserConnection;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\Variation;
use common\models\VariationItem;
use common\models\Attribution;
use common\models\User;

$this->title = 'Product Create';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = 'Add New';
$schedule_date1 = date('d/m/Y', time());
$user_id = Yii::$app->user->identity->id;
if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
    $user_id = Yii::$app->user->identity->parent_id;
$product_variants = Variation::find()->where(['user_id' => $user_id])->all();
$categories = Category::find()->where(['user_id' => $user_id])->all();

$attributes_data = Attribution::find()->Where(['user_id' => $user_id])->all();
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
            <div id="wizard1" class="wizard wizard-ux">
                <input type="hidden" id="pID_created" value=""/>
                <ul class="steps">
                    <li data-step="1" class="active">General Information<span class="chevron"></span></li>
                    <li data-step="2">Variations<span class="chevron"></span></li>
                    <li data-step="3">Categories<span class="chevron"></span></li>
                    <li data-step="4">Channel Manager<span class="chevron"></span></li>
                    <li data-step="5">Inventory Management<span class="chevron"></span></li>
                    <li data-step="6">Media<span class="chevron"></span></li>
                    <li data-step="7">Pricing<span class="chevron"></span></li>
                    <!--<li data-step="8">Translation<span class="chevron"></span></li>-->
                </ul>
                <div class="step-content">
                    <div data-step="1" class="step-pane active">
                        <form id="pgen_frm" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" class="form-horizontal group-border-dashed">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Name</label>
                                <div id="pName_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter Value" class="form-control" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">SKU</label>
                                <div id="pSKU_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">HTS</label>
                                <div id="pHTS_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">UPC</label>
                                <div id="pUPC_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">EAN</label>
                                <div id="pEAN_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">JAN</label>
                                <div id="pJAN_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">ISBN</label>
                                <div id="pISBN_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">MPN</label>
                                <div id="pMPN_create" class="pgen_req col-sm-6">
                                    <input type="text" placeholder="Please Enter value (must be unique)" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Adult</label>
                                <div id="pAdult_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Age Group</label>
                                <div id="pAgeGroup_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <option value="Newborn">Newborn</option>
                                        <option value="Infant">Infant</option>
                                        <option value="Toddler">Toddler</option>
                                        <option value="Kids">Kids</option>
                                        <option value="Adult">Adult</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Availability</label>
                                <div id="pAvail_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <option value="In Stock">In Stock</option>
                                        <option value="Out of Stock">Out of Stock</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Brand</label>
                                <div id="pBrand_create" class="pgen_req col-sm-6">
                                    <input type="text" class="form-control" readonly value="<?php echo ucfirst(Yii::$app->user->identity->company); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Condition</label>
                                <div id="pCond_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <option value="New">New</option>
                                        <option value="Used">Used</option>
                                        <option value="Refurbished">Refurbished</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Gender</label>
                                <div id="pGend_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <option value="Female">Female</option>
                                        <option value="Male">Male</option>
                                        <option value="Unisex">Unisex</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Weight (lbs)</label>
                                <div id="pWght_create" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control" onkeypress="validate(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Description</label>
                                <div id="pDes_create" class="col-sm-6">
                                    <div id="product-create-description"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-10">
                                    <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next1">Next Step</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-step="2" class="step-pane">
                        <form id="pvariation_frm" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding">
                                <div class="col-sm-7">
                                    <label class="control-label">Product Variants</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Variant Name</label>
                                <div id="pvar_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                        <?php foreach ($product_variants as $p_var) : ?>
                                          <option value="<?= $p_var->id; ?>"><?= $p_var->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Variant Items</label>
                                <div id="pvaritems_create" class="col-sm-6">
                                    <select class="select2">
                                        <option>Please Select</option>
                                    </select>
                                </div>

                            </div>
                            <div class="form-group">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-6" style="text-align:right;">
                                    <button class="btn btn-space btn-primary add_pvar">
                                        <i class="icon icon-left mdi mdi-plus"></i> 
                                        Add 
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="columns_added" value=""/>
                            <input type="hidden" id="columns_count" value="6"/>
                            <input type="hidden" id="rows_count" value="1"/>
                            <div class="col-sm-12">
                                <div class="panel panel-default">
                                    <div class="pvariation_tbl_body panel-body be-loading">
                                        <table id="pvariation_tbl" class="table table-condensed table-hover table-bordered table-striped">
                                            <thead>
                                                <tr>                                                    
                                                    <th>Variant Name</th>
                                                    <th>SKU</th>
                                                    <th>Inventory</th>
                                                    <th>Price</th>
                                                    <th>Weight</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="save_var_btn">
                                            <button class="btn btn-space btn-primary save_pvar">
                                                Save 
                                            </button>
                                        </div>
                                        <div class="be-spinner">
                                            <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous2">Previous</button>
                                    <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next2">Next Step</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-step="3" class="step-pane">
                        <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="col-sm-12">
                                <div class="category_add">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Categories</label>
                                        <div id="category_create" class="col-sm-6">
                                            <select class="select2">
                                                <option>Please Select</option>
                                                <?php foreach ($categories as $category) : ?>
                                                    <option value="<?= $category->id; ?>"><?= $category->name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous3">Previous</button>
                                            <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next3">Next Step</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-step="4" class="step-pane">
                        <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="channel_add">
                                <a href="/channels/create" target="_blank" class="btn btn-space btn-primary">
                                    <i class="icon icon-left mdi mdi-plus"></i>
                                    Add New Channel
                                </a>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Channels</label>
                                    <div id="channel_create" class="col-sm-6">
                                        <select class="select2">
                                            <option>Please Select</option>
                                            <?php foreach ($connections as $connection) : ?>
                                                <option value="<?= $connection['id']; ?>"><?= $connection['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous4">Previous</button>
                                        <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next4">Next Step</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div data-step="5" class="step-pane">
                        <form id="pinvt_frm" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="inventory_add">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Stock Level</label>
                                    <div id="pstk_lvl_create" class="col-sm-6">
                                        <select class="select2">
                                            <option>Please Select</option>
                                            <option value="In Stock">In Stock</option>
                                            <option value="Out of Stock">Out of Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Stock Status</label>
                                    <div id="pstk_sts_create" class="col-sm-6">
                                        <select class="select2">
                                            <option>Please Select</option>
                                            <option value="Visible">Visible</option>
                                            <option value="Hidden">Hidden</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Low Stock Notification</label>
                                    <div id="plw_stk_ntf_create" class="col-sm-6">
                                        <input type="text" placeholder="Please Enter value" class="form-control" onkeypress="validate(event)">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous5">Previous</button>
                                        <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next5">Next Step</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-step="6" class="step-pane">
                        <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="row pmedia">
                                <input type="hidden" value="4" id="imgDivCount"/>
                                <?php
                                for ($i = 1; $i <= 4; $i++) :
                                  $radio_check = '';
                                  if ($i == 1):
                                    $radio_check = 'checked';
                                  endif;
                                  ?>
                                  <div class="col-sm-3 pimg-div<?= $i ?> be-loading draggable-element" id="pimgDiv_<?= $i ?>">

                                      <div class="bs-grid-block product_create_image product-texthover-default">

                                          <div class="user-display-bg">
                                              <div class="content dropzone pImg_Create_Drop1" id="pImageDrop_<?php echo $i; ?>">
                                              </div>
                                              <!-- <div class="product-default-overlay custom_drozone_css dropzone pImg_Create_Drop2"><br />
                                                           <span class="panel-heading profile-panel-heading" ></span>
                                                   </div>-->
                                          </div>

                                      </div>
                                      <input type="hidden" id="upld_img<?php echo $i; ?>" class="upld_img" value="">
                                      <div class="setimg">
                                          <i class="icon icon-left mdi mdi-image"></i> 
                                          Set as Default Image
                                          <div class="be-radio">
                                              <input <?= $radio_check ?> name="pimg_radio" id="pimgRad_<?php echo $i; ?>" type="radio">
                                              <label for="pimgRad_<?php echo $i; ?>"></label>
                                          </div>
                                      </div>
                                      <table id="pimg_tbl<?php echo $i; ?>" style="clear: both" class="table table-striped table-borderless">
                                          <tbody>
                                              <tr>
                                                  <td width="45%">Image Label</td>
                                                  <td width="55%"><a id="pimg_lbl<?php echo $i; ?>" class="pimg_lbl" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                              </tr>
                                              <tr>
                                                  <td width="45%">Alt Tag</td>
                                                  <td width="55%"><a id="pimg_alt_tag<?php echo $i; ?>" class="pimg_alt_tag" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                              </tr>
                                              <tr>
                                                  <td width="45%">HTML Video Link</td>
                                                  <td width="55%"><a id="pimg_html_video<?php echo $i; ?>" class="pimg_html_video" href="#" data-type="text" data-title="Please Enter value(Only support Youtube and Vimeo)"></a></td>
                                              </tr>                                              
                                          </tbody>
                                      </table>
                                      <div class="vfile">
                                          <input type="file" name="pimg_360_video" id="pimg_360_video<?php echo $i; ?>" data-multiple-caption="{count} files selected" multiple class="inputfile" accept="video/*" >
                                          <label for="pimg_360_video<?php echo $i; ?>" class="btn-default"> <i class="mdi mdi-upload"></i>
                                              <span>Select 360-degree video</span>
                                          </label>
                                      </div>
                                      <div class="progress" id="pimg_360_video_progress_wrapper<?php echo $i; ?>">
                                          <div id="pimg_360_video_progress<?php echo $i; ?>" class="progress-bar progress-bar-primary progress-bar-striped"></div>
                                      </div>
                                      <div class="vupld">
                                          <button id="vupldbtn_<?php echo $i; ?>" class="btn btn-rounded btn-space btn-default vupld_btn">Upload</button>
                                      </div>
                                      <div class="pimg_save_btns">
                                          <button id="pimgSaveBtn_<?php echo $i; ?>" class="btn btn-space btn-primary btn-sm pimg_save_btn">Save</button>
                                      </div>
                                      <div class="be-spinner">
                                          <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                          <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/>
                                          </svg>
                                      </div>
                                  </div>
                                <?php endfor; ?>
                                <div class="addimg">
                                    <button class="btn btn-space btn-primary addimg-btn">
                                        <i class="icon icon-left mdi mdi-plus"></i> 
                                        Add More
                                    </button>
                                </div>

                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous6">Previous</button>
                                    <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next6">Next Step</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div data-step="7" class="step-pane">
                        <form id="pPrice_frm" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Price</label>
                                <div id="pprice_create" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control" onkeypress="validate(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Sale Price</label>
                                <div id="psale_price_create" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control" onkeypress="validate(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Schedule Sale Date</label>
                                <div class="col-md-3 col-xs-7">
                                    <div id="psched_date1_create" data-min-view="2" data-date-format="dd/mm/yyyy" class="input-group date datetimepicker1">
                                        <input size="16" type="text" value="<?php echo $schedule_date1; ?>" readonly="" class="form-control"><span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous7">Previous</button>
                                    <button data-wizard="#wizard1" class="btn btn-success btn-space wizard-next7">Complete</button>
                                </div>
                            </div>
                        </form>
                    </div>
		    <!--For Translation TAb!-->
<!--                    <div data-step="9" class="step-pane">
                        <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            Accordions
                            <div class="row">
                                <div class="col-sm-12 panel-group accordion">
                                    <div class="panel panel-default">
                                        <div class="panel-heading col-sm-2">
                                            <h4 class="panel-title"><a class="collapsed" data-toggle="collapse" data-parent="#accordion1" href="#accordion1" ><i class="icon mdi mdi-chevron-down"></i>English</a></h4>
                                        </div>
                                        add button for languages
                                        <div class="transalte-language col-sm-10">
                                            <button class="btn btn-space btn-primary addimg-btn">
                                                <i class="icon icon-left mdi mdi-translate"></i> 
                                                Automatically Translate
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="accordion1" class="panel-group accordion collapse">
                                    Editor Description
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion1" href="#collapseOne" class="collapsed"><i class="icon mdi mdi-chevron-down"></i>Description</a></h4>
                                        </div>
                                        <div id="collapseOne" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <div id="translation-description"></div>
                                            </div>
                                        </div>
                                    </div>
                                    Editor Short Description
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion1" href="#collapseTwo" class="collapsed"><i class="icon mdi mdi-chevron-down"></i>Short Description</a></h4>
                                        </div>
                                        <div id="collapseTwo" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <div id="trans-shortdescription"></div>
                                            </div>
                                        </div>
                                    </div>
                                    Editor Application Tips
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion1" href="#collapseThree" class="collapsed"><i class="icon mdi mdi-chevron-down"></i>Application Tips</a></h4>
                                        </div>
                                        <div id="collapseThree" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <div id="trans-applicationtips"></div>
                                            </div>
                                        </div>
                                    </div>
                                    Editor Ingredients
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion1" href="#collapseFour" class="collapsed"><i class="icon mdi mdi-chevron-down"></i>Ingredients</a></h4>
                                        </div>
                                        <div id="collapseFour" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <div id="trans-ingredients"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            add button for languages
                            <div class="addlanguage">
                                <button class="btn btn-space btn-primary addimg-btn">
                                    <i class="icon icon-left mdi mdi-plus"></i> 
                                    Add Language
                                </button>
                            </div>


                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous">Previous</button>
                                    <button data-wizard="#wizard1" class="btn btn-success btn-space wizard-complete">Complete</button>
                                </div>
                            </div>
                        </form>
                    </div>-->
		    <!--End Section For Translation tab!-->

                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="current_user_id" value="<?php echo $user_id;?>">
<!--Invalid Video Type Modal-->
<div id="mod-360video-alert" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3>Invalid File Type!</h3>
                    <p>Please select video file.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-danger">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in category_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close category_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='category_ajax_header_error_msg'>Error</h3>
                    <p id="category_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default category_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>