<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use backend\models\CustomerUser;
use backend\models\OrdersProducts;
use backend\models\OrderFullfillment;





$this->title = 'Order Tracking';
$this->params['breadcrumbs'][] = 'Order Tracking';
?>
<div class="page-head">
    <h2 class="page-head-title">Order Tracking</h2>
    <ol class="breadcrumb page-head-nav">
<?php
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]);
?>
    </ol>
</div>
 <div class="row">
            <div class="col-md-12">
              <ul class="timeline">
                <li class="timeline-item">
                  <div class="timeline-date"><span>September 16, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar6.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">4:34 PM</span><span class="timeline-autor">Penelope Thornton</span>
                      <p class="timeline-activity">Pellentesque imperdiet sit <a href="#">Amet nisl sed mattis</a>.</p>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-detailed">
                  <div class="timeline-date"><span>September 13, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">9:54 AM</span><span class="timeline-autor">Kristopher Donny  </span>
                      <p class="timeline-activity">Mauris condimentum est <a href="#">Viverra erat fermentum</a>.</p>
                      <div class="timeline-summary">
                        <p>Suspendisse ac libero sed mauris tempor vehicula porttitor non sapien. Aliquam viver... </p>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-detailed timeline-item-gallery">
                  <div class="timeline-date"><span>August 23, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar3.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">10:42 AM</span><span class="timeline-autor">Sherwood Clifford  </span>
                      <p class="timeline-activity">pellentesque tortor <a href="#">enim</a>.</p>
                      <div class="timeline-gallery"><img src="/img/gallery/img2.jpg" alt="Thumbnail" class="gallery-thumbnail"><img src="/img/gallery/img4.jpg" alt="Thumbnail" class="gallery-thumbnail"><img src="/img/gallery/img11.jpg" alt="Thumbnail" class="gallery-thumbnail"><img src="/img/gallery/img12.jpg" alt="Thumbnail" class="gallery-thumbnail"></div>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-detailed">
                  <div class="timeline-date"><span>August 6, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar4.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">7:15 PM</span><span class="timeline-autor">Benji Harper </span>
                      <p class="timeline-activity">Mauris condimentum est <a href="#">Vestibulum justo neque</a>.</p>
                      <div class="timeline-summary">
                        <p>Quisque condimentum enim nec porttitor egestas. Morbi fermentum in ante volutpat... </p>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="timeline-item">
                  <div class="timeline-date"><span>August 4, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar5.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">12:02 PM</span><span class="timeline-autor">Justine Myranda </span>
                      <p class="timeline-activity">Pellentesque imperdiet sit <a href="#">Amet nisl sed mattiss</a>.</p>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-detailed">
                  <div class="timeline-date"><span>June 11, 2016</span></div>
                  <div class="timeline-content">
                    <div class="timeline-avatar"><img src="/img/avatar3.png" alt="Avatar" class="circle"></div>
                    <div class="timeline-header"><span class="timeline-time">6:25 AM</span><span class="timeline-autor">Sherwood Clifford </span>
                      <p class="timeline-activity">pellentesque tortor <a href="#">Aliquam viverra</a>.</p>
                      <blockquote class="timeline-blockquote">
                        <p>Quisque condimentum enim nec porttitor egestas. </p>
                        <footer>Aliquam viverra ornare dolor.</footer>
                      </blockquote>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-loadmore"><a href="#" class="load-more-btn">Load more</a></li>
              </ul>
            </div>
          </div>