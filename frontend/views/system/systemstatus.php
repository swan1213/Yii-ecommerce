<?php
use common\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;


$this->title = 'System Status';
$username = Yii::$app->user->identity->username;
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
<?php
$d =  date('Y-m-d h:i:s', time());
?>

<div class="main-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
               <div class="panel-heading panel-heading-divider">As of  <?php echo date('F'); ?>, <?php echo date('d'); ?>, <?php echo date('Y'); ?>, at <?php echo  date("h:i:s"); ?> <?php echo date('A'); ?> the system is <span class="live_color">live</span>.</div>
               <div class="panel-heading panel-heading-divider" style="display:none;">As of TIMESTAMP IN MONTH, DAY, YEAR, at CURRENT TIME XX:XX:XX AM OR PM, the system is <span class="down_color">down</span>.</div>
               <div class="panel-heading panel-heading-divider" style="display:none;">As of TIMESTAMP IN MONTH, DAY, YEAR, at CURRENT TIME XX:XX:XX AM OR PM, the system is <span class="temp_color">temporarily unaccessible</span>.</div>
              
            </div>
        </div>
    </div>
</div>
