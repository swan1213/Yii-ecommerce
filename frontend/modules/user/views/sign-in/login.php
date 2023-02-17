<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use frontend\widgets\SpinnerWidget;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = Yii::t('frontend', 'Login');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'be-splash-screen';

?>

<?php
    echo SpinnerWidget::widget([
        'title' => 'We are updating and loading your data.',
    ]);
?>

<div class="be-wrapper be-login">
    <div class="be-content be-loading">
        <div class="main-content container-fluid">
            <div class="splash-container">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading">
                        <img src="<?php echo Yii::getAlias('@frontendUrl') ?>/img/elliot-logo-thumbnail.svg" alt="logo" width="150" height="160" class="logo-img">
                    </div>
                    <div class="panel-body">

                        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                        <?php if($idle!=0){ ?>
                            <div role="alert" class="alert alert-contrast alert-danger alert-dismissible">
                                <div class="icon" style="    background: none;"><span class="mdi mdi-close-circle-o"></span></div>
                                <div class="message">
                                    <button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true" class="mdi mdi-close"></span></button>You have been logged out due to inactivity within 30 mins. Please log in again.
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <?= $form->field($model, 'identity')->textInput(['autofocus' => true])->label('Email') ?>
                        </div>
                        <div class="form-group">
                            <?= $form->field($model, 'password')->passwordInput() ?>
                        </div>
                        <div class="form-group row login-tools">
                            <div class="col-xs-6 login-remember">
                                <div class="be-checkbox">
                                    <input type="checkbox" id="rememberMe"<?=($model->rememberMe)?' checked':'';?>>
                                    <label for="rememberMe">Remember Me</label>
                                </div>
                            </div>
                            <div class="col-xs-6 login-forgot-password"><a href="/request-password-reset">Forgot Password?</a></div>
                        </div>
                        <div class="form-group login-submit">
                            <?= Html::submitButton('Log in', ['class' => 'btn btn-primary btn-xl LoginCustomClass', 'name' => 'login-button']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
<!--                <div class="splash-footer"><span>Don't have an account? <a href="/user/sign-in/signup">Sign Up</a></span></div>-->
            </div>
        </div>
    </div>
</div>
<?php
$loginJs = <<< SCRIPT
        $(document).ready(function(){
        
            $(".LoginCustomClass").click(function(event){
                //event.preventDefault();
                var loginUser = $("#loginform-identity").val();
                var loginPwd = $("#loginform-password").val();
                if ( loginUser.length > 0 && loginPwd.length > 0 ) {
                    jQuery('body').addClass('be-loading-active');
                    jQuery('body').addClass('be-loading');
                    jQuery('.be-loader-modal-text').css('display', 'block');
                    jQuery('.be-spinner').css('display', 'block');
                
                }
            });
        
        
        });
SCRIPT;

$this->registerJs($loginJs, View::POS_READY);

?>
