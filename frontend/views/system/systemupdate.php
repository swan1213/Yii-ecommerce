<?php
use common\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;

$this->title = 'System Updates';
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

<div class="main-content container-fluid">
          <div class="row">
            <div class="col-md-12">
              <ul class="timeline">
                <li class="timeline-item">
                  <div class="timeline-date"><span>Launch</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/sergio_img.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time"></span><span class="timeline-autor">Sergio Villasenor, </span>
                      <p class="timeline-activity">CEO at Elliot,  <a href="#">"we are live."</a></p>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-loadmore"><a href="#" class="load-more-btn">Load more</a></li>
              </ul>
            </div>
          </div>
        </div>
