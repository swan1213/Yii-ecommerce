<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018-01-22
 * Time: 05:31 PM
 */
use frontend\models\search\CategorySearch;
use common\models\ProductCategory;
use common\models\Category;

?>

<div class="row wizard-row">
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
                    <li data-step="1" class="active">Create Feed<span class="chevron"></span></li>
                </ul>
                <div class="step-content">
                    <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                    <form action="<?=$action?>" method="post" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Feed Name</label>
                            <div id="fb_feed_name" class="col-sm-6">
                                <input  name="feed_name" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="<?=$feed_name?>">
                            </div>
                        </div>
                        <div>
                            <div class="col-md-6">
                                <div><strong style="color: cadetblue;">Please select the categories</strong></div>
                                <div class="m_treeview">
                                    <?php
                                    function showTreeView($category_list, $selected_categories) {
                                        $result = array();
                                        if (sizeof($category_list) > 0) {
                                            $result[] = '<ul>';
                                            foreach ($category_list as $single_category) {
                                                $child_category_list = Category::find()->Where(['parent_id' => $single_category->id])->orderBy(['name' => SORT_ASC])->all();
                                                $checked = in_array($single_category->id, $selected_categories, true) ? "checked" : "";
                                                $element = "<li>
                                                                <div class='be-checkbox'>
                                                                        <input class='fb_feed_categories' id='chk_{$single_category->id}' type='checkbox' name='categories[]' value='{$single_category->id}' $checked>
                                                                        <label for='chk_{$single_category->id}'>{$single_category->name}</label>
                                                                </div>";

                                                if(!empty($child_category_list)){
                                                    $element .= showTreeView($child_category_list, $selected_categories);
                                                } else {
                                                    $element .= '</li>';
                                                }

                                                $result[] = $element;
                                            }

                                            $result[] = '</ul>';
                                        }

                                        return implode($result);
                                    }

                                    echo showTreeView($categories, $selected_categories);
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div><strong style="color: cadetblue;">Please select the countries</strong></div>
                                <div>
                                    <?php
                                    if(!empty($countries)) {
                                        foreach ($countries as $country) { ?>
                                            <div class="be-checkbox inline col-sm-6 col-md-4 col-lg-3 margin-left-0">
                                                <input class="fb_feed_countries" id="<?= $country->sortname; ?>"
                                                       type="checkbox" name="countries[]"
                                                       value="<?= $country->sortname; ?>" <?= in_array($country->sortname, $selected_countries, true) ? "checked" : "" ?>>
                                                <label for="<?= $country->sortname; ?>"><?= $country->name; ?></label>
                                            </div>
                                            <?php
                                        }
                                    }else{ ?>
                                        <div role="alert" class="alert alert-primary alert-dismissible" style="margin-top: 20px;">
                                            <span class="icon mdi mdi-info-outline"></span>
                                            You don't have any Connected Channel. Please connect your Channel or Store first
                                            <i><u><a style="color: burlywood;" href="/channels"><b>Here</b></a></u></i>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <div class="col-sm-12" style="padding-top:30px;padding-left:30px;">
                                <button class="btn btn-default btn-space"><a href='/facebook'>Cancel</a></button>
                                <input type="submit" class="btn btn-space btn-primary" id="fb_feed_submit" value="Save">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="fbfeed_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="tpl_ajax_header_msg">Success!</h3>
                    <p id="tpl_ajax_msg">Success! Your feed has been created successfully.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default tpl_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in tpl_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='tpl_ajax_header_error_msg'>Error</h3>
                    <p id="tpl_ajax_msg_eror">Error Something went wrong. Please try again</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default tpl_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>