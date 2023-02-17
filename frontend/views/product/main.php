<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>-->
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <?php
        $this->registerJs(
          
            'jQuery(document).ready(function () {
              //initialize the javascript
              App.init();
              App.uiNotifications();
              App.textEditors();
              //App.formEditable();
              App.dataTables();
              App.dashboard();
              App.wizard();
              App.formElements();
              });'
        );
        ?>
    </head>
    <?php
    if (Yii::$app->session->hasFlash('success')):
      $header_cls = 'be-color-header be-color-header-success';
    elseif (Yii::$app->session->hasFlash('warning')):
      $header_cls = 'be-color-header be-color-header-warning';
    elseif (Yii::$app->session->hasFlash('danger')):
      $header_cls = 'be-color-header be-color-header-danger';
    elseif (Yii::$app->session->hasFlash('info')):
      $header_cls = 'be-color-header';
    else:
      $header_cls = '';
    endif;

//    $header_cls = 'be-wrapper be-fixed-sidebar';
    ?>
    <body>
        <div class="be-wrapper be-fixed-sidebar <?php echo $header_cls ?>">
            <?php $this->beginBody() ?>

            <?php include 'header.php'; ?>

            <?= Alert::widget() ?>
            <div class="be-content">
                <div class="main-content container-fluid">
                    <?=
                    $content
                    ?>
                </div>
            </div>


            <?php include 'footer.php'; ?>



            <?php $this->endBody() ?>
        </div>
    </body>
</html>
<?php $this->endPage() ?>
