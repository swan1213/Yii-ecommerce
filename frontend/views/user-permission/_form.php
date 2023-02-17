<?php

use frontend\components\Helpers;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
?>

<div class="user-form">

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


    <div class="col-sm-10" >
        <div class="panel panel-default panel-border-color panel-border-color-primary">
            <div class="panel-body">
                <?php $form = ActiveForm::begin(); ?>
                <div class="form-group xs-pt-10">
                    <?=$form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="form-group xs-pt-10">
                    <label>Menu Role</label>
                    <select id="role_menu_opt" multiple="multiple" name="role_menu_select[]">
                        <?php
                        foreach ($role_menu_permission as $key => $menu_value) {
                            echo '<optgroup label='.$key.'>';
                            foreach ($menu_value as $key => $value) {
                                if (Helpers::isExistSubMenuItem($model->menu_permission, $key))
                                    echo '<option value='.$key.' selected="">'.$value.'</option>';
                                else
                                    echo '<option value='.$key.'>'.$value.'</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group xs-pt-10">
                    <label>Channel Role</label>
                    <select id="role_channel1_select" multiple="multiple" name="role_channel_select[]">
                        <?php
                        foreach ($role_channel_permission as $key => $channel_value) {
                            if (is_array($channel_value)) {
                                echo '<optgroup label='.$key.'>';
                                foreach ($channel_value as $key => $value) {
                                    if (Helpers::isExistSubChannelItemForRole($model->channel_permission, $key))
                                        echo '<option value='.$key.' selected="">'.$value.'</option>';
                                    else
                                        echo '<option value='.$key.'>'.$value.'</option>';
                                }
                                echo '</optgroup>';
                            }
                            else {
                                if (Helpers::isExistSubChannelItemForRole($model->channel_permission, $key)) {
                                    echo '<option value=' . $key . ' selected="">' . $channel_value . '</option>';
                                }
                                else {
                                    echo '<option value=' . $key . '>' . $channel_value . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group xs-pt-10">
                    <label>Other Role</label>
                    <select id="role_setting_optgroup" multiple="multiple" name="role_other_select[]">
                        <?php
                        foreach ($role_other_permission as $key => $other_value) {
                            if (is_array($other_value)) {
                                echo '<optgroup label='.'Channel Setting'.'>';
                                foreach ($other_value as $key => $value) {
                                    if (Helpers::isExistSubOtherItemForRole($model->other_permission, $key))
                                        echo '<option value='.$key.' selected="">'.$value.'</option>';
                                    else
                                        echo '<option value='.$key.'>'.$value.'</option>';
                                }
                                echo '</optgroup>';
                            }
                            else {
                                if (Helpers::isExistSubChannelItemForRole($model->other_permission, $key))
                                    echo '<option value='.$key.' selected="">'.$other_value.'</option>';
                                else
                                    echo '<option value='.$key.'>'.$other_value.'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-space btn-primary' : 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>





