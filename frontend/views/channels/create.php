<?php

use common\models\Feed;
use common\models\Pinterest;
use common\models\UserFeed;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\db\Query;
use common\models\User;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\UserConnection;;
use frontend\components\Helpers;

/* @var $this yii\web\View */
/* @var $model backend\models\Channels */

$this->title = 'Global Commerce Connector';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => ['/index']];
$this->params['breadcrumbs'][] = 'Add New';
?>

<div class="page-head">
    <h2 class="page-head-title">Add New</h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>

<div class="main-content container-fluid">
    <div class="row marketplace-listing">
        <?php
        $user_id = Yii::$app->user->identity->id;
        $connection_parents = ConnectionParent::find()->orderBy(['name' => SORT_ASC])->all();
        foreach ($connection_parents as $channels_data) {

            $childChonnections = $channels_data->getChildConnections();

            $childChannelCnt = count($childChonnections);
            $multiseller_center_connected_channel_count = 0;
            $isEnable = Connection::CONNECTED_ENABLED_YES;
            if (isset($channels_data) and ! empty($channels_data)) {
                foreach ($childChonnections as $single_multiseller_channel) {
                    $isEnable = $single_multiseller_channel->enabled;
                    $multiseller_center_connected_channel_count += UserConnection::find()->where(['user_id' => $user_id, "connection_id"=>$single_multiseller_channel->id])->available()->count();
                }
            }
            if($isEnable==Connection::CONNECTED_ENABLED_NO)
                continue;
            ?>
            <?php
            if ($childChannelCnt == 1) {
                if ( $channels_data->name === 'Square' || $channels_data->name === 'NetSuite' )
                    continue;

                if ($channels_data->name == 'Instagram') {
                    $connection_row = Connection::find()->where(['name' => 'Instagram'])->one();
                    $channel_id = $connection_row->id;
                    ?>
                    <div class="col-sm-3">
                        <div class="bs-grid-block" data-toggle="modal"  data-target="#connection_parent_<?=$channel_id?>">
                            <div class="content">
                                <span class="label label-success custom-span-channels-label span-top-22" style=" position: absolute; right: 30px; ">Coming Soon</span>
                                <img src="<?php echo $channels_data->image_url; ?>" style="<?php echo $channels_data->name == 'WeChat' ? '' : 'width: 100%;' ?>" alt="<?php echo $channels_data->id; ?>" class="resize_Xiao">
                            </div>
                        </div>
                    </div>
                    <?php
                } else if ($channels_data->name == 'Google Shopping') {
                    $user_id = Yii::$app->user->identity->id;
                    $user_data = User::find()->where(['id' => $user_id, 'google_feed' => 1])->one();
                    if (!empty($user_data)):
                        $google_enable = 'yes';
                    else:
                        $google_enable = 'no';
                    endif;

                    if ($google_enable == 'yes') {
                        ?>
                        <div class="col-sm-3">
                            <a href="<?php echo "/googleshopping?id=" .$channels_data->id ?>">
                                <div class="bs-grid-block connected">

                                    <div class="content">
                                        <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                        <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                    </div>

                                </div>
                            </a>
                        </div>
                    <?php } else {
                        ?>
                        <div class="col-sm-3">
                            <div class="bs-grid-block">
                                <div class="content">
                                    <a href="<?php echo "/googleshopping?id=" .$channels_data->id ?>"><img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao"></a>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                } else if ($channels_data->name == 'Facebook') {
                    $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
                    $fb_feed_count = UserFeed::find()->where(["feed_id"=>$fb_feed_model->id, "user_id"=>$user_id])->count();
                    if ($fb_feed_count>0) {
                        ?><div class="col-sm-3">
                        <a href="/facebook">
                            <div class="bs-grid-block connected">

                                <div class="content">
                                    <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                    <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                </div>

                            </div>
                        </a>
                        </div><?php
                    } else {
                        ?>
                        <div class="col-sm-3">
                        <div class="bs-grid-block">
                            <div class="content">
                                <a href="/facebook"><img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao"></a>
                            </div>
                        </div>
                        </div><?php
                    }
                    ?>

                    <?php
                } else if ($channels_data->name == 'Flipkart') {

                    $facebook = Connection::find()->where(['name' => 'Flipkart'])->one();
                    $channel_id = $facebook->id;
                    $fb_connected = UserConnection::find()->Where(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $channel_id])->one();
                    if (isset($fb_connected) and ! empty($fb_connected)) {
                        ?><div class="col-sm-3">
                        <a href="/channels/flipkart">
                            <div class="bs-grid-block connected">

                                <div class="content">
                                    <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                    <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                </div>

                            </div>
                        </a>
                        </div><?php
                    } else {
                        ?>
                        <div class="col-sm-3">
                        <div class="bs-grid-block">
                            <div class="content">
                                <a href="/channels/flipkart"><img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao"></a>
                            </div>
                        </div>
                        </div><?php
                    }
                    ?>
                    <?php
                } else if ($channels_data->name == 'TMall') {

                    $facebook = Connection::find()->where(['name' => 'TMall'])->one();
                    $channel_id = $facebook->id;
                    $fb_connected = UserConnection::find()->Where(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $channel_id])->one();
                    if (isset($fb_connected) and ! empty($fb_connected)) {
                        ?><div class="col-sm-3">
                        <a href="/tmall<?="?id=".$channels_data->id?>">
                            <div class="bs-grid-block connected">

                                <div class="content">
                                    <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                    <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                </div>

                            </div>
                        </a>
                        </div><?php
                    } else {
                        ?>
                        <div class="col-sm-3">
                        <div class="bs-grid-block">
                            <div class="content">
                                <a href="/tmall<?="?id=".$channels_data->id?>"><img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao"></a>
                            </div>
                        </div>
                        </div><?php
                    }
                    ?>
                <?php } else if ($channels_data->name == 'Pinterest') {

                    $p_feed_model = Feed::findOne(["name"=>"pinterest"]);
                    $p_feed_count = UserFeed::find()->where(["feed_id"=>$p_feed_model->id, "user_id"=>$user_id])->count();
                        if ($p_feed_count>0) {
                            ?><div class="col-sm-3">
                            <a href="/pinterest">
                                <div class="bs-grid-block connected">

                                    <div class="content">
                                        <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                        <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                    </div>
                                </div>
                            </a>
                        </div><?php
                        } else {
                        ?>
                        <div class="col-sm-3">
                            <div class="bs-grid-block">
                                <div class="content">
                                    <a href="/pinterest">
                                        <img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao">
                                    </a>
                                </div>
                            </div>
                        </div><?php
                        }
                        ?>

                        <?php
                    } else if ($channels_data->name == 'Rakuten') {

                        $facebook = Connection::find()->where(['name' => 'Rakuten'])->one();
                        $channel_id = $facebook->id;
                        $fb_connected = UserConnection::find()->Where(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $channel_id])->one();
                        if (isset($fb_connected) and ! empty($fb_connected)) {
                            ?><div class="col-sm-3">
                            <a href="/channels/rakuten">
                                <div class="bs-grid-block connected">

                                    <div class="content">
                                        <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                        <img src="<?php echo $channels_data->image_url; ?>" alt="" style="width: 100%" class="resize_Xiao">
                                    </div>
                                </div>
                            </a>
                            </div><?php
                        } else {
                            ?>
                            <div class="col-sm-3">
                            <div class="bs-grid-block">
                                <div class="content">
                                    <a href="javascript:" data-toggle="modal" class=""  data-target="#connection_parent_<?=$channels_data->id?>"><img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao"></a>
                                </div>
                            </div>
                            </div><?php
                        }
                        ?>

                        <?php

                }
                elseif($channels_data->name == 'Jet'){

                    $jet = Connection::findOne(['name' => 'Jet']);
                    $jet_id = $jet->id;
                    $jet_connected = UserConnection::findOne(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $jet_id]);

                    if (isset($jet_connected) and !empty($jet_connected)) {
                        $connected_jet_link = "/channelsetting?id=".$jet_id."&type=".Connection::CONNECTION_TYPE_CHANNEL."&u=".$jet_connected->id;
                        ?>
                        <div class="col-sm-3">
                        <a href="<?php echo $connected_jet_link?>">
                            <div class="bs-grid-block connected">

                                <div class="content">
                                    <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; ">Connected</span>
                                    <img src="<?php echo $channels_data->image_url; ?>" alt="Jet" style="width: 100%" class="resize_Xiao">
                                </div>

                            </div>
                        </a>
                        </div><?php
                    } else {
                        ?>
                        <div class="col-sm-3">
                            <div class="bs-grid-block">
                                <div class="content">
                                    <a href="/channels/jet">
                                        <img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="Jet" class="resize_Xiao">
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php }
                }   else {
                    ?>
                    <div class="col-sm-3">
                        <div class="bs-grid-block tester">
                            <div class="content">
                                <img src="<?php echo $channels_data->image_url; ?>" style="width: 100%;" alt="" class="resize_Xiao">
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php
            } else if ($childChannelCnt != 1) {
                $channel_id = $channels_data->id;
                $connection_status = $multiseller_center_connected_channel_count . ' of ' . $childChannelCnt;
                ?>

                <div class="col-sm-3">
                    <div class="bs-grid-block <?=$multiseller_center_connected_channel_count>0?"connected" : ""?>" data-toggle="modal"  data-target="#connection_<?=$channel_id?>">
                        <div class="content">
                            <?php if ($multiseller_center_connected_channel_count > 0): ?>
                                <span  class="label label-success span-top-22" style=" position: absolute; right: 30px; "><?php echo $connection_status; ?></span>
                            <?php endif; ?>

                            <img src="<?php echo $channels_data->image_url; ?>" style="width: 100%" alt="<?php echo $channel_id ?>" class="resize_Xiao">
                        </div>
                    </div>
                </div>
                <?php
            }
            //}
        }
        ?>
    </div>
</div>

<?php

foreach ($connection_parents as $channels_data1) {
    $channel_id = $channels_data->id;
    $childChannelIds = $channels_data1->getChildConnections();


    $isEnable = Connection::CONNECTED_ENABLED_YES;
    if (isset($childChannelIds) and ! empty($childChannelIds)) {
        foreach ($childChannelIds as $single_multiseller_channel) {
            $isEnable = $single_multiseller_channel->enabled;
        }
    }
    if($isEnable==Connection::CONNECTED_ENABLED_NO)
        continue;

    $count_parent_name1 =  count($childChannelIds);
    $parent_name1 = $channels_data1->name;

    if ( $parent_name1 === 'Square' || $parent_name1 === 'NetSuite' )
        continue;

    if (!Helpers::isExistTopChannelItem(Yii::$app->session->get("channel_permission"), $parent_name1)) {
        continue;
    }
    $marketplace1 = Connection::find()->channel()->Where(['parent_id' => $channels_data1->id])->one();
    if (empty($marketplace1)):
        $name = "test";
    else:
        $name = $marketplace1->getConnectionName();
    endif;
    ?>
    <?php if ($count_parent_name1 == 1) { ?>
        <div id="<?php echo "connection_parent_" . $marketplace1->id; ?>" tabindex="-1" role="dialog" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="text-primary"><img src="<?php echo $marketplace1->getConnectionImage(); ?>" style="width: 50%" alt=""></span></div>
                            <?php if ($marketplace1->name == 'Instagram') { ?>
                                <h3>Coming Soon</h3>
                            <?php } ?>
                            <!-- <p>Subscribe to get updated when <?= $marketplace1->name ?> becomes available in Elliot.</p> -->
                            <div class="xs-mt-50">
                                <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                                <?php if (in_array($marketplace1->name, [
                                    'Google Shopping',
                                    'Shiphawk',
                                    'Shiphero',
                                    'Instagram'
                                ])): ?>
                                    <?php
                                    $single_user_connect_url = "";
                                    ?>
                                    <!-- <a href="<?php echo $single_user_connect_url; ?>">
                                        <button type="button" class="btn btn-space btn-primary">Subscribe</button>
                                    </a> -->

                                <?php else: ?>
                                    <!-- <button type="button" data-dismiss="modal" class="btn btn-space btn-primary subscribeChannel" data-type="<?php echo $marketplace1->name ?>">
                                        Subscribe
                                    </button> -->
                                <?php endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    <?php } else if ($count_parent_name1 != 1) { ?>
        <div id="<?php echo "connection_" . $channels_data1->id; ?>" tabindex="-1" role="dialog" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <img src="<?php echo $channels_data1->image_url; ?>" style="width: 50%" alt="">
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="">
                            <!--<div class="text-primary text-center"></span></div>-->
                            <div class="row">
                                <div class="form-group">
                                    <?php

                                    $channels_group_data = Connection::find()->channel()->Where(['parent_id' => $channels_data1->id])->all();
                                    $arr = array();
                                    foreach ($channels_group_data as $val) {
                                        $arr[] = $val['name'];
                                    }
                                    sort($arr);
                                    ?>
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
                                            if (isset($_GET['a']) and ! empty($_GET['a'])) {
                                                echo"<pre>";
                                                print_R($channels_group_data);
                                                die;
                                            }

                                            foreach ($channels_group_data as $single_multiseller_channel) :
                                                $status_lazada = 'Get Connected';
                                                $link = '/'.strtolower($parent_name1).'?id='.$single_multiseller_channel->id;

                                                if (UserConnection::find()->where(['user_id' => $user_id, "connection_id"=>$single_multiseller_channel->id])->available()->count()>0) {
                                                    $status_lazada = 'Connected';
                                                }
                                                ?>
                                                <tr>
                                                    <td class="captialize"><?= $single_multiseller_channel->getConnectionName(); ?></td>
                                                    <td><a href="<?php echo $link; ?>"><?= $status_lazada; ?></a></td>
                                                </tr>
                                            <?php
                                            endforeach;
                                            ?>
                                            </tbody>
                                        </table>
                                    </div><?php
                                    //                                    }
                                    ?>


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






