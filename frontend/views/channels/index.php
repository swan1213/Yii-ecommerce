<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Connection;
use yii\widgets\Breadcrumbs;
use common\models\UserConnection;
use common\models\Order;
use frontend\components\Helpers;

$this->title = 'Channels';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/channels']];
$this->params['breadcrumbs'][] = 'View All';

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

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading">
                <div class="tools">
                    <a href="#"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="channels_tbl_view" class="table table-striped table-hover table-fw-widget">
                    <thead>

                        <tr>
                            <th>Channel Name</th>
                            <th>Products Listed in Channel</th>
                            <th>Channel Revenue</th>
                            <th>Channel Sales</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($showChannelsData as $single) {
                            if($single['name'] != 'Souq') {
                            ?>
                            <tr class="odd gradeX">

                                <td>
                                    <?php
                                        echo $single['name'];
                                    ?>
                                </td>
                                <td><?php echo $single['channelProductCnt']; ?></td>
                                <td><?php echo '$' . number_format($single['channelRevenue'], '2', '.',','); ?></td>
                                <td><?php echo $single['channelSales']?$single['channelSales']:0; ?></td>
                                <td>
                                    <?php
                                         if ( $single['name'] == "Google Shopping" || $single['name'] == "Facebook" || $single['name'] == "Pinterest" || $single['name'] == "TMall" ) {
                                            $href_url = $single['connected_url'] ? $single['connected_url'] : 'javascript:';
                                             echo '<a href="'. $href_url .'">'.$single["status"].'</a>';
                                         } else {
                                             echo '<a href="javascript:" data-toggle="modal" class=""  data-target="#connection_'. $single['parentId'].'">'.$single["status"].'</a>';
                                         }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            }
                        }
                        ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php
foreach ($showChannelsData as $marketplace) {
    ?>
    <?php if ($marketplace['connection_child_count'] == 1) { ?>
        <div id="connection_<?php echo $marketplace['parentId']; ?>" tabindex="-1" role="dialog" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close">
                            <span class="mdi mdi-close"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="text-primary">
                                <img src="<?php echo $marketplace['channel_image']; ?>" style="width: 50%" alt="" class="<?php echo $marketplace['name'] == "Xiao Hong Shu" ? 'resize_Xiao' : ''; ?>">
                            </div>
                            <?php if ($marketplace['name'] == 'Instagram') { ?>
                                <h3>Coming Soon</h3>
                            <?php } ?>
                            <p>Subscribe to get updated when <?= $marketplace['name'] ?> becomes available in Elliot.</p>
                            <div class="xs-mt-50"> 
                                <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                                <?php if (in_array($marketplace['name'], [
                                        'Google Shopping',
                                        'Shiphawk',
                                        'Shiphero',
                                        'Instagram'
                                    ])): ?>
                                    <?php
                                        $single_user_connect_url = $marketplace['connected_url'] ? $marketplace['connected_url'] : 'javascript:';
                                    ?>
                                    <a href="<?php echo $single_user_connect_url; ?>">
                                        <button type="button" class="btn btn-space btn-primary">Subscribe</button>
                                    </a>

                                <?php else: ?>
                                    <button type="button" data-dismiss="modal" class="btn btn-space btn-primary subscribeChannel" data-type="<?php echo $marketplace['name'] ?>">
                                        Subscribe
                                    </button>
                                <?php endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div id="connection_<?php echo $marketplace['parentId']; ?>" tabindex="-1" role="dialog" class="modal fade customMODAL">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <img src="<?php echo $marketplace['channel_image']; ?>" style="width: 50%" alt="">
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close">
                            <span class="mdi mdi-close"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="">
                            <div class="row">
                                <div class="form-group">
                                    <div class="panel-body table-responsive ">
                                        <table id="lazada_connect_modal" class="table-borderless table table-striped table-hover table-fw-widget dataTable">
                                            <thead>
                                                <tr>
                                                    <th >Seller Center</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="no-border-x">
                                                <?php

                                                foreach ($marketplace['childChannels'] as $single_user_channels) :

                                                    $single_user_connect_status = Connection::CONNECT_STATUS_GET;
                                                    if ($single_user_channels['connected_count'] > 0) {
                                                        $single_user_connect_status = Connection::CONNECT_STATUS_CONNECTED;
                                                    }
                                                    $single_user_connect_url = $single_user_channels['link_url'] ? $single_user_channels['link_url'] : 'javascript:';
                                                    $single_user_connect_name = $single_user_channels['name'];
                                                    ?>
                                                    <tr>
                                                        <td class="captialize"><?= $single_user_connect_name; ?></td>
                                                        <td><a href="<?php echo $single_user_connect_url; ?>"><?= $single_user_connect_status; ?></a></td>
                                                    </tr>
                                                    <?php
                                                endforeach;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="xs-mt-50 pull-right" style="margin-right: 30px;"> 
                                    <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                                    <!--<button type="button" class="btn btn-space btn-primary">Subscribe</button>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>

        <?php
    }
}
?>


