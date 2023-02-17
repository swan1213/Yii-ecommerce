<?php
    use common\models\ProductCategory;
    use common\models\Category;
    use yii\widgets\ActiveForm;
    use yii\helpers\Html;

    $target_countries = [
        'AR' => 'Argentina',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'BR' => 'Brazil',
        'CA' => 'Canada',
        'CL' => 'Chile',
        'CN' => 'China',
        'CO' => 'Colombia',
        'CZ' => 'Czechia',
        'DK' => 'Denmark',
        'FR' => 'France',
        'DE' => 'Germany',
        'HK' => 'Hong Kong',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'JP' => 'Japan',
        'MY' => 'Malaysia',
        'MX' => 'Mexico',
        'NL' => 'Netherlands',
        'NZ' => 'New Zealand',
        'NO' => 'Norway',
        'PH' => 'Phillippines',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'RW' => 'Russia',
        'SG' => 'Singapore',
        'ZA' => 'South Africa',
        'ES' => 'Spain',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'TW' => 'Taiwan',
        'TR' => 'Turkey',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States'
    ];

    $fetch_frequency_list = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly'
    ];

    $fetch_date_list = [];

    for($i=1; $i<=31; $i++) {
        $fetch_date_list[$i] = $i;
    }

    $fetch_weekday_list = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Rriday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
    ];

    $fetch_time_list = [
        '0' => '12:00 AM',
        '1' => '1:00 AM',
        '2' => '2:00 AM',
        '3' => '3:00 AM',
        '4' => '4:00 AM',
        '5' => '5:00 AM',
        '6' => '6:00 AM',
        '7' => '7:00 AM',
        '8' => '8:00 AM',
        '9' => '9:00 AM',
        '10' => '10:00 AM',
        '11' => '11:00 AM',
        '12' => '12:00 PM',
        '13' => '1:00 PM',
        '14' => '2:00 PM',
        '15' => '3:00 PM',
        '16' => '4:00 PM',
        '17' => '5:00 PM',
        '18' => '6:00 PM',
        '19' => '7:00 PM',
        '20' => '8:00 PM',
        '21' => '9:00 PM',
        '22' => '10:00 PM',
        '23' => '11:00 PM'
    ];
?>

<style type="text/css">
    .category-input .treeview {
        max-height: 300px;
        overflow-y: auto;
    }
</style>

<div class="row wizard-row">
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
                    <li data-step="1" class="active">Authrized<span class="chevron"></span></li>
                    <li data-step="2" class="active"><?= $type ?> Feed<span class="chevron"></span></li>
                </ul>

                <div class="step-content googleshopping-feed-container">
                    <?php $feed_form = ActiveForm::begin([
                        'action' => $action,
                        'method' => 'post',
                        'id' => 'google_shopping_datafeed_form',
                        'class' => 'form-horizontal group-border-dashed'
                    ]); ?>
                        <div class="form-group row">
                            <label class="col-sm-12 control-label"><h4><strong>Please provide the below information</strong></h4></label>
                        </div>
                        <div class="form-group row category-input <?php if(!empty($feed_model->errors) and isset($feed_model->errors['category_ids'])) echo 'has-error' ?>">
                            <label class="control-label col-sm-12">
                                Category list
                            </label>
                            <div class="treeview col-sm-12">
                                <?php
                                    function nested2ul($category_list, $l_selected_categories) {
                                        $result = array();
                                        if (sizeof($category_list) > 0) {
                                            $result[] = '<ul>';

                                            foreach ($category_list as $single_category) {
                                                $category_product_count = ProductCategory::find()->Where(['category_id' => $single_category->id])->count();
                                                $parent_id = $single_category->parent_id;
                                                $category_id = $single_category->id;

                                                if(empty($l_selected_categories)) {
                                                    $checked = '';
                                                    $class_name = 'custom-unchecked';
                                                } else {
                                                    $checked = in_array($category_id, $l_selected_categories)?'checked':'';
                                                    $class_name = in_array($category_id, $l_selected_categories)?'custom-checked':'custom-unchecked';
                                                }
                                                
                                                $element = sprintf(
                                                    '<li><input type="checkbox" name="GoogleShopppingFeedForm[category_ids][]" value="%s" %s><label class="%s"><div class="row"><div class="col-md-8 span_cat_name_nested">%s</div><div class="col-md-3 span_product_nested">%s Product</div></div></label>',
                                                    $single_category->id,
                                                    $checked,
                                                    $class_name,
                                                    $single_category->name,
                                                    $category_product_count
                                                );

                                                $child_category_list = Category::find()->Where(['parent_id' => $category_id])->orderBy(['name' => SORT_ASC])->all();
                                                if(sizeof($child_category_list) > 0) {
                                                    $element .= nested2ul($child_category_list, $l_selected_categories);
                                                } else {
                                                    $element .= '</li>';
                                                }

                                                $result[] = $element;
                                            }

                                            $result[] = '</ul>';
                                        }

                                        return implode($result);
                                    }

                                    echo nested2ul($category_list, $selected_categories);
                                ?>
                            </div>
                            <?php if (!empty($feed_model->errors) and isset($feed_model->errors['category_ids'])): ?>
                                <div class="help-block col-sm-12">
                                    <?= $feed_model->errors['category_ids'][0] ?>
                                </div>
                            <?php endif; ?> 
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6">
                                <label class="control-label">
                                    Feed Name
                                </label>
                                <?php
                                    echo $feed_form->field($feed_model, 'feed_name')->textInput(['placeholder' => 'Feed Name', 'class' => 'form-control', 'value' => 'Elliot Feed'])->label(false);
                                 ?>
                            </div>

                            <div class="col-sm-6">
                                <?= $feed_form->field($feed_model, 'destinations')->checkboxList(['Shopping'=>'Shopping', 'DisplayAds'=>'Display']) ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6">
                                <?= $feed_form->field($feed_model, 'target_country')->dropDownList($target_countries, [
                                    'prompt' => 'Choose...',
                                    'class' => 'form-control',
                                    'onchange' => 'onChangeTargetCountry($(this).val())'
                                ]); ?>
                            </div>

                            <div class="col-sm-6">
                                <?= $feed_form->field($feed_model, 'language')->dropDownList([], [
                                    'prompt' => 'Choose...',
                                    'class' => 'form-control feed-language'
                                ]); ?>
                            </div>
                            
                        </div>

                        <fieldset class="form-group row">
                            <label class="col-sm-3 control-label">Create an upload schedule</label>
                            <div class="col-sm-9">
                                <div class="form-group">
                                    <?= $feed_form->field($feed_model, 'fetch_frequency')->dropDownList($fetch_frequency_list, [
                                        'class' => 'form-control fetch-frequency',
                                        'onchange' => 'onChangeFrequency($(this).val())'
                                    ]); ?>
                                </div>

                                <div class="form-group fetch-date-wrap">
                                    <?= $feed_form->field($feed_model, 'fetch_date')->dropDownList($fetch_date_list, [
                                        'class' => 'form-control fetch-date'
                                    ]); ?>
                                </div>

                                <div class="form-group fetch-weekday-wrap">
                                    <?= $feed_form->field($feed_model, 'fetch_weekday')->dropDownList($fetch_weekday_list, [
                                        'class' => 'form-control fetch-weekday'
                                    ]); ?>
                                </div>

                                <div class="form-group">
                                    <?= $feed_form->field($feed_model, 'fetch_time')->dropDownList($fetch_time_list, [
                                        'class' => 'form-control fetch-time'
                                    ]); ?>
                                </div>

                                <div class="form-group">
                                    <?= $feed_form->field($feed_model, 'timezone')->dropDownList($timezone_list, [
                                        'prompt' => 'Choose...',
                                        'class' => 'form-control'
                                    ]); ?>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group row">
                            <div class="col-sm-9">
                                <div class="alert alert-danger error-wrap" style="display: none;">
                                </div>  
                            </div>
                        </div>

                        <input type="hidden" id="google_shopping_id" value="<?= $id ?>">

                        <div class="form-group row">
                            <div class="col-sm-12">
                                <?= Html::a('Cancel', ['/googleshopping', 'id' => $id], ['class'=>'btn btn-default']) ?>
                                <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/google-shopping.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>