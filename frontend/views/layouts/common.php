<?php
/**
 * @var $this yii\web\View
 */

use common\models\Feed;
use common\models\Fulfillment;
use common\models\FulfillmentList;
use common\models\Notification;
use common\models\Order;
use common\models\Pinterest;
use common\models\User;
use common\models\Connection;
use common\models\UserConnection;
use common\models\UserFeed;
use frontend\assets\FrontendAsset;
use frontend\components\CustomFunction;
use frontend\components\MenuComponent;
use frontend\widgets\LeftMenu;
use common\widgets\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\log\Logger;
use yii\web\View;
use yii\widgets\Breadcrumbs;
use frontend\widgets\SpinnerWidget;

$bundle = FrontendAsset::register($this);

$user_name = Yii::$app->user->identity->getPublicIdentity();
$user_id = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER)
    $user_connection_count = Yii::$app->user->identity->parent_id;

$flash_msg = '';
$cls_header_title = 'show';
$cls_header_flash = 'hide';
if (Yii::$app->session->hasFlash('success')) {
    $flash_msg = Yii::$app->session->getFlash('success');
    $cls_header_title = 'hide';
    $cls_header_flash = 'show';
    $header_cls = 'be-color-header be-color-header-success';
}
else if (Yii::$app->session->hasFlash('warning')) {
    $flash_msg = Yii::$app->session->getFlash('warning');
    $cls_header_title = 'hide';
    $cls_header_flash = 'show';
    $header_cls = 'be-color-header be-color-header-warning';
}
else if (Yii::$app->session->hasFlash('danger')) {
    $flash_msg = Yii::$app->session->getFlash('danger');
    $cls_header_title = 'hide';
    $cls_header_flash = 'show';
    $header_cls = 'be-color-header be-color-header-danger';
}
else if (Yii::$app->session->hasFlash('info')) {
    $flash_msg = Yii::$app->session->getFlash('info');
    $cls_header_title = 'hide';
    $cls_header_flash = 'show';
    $header_cls = 'be-color-header';
}
else {
    $header_cls = '';
}

$page_title_style = "display:none";

$page_header = $this->title;
if ($page_header == 'Dashboard | Elliot'):
    $page_header = 'Dashboard';
endif;

// Check For Shipstation title
if ($page_header == 'ShipStation'){

    $shipStationModel = FulfillmentList::findOne(['name' => 'ShipStation']);

    $shipStation_Connect = Fulfillment::findOne(['user_id' => $user_id, 'fulfillment_list_id' => $shipStationModel->id ]);
    if ( empty($shipStation_Connect) ) {
        $page_title_style = "";
        $page_header = 'ShipStation - <a href="/fulfillment/instruction" target="_blank" class="shipanchor"><h4>Instructions for Connecting to Elliot</h4></a>';
    }

}


//Get all Notifications
$new_notifs = Notification::find()->where([
    'status' => Notification::NOTIFICATION_STATUS_UNREAD,
    'user_id' => $user_id
])->orderBy(['created_at' => SORT_DESC])->all();
if(Yii::$app->user->identity->level != User::USER_LEVEL_MERCHANT_USER){
    $connected_channels = UserConnection::find()->where(['user_id' => $user_id])->available()->all();
//$connected_channels = UserConnection::find()->where(['connected' => UserConnection::CONNECTED_YES, 'user_id' => $user_id])->available()->all();
    $array_connected_channels = [];
    foreach ($connected_channels as $connected_channel){
        $connected_channel_detail = Connection::find()->where(['id'=>$connected_channel->connection_id])->one();
        $array_connected_channels[] = array(
            "id"=> $connected_channel->connection_id,
            "user_connection_id"=> $connected_channel->id,
            "text"=>$connected_channel->getPublicName(),
            "type"=>$connected_channel_detail->type_id);
    }
    $menu_connected_channels = [];
    foreach ($array_connected_channels as $connected_channel){
        $menu_connected_channels[] = array(
            'label' => Yii::t('frontend', $connected_channel['text']),
            'url' => ["/channelsetting?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
            'active' => (\Yii::$app->controller->id == "channelsetting" && Yii::$app->request->get()['u']==$connected_channel['user_connection_id'])
        );
    }
    $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
    $fb_feed_count = UserFeed::find()->where(["feed_id"=>$fb_feed_model->id, "user_id"=>$user_id])->count();
    if($fb_feed_count>0){
        $menu_connected_channels[] = array(
            'label' => Yii::t('frontend', "Facebook"),
            'url' => ["/facebook"],
            'active' => (\Yii::$app->controller->id == "facebook")
        );
    }
    $p_feed_model = Feed::findOne(["name"=>"pinterest"]);
    $p_feed_count = UserFeed::find()->where(["feed_id"=>$p_feed_model->id, "user_id"=>$user_id])->count();
    if($p_feed_count>0){
        $menu_connected_channels[] = array(
            'label' => Yii::t('frontend', "Pinterest"),
            'url' => ["/pinterest"],
            'active' => (\Yii::$app->controller->id == "pinterest")
        );
    }
    $menu_connected_peoples = [];
    foreach ($array_connected_channels as $connected_channel){
        $inactiveCustomers = \common\models\Customer::find()->where(["visible"=>\common\models\Customer::VISIBLE_NO, 'user_id' => $user_id, 'user_connection_id' => $connected_channel['user_connection_id']])->count();
        if($inactiveCustomers>0){
            $menu_connected_peoples[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => '#',
                'options' => ['class' => 'parent'],
                'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']) || (\Yii::$app->controller->action->uniqueId == 'people/inactive-customers' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                'items' => [
                    [
                        'label' => Yii::t('frontend', 'View All'),
                        'url' => ["/people/connected-customer?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                    [
                        'label' => Yii::t('frontend', 'Hidden People'),
                        'url' => ["/people/inactive-customers?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'people/inactive-customers' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                ]
            );
        }
        else{
            $menu_connected_peoples[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => ["/people/connected-customer?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
            );
        }
    }
    $menu_connected_orders = [];
    foreach ($array_connected_channels as $connected_channel){
        $inactiveOrders= \common\models\Order::find()->where(["visible"=>\common\models\Order::ORDER_VISIBLE_INACTIVE, 'user_id' => $user_id, 'user_connection_id' => $connected_channel['user_connection_id']])->count();
        if($inactiveOrders>0){
            $menu_connected_orders[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => '#',
                'options' => ['class' => 'parent'],
                'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']) || (\Yii::$app->controller->action->uniqueId == 'order/inactive-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                'items' => [
                    [
                        'label' => Yii::t('frontend', 'View All'),
                        'url' => ["/order/connected-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                    [
                        'label' => Yii::t('frontend', 'Hidden Order'),
                        'url' => ["/order/inactive-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'order/inactive-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id'])
                    ],
                ]
            );
        }
        else{
            $menu_connected_orders[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => ["/order/connected-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
            );
        }
    }
}
else{
    $real_user_id = empty(Yii::$app->user->identity->parent_id) ? Yii::$app->user->identity->id : Yii::$app->user->identity->parent_id;
    $user_permission = Yii::$app->user->identity->getPermission();
    $connected_channels = UserConnection::find()->where(['user_id' => $real_user_id])->available()->all();
    $permission_channels = explode(", ", $user_permission->channel_permission);
    $permission_menuss = explode(", ", $user_permission->menu_permission);
//$connected_channels = UserConnection::find()->where(['connected' => UserConnection::CONNECTED_YES, 'user_id' => $user_id])->available()->all();
    $array_connected_channels = [];
    foreach ($connected_channels as $connected_channel){
        if(in_array($connected_channel->connection_id, $permission_channels)){
            $connected_channel_detail = Connection::find()->where(['id'=>$connected_channel->connection_id])->one();
            $array_connected_channels[] = array(
                "id"=> $connected_channel->connection_id,
                "user_connection_id"=> $connected_channel->id,
                "text"=>$connected_channel->getPublicName(),
                "type"=>$connected_channel_detail->type_id);
        }
    }
    $menu_connected_channels = [];
    foreach ($array_connected_channels as $connected_channel){
        $menu_connected_channels[] = array(
            'label' => Yii::t('frontend', $connected_channel['text']),
            'url' => ["/channelsetting?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
            'active' => (\Yii::$app->controller->id == "channelsetting" && Yii::$app->request->get()['u']==$connected_channel['user_connection_id'])
        );
    }
    $fb_connection = Connection::findOne(['name'=>'Facebook']);
    if(in_array($fb_connection->id, $permission_channels)){
        $fb_feed_model = Feed::findOne(["name"=>"facebook"]);
        $fb_feed_count = UserFeed::find()->where(["feed_id"=>$fb_feed_model->id, "user_id"=>$real_user_id])->count();
        if($fb_feed_count>0){
            $menu_connected_channels[] = array(
                'label' => Yii::t('frontend', "Facebook"),
                'url' => ["/facebook"],
                'active' => (\Yii::$app->controller->id == "facebook")
            );
        }
    }
    $pin_connection = Connection::findOne(['name'=>'Pinterest']);
    if(in_array($pin_connection->id, $permission_channels)) {
        $p_feed_model = Feed::findOne(["name" => "pinterest"]);
        $p_feed_count = UserFeed::find()->where(["feed_id" => $p_feed_model->id, "user_id" => $real_user_id])->count();
        if ($p_feed_count > 0) {
            $menu_connected_channels[] = array(
                'label' => Yii::t('frontend', "Pinterest"),
                'url' => ["/pinterest"],
                'active' => (\Yii::$app->controller->id == "pinterest")
            );
        }
    }
    $menu_connected_peoples = [];
    foreach ($array_connected_channels as $connected_channel){
        $inactiveCustomers = \common\models\Customer::find()->where(["visible"=>\common\models\Customer::VISIBLE_NO, 'user_id' => $real_user_id, 'user_connection_id' => $connected_channel['user_connection_id']])->count();
        if($inactiveCustomers>0){
            $menu_connected_peoples[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => '#',
                'options' => ['class' => 'parent'],
                'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']) || (\Yii::$app->controller->action->uniqueId == 'people/inactive-customers' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                'items' => [
                    [
                        'label' => Yii::t('frontend', 'View All'),
                        'url' => ["/people/connected-customer?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                    [
                        'label' => Yii::t('frontend', 'Hidden People'),
                        'url' => ["/people/inactive-customers?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'people/inactive-customers' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                ]
            );
        }
        else{
            $menu_connected_peoples[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => ["/people/connected-customer?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
            );
        }
    }
    $menu_connected_orders = [];
    foreach ($array_connected_channels as $connected_channel){
        $inactiveOrders= \common\models\Order::find()->where(["visible"=>\common\models\Order::ORDER_VISIBLE_INACTIVE, 'user_id' => $real_user_id, 'user_connection_id' => $connected_channel['user_connection_id']])->count();
        if($inactiveOrders>0){
            $menu_connected_orders[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => '#',
                'options' => ['class' => 'parent'],
                'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']) || (\Yii::$app->controller->action->uniqueId == 'order/inactive-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                'items' => [
                    [
                        'label' => Yii::t('frontend', 'View All'),
                        'url' => ["/order/connected-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
                    ],
                    [
                        'label' => Yii::t('frontend', 'Hidden Order'),
                        'url' => ["/order/inactive-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                        'active' => (\Yii::$app->controller->action->uniqueId == 'order/inactive-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id'])
                    ],
                ]
            );
        }
        else{
            $menu_connected_orders[] = array(
                'label' => Yii::t('frontend', $connected_channel['text']),
                'url' => ["/order/connected-order?id={$connected_channel['id']}&type={$connected_channel['type']}&u={$connected_channel['user_connection_id']}"],
                'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order' && Yii::$app->request->get()['u']==$connected_channel['user_connection_id']),
            );
        }
    }
}


$fulfillmentSoftwareConnectLists = MenuComponent::FulfillmentSoftwareConnectLists($user_id);
?>
<?php $this->beginContent('@frontend/views/layouts/base.php'); ?>
<div class="be-wrapper be-fixed-sidebar <?php echo $header_cls ?> be-loading">

<!--    navbar start-->
    <nav class="navbar navbar-default navbar-fixed-top be-top-header">
        <div class="container-fluid">
            <div class="navbar-header"><a href="/" class="navbar-brand"></a>
            </div>
            <div class="be-right-navbar be-right-navbar-flex">

                <ul class="nav navbar-nav navbar-right be-user-nav">
                    <li class="dropdown acount-dropdown">
                        <a href="javascript:" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                            <img src="<?php echo Yii::$app->user->identity->userProfile->getPhoto($this->assetManager->getAssetUrl($bundle, 'img/avatar-150.png'))?>" alt="Avatar">
                            <span class="user-name"><?php echo $user_name; ?></span>
                        </a>

                        <ul role="menu" class="dropdown-menu">
                            <li>
                                <div class="user-info">
                                    <div class="user-name captialize"><?php echo $user_name; ?></div>
                                    <div class="user-position online">Available</div>
                                </div>
                            </li>
                            <li><a href="/user/profile"><span class="icon mdi mdi-face"></span> Account</a></li>
                            <li><a href="/user/profile/change-password"><span class="icon mdi mdi-settings"></span>Change Password</a></li>
                            <li><a href="/user/sign-in/logout" data-method="Post"><span class="icon mdi mdi-power"></span> Logout</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="page-title" style="<?php echo $page_title_style;?>">
                    <span class="flash-message title <?= $cls_header_title ?>"><?php echo $page_header; ?></span>
                    <span class="flash-message msg <?= $cls_header_flash ?>"><?php echo $flash_msg; ?></span>
                </div>

                <div class="search-container">
<!--                    <div class="input-group input-group-sm" style="max-width: 100%;">-->
<!--                        <input type="text" name="search" placeholder="Search..." class="form-control search-input" id = "input_algolia">-->
<!--                        <span class="input-group-btn">-->
<!--                            <button type="button" class="btn btn-primary" id="btn_algolia">Search</button>-->
<!--                        </span>-->
<!--                    </div>-->
                </div>

                <style>
                    #noti_bar_mobile{
                        color:#4285f4;;
                    }
                    @media (max-width: 767px){
                        #noti_bar_mobile {
                            color:#ffffff;
                        }
                    }
                </style>
                <ul class="elliot_notif nav navbar-nav navbar-right be-icons-nav">
                    <li class="dropdown notif"><a href="javascript:" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                        <span class="icon mdi mdi-notifications" id="noti_bar_mobile"></span>
                            <?php if (!empty($new_notifs)): ?>
                                <span class="indicator"></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu be-notifications">
                            <li>
                                <div class="title">Notifications<span class="badge"><?= count($new_notifs) ?></span></div>
                                <div class="list">
                                    <div class="be-scroller">
                                        <div class="content">
                                            <ul>
                                                <?php foreach ($new_notifs as $notif): ?>
                                                    <li id="li_<?= $notif->id; ?>" class="notification notification-unread">
                                                        <a href="javascript: ">
                                                            <div class="notification-info" id="<?= $notif->id ?>">
                                                                <div class="text">
                                                                    <?php echo strrpos($notif->message, ".") ? $notif->message : $notif->message . '.'; ?>
                                                                    <span id="<?= $notif->id ?>" class="mdi mdi-check-circle notif_check" title="Click to clear the notification"></span>
                                                                </div>
                                                            </div>
                                                        </a>

                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="footer"> <a href="javascript:">View all notifications</a></div>-->
                            </li>
                        </ul>
                    </li>
                    <?php if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT) { ?>
                        <style>
                            #search_bar_mobile{
                                display:none;
                                padding: 10px;
                            }
                            @media (max-width: 767px){
                                #search_bar_mobile {
                                    display: inline-block;
                                }
                            }
                        </style>
                        <li class="dropdown connect">
                            <a href="javascript:" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                                <span class="icon mdi mdi-apps"></span>
                            </a>
                            <ul class="dropdown-menu be-connections test tc-scroll">
                                <li>
                                    <div class="list">
                                        <div class="content">

                                            <?php
                                            $connections = Connection::find()
                                                ->where(['type_id' => Connection::CONNECTION_TYPE_STORE, 'enabled' => Connection::CONNECTED_ENABLED_YES])
                                                ->orderBy(['name' => SORT_ASC])->all();

                                            $connections_index = 0;
                                            $all_connections_count = count($connections) - 1;
                                            foreach ($connections as $each_Connection){
                                                $connection_Img = $each_Connection->image_url;
                                                $connection_Name = $each_Connection->name;
                                                $connectionId = $each_Connection->id;
                                                ?>
                                                <?php
                                                if ($connections_index % 2 == 0){
                                                    echo "<div class='row'>";
                                                }
                                                ?>

                                                <div class="col-sm-6">
                                                    <a href="/stores/<?php echo strtolower($connection_Name)."?id=".$connectionId;?>" class="connection-item">
                                                        <img src="<?php echo $connection_Img?>" alt="<?php echo $connection_Name;?>">
                                                        <span><?php echo $connection_Name?></span>
                                                        <?php
                                                        $user_connection_count = UserConnection::find()->where(['connection_id' => $connectionId, 'user_id' => $user_id])->count();
                                                        if ($user_connection_count > 0)
                                                        {
                                                            ?>
                                                            <span class="label label-success span-top-22" style="display: inline-block;margin-right: 8px;">Connected</span>
                                                            <span class="label label-success span-top-22" style="display: inline-block;"><?php echo $user_connection_count; ?></span>
                                                            <?php
                                                        }
                                                        ?>
                                                    </a>
                                                </div>
                                                <?php
                                                if ($connections_index % 2 == 1 || $connections_index == $all_connections_count){
                                                    echo "</div>";
                                                }
                                                ?>

                                                <?php
                                                $connections_index ++;
                                            }
                                            ?>

                                        </div>
                                    </div>
                                    <div class="footer"> <a href="/channels">Connect to Marketplaces</a></div>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
<!--navbar end-->
<!--    search bar start-->
<!--    <div id="search_bar_mobile">-->
<!--        <div class="search-container">-->
<!--            <div class="input-group input-group-sm" style="max-width: 100%;">-->
<!--                <input type="text" name="search" placeholder="Search..." class="form-control search-input" id = "input_algolia_mobile">-->
<!--                <span class="input-group-btn">-->
<!--                    <button type="button" class="btn btn-primary" id="btn_algolia_mobile">Search</button>-->
<!--                </span>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    search bar end-->
<!--    left side menu start-->

    <div class="be-left-sidebar">
        <div class="left-sidebar-wrapper">
<!--            main left menu content start-->
            <a href="javascript:" class="left-sidebar-toggle">Dashboard</a>
            <div class="left-sidebar-spacer">
                <div class="left-sidebar-scroll">
                    <div class="left-sidebar-content">

                        <?php
                        echo LeftMenu::widget([
                            'options' => ['class' => 'sidebar-elements'],
                            'linkTemplate' => '<a href="{url}">{icon}<span>{label}</span>{right-icon}</a>',
                            'submenuTemplate' => "\n<ul class=\"treeview-menu\">\n{items}\n</ul>\n",
                            'activateParents' => true,
                            'items' => [
                                [
                                    'label' => Yii::t('frontend', 'Main'),
                                    'options' => ['class' => 'divider'],
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Dashboard'),
                                    'icon' => '<i class="icon mdi mdi-home"></i>',
                                    'url' => ['/'],
                                    'active' => (\Yii::$app->controller->id == 'dashboard')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Products'),
                                    'url' => '#',
                                    'icon' => '<i class="icon mdi mdi-collection-item"></i>',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(\Yii::$app->controller->id,['product']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'View All'),
                                            'url' => ['/product'],
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'product/index'),
                                            'visible' => CustomFunction::checkPermissionMenu(3),
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Add New'),
                                            'url' => ['/product/create'],
                                            'visible' => CustomFunction::checkPermissionMenu(4),
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'product/create')
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Connected Channels'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id, ['product-connected', ]),
                                            'visible' => CustomFunction::checkConnectedChannel( $user_id, $user_level ),
                                            'items' => CustomFunction::getConnectedChannel($user_id, $user_level)

                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Attributes'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'visible' => CustomFunction::checkPermissionMenu(5),
                                            'active' => in_array(Yii::$app->controller->id,['attributes', ]),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/attributes'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'attributes/index')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Add New'),
                                                    'url' => ['/attributes/create'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'attributes/create')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Attribute Types'),
                                                    'url' => '#',
                                                    'options' => ['class' => 'parent'],
                                                    'active' => in_array(Yii::$app->controller->id,['attribute-type', ]),
                                                    'items' => [
                                                        [
                                                            'label' => Yii::t('frontend', 'View All'),
                                                            'url' => ['/attribute-type'],
                                                            'active' => (\Yii::$app->controller->action->uniqueId == 'attribute-type/index')
                                                        ],
                                                        [
                                                            'label' => Yii::t('frontend', 'Add New'),
                                                            'url' => ['/attribute-type/create'],
                                                            'active' => (\Yii::$app->controller->action->uniqueId == 'attribute-type/create')
                                                        ],
                                                    ]
                                                ],

                                            ]
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Categories'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'visible' => CustomFunction::checkPermissionMenu(6),
                                            'active' => in_array(Yii::$app->controller->id,['categories', ]),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/categories'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'categories/index')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Add New'),
                                                    'url' => ['/categories/create'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'categories/create')
                                                ],
                                            ]
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Variations'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'visible' => CustomFunction::checkPermissionMenu(7),
                                            'active' => in_array(Yii::$app->controller->id,['variations', ]),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/variations'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'variations/index')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Add New'),
                                                    'url' => ['/variations/create'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'variations/create'),
                                                    'visible' => false
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Variations Set'),
                                                    'url' => '#',
                                                    'options' => ['class' => 'parent'],
                                                    'active' => in_array(Yii::$app->controller->id,['variations-set', ]),
                                                    'items' => [
                                                        [
                                                            'label' => Yii::t('frontend', 'View All'),
                                                            'url' => ['/variations-set'],
                                                            'active' => (\Yii::$app->controller->action->uniqueId == 'variations-set/index')
                                                        ],
                                                        [
                                                            'label' => Yii::t('frontend', 'Add New'),
                                                            'url' => ['/variations-set/create'],
                                                            'active' => (\Yii::$app->controller->action->uniqueId == 'variations-set/create'),
                                                            'visible' => false
                                                        ],
                                                    ]
                                                ],
                                            ]
                                        ],

                                    ]
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Orders'),
                                    'url' => '#',
                                    'icon' => '<i class="icon mdi mdi-receipt"></i>',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(Yii::$app->controller->id,['order']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'View All'),
                                            'url' => ['/order'],
                                            'visible' => CustomFunction::checkPermissionMenu(8),
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'order/index')
                                        ],
                                        (count($menu_connected_orders) > 0) ? [
                                            'label' => Yii::t('frontend', 'Connected Channels'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'order/connected-order'),
                                            'items' => $menu_connected_orders
                                        ]: [],
                                    ]
                                ],
                                [
                                    'label' => Yii::t('frontend', 'People'),
                                    'url' => '#',
                                    'icon' => '<i class="icon mdi mdi-face"></i>',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(Yii::$app->controller->id,['people']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'View All'),
                                            'url' => ['/people'],
                                            'visible' => CustomFunction::checkPermissionMenu(1),
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'people/index')
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Add New'),
                                            'url' => ['/people/create'],
                                            'visible' => CustomFunction::checkPermissionMenu(2),
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'people/create')
                                        ],
                                        (count($menu_connected_peoples) > 0) ? [
                                            'label' => Yii::t('frontend', 'Connected Channels'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'people/connected-customer'),
                                            'items' => $menu_connected_peoples
                                        ] : [],

                                    ]
                                ],
//                                [
//                                    'label' => Yii::t('frontend', 'Content'),
//                                    'url' => '#',
//                                    'icon' => '<i class="icon mdi mdi-collection-image-o"></i>',
//                                    'options' => ['class' => 'parent'],
//                                    'active' => in_array(Yii::$app->controller->id,['page','article','article-category','widget-text','widget-menu','widget-carousel']),
//                                    'items' => [
//                                        [
//                                            'label' => Yii::t('frontend', 'View All'),
//                                            'url' => ['/content/index'],
//                                            'active' => (\Yii::$app->controller->action->uniqueId == 'content/index')
//                                        ],
//                                        [
//                                            'label' => Yii::t('frontend', 'Add New'),
//                                            'url' => ['/content/create'],
//                                            'active' => (\Yii::$app->controller->action->uniqueId == 'content/create')
//                                        ],
//                                    ]
//                                ],
                                [
                                    'label' => Yii::t('frontend', 'Settings'),
                                    'options' => ['class' => 'divider'],
                                ],
                                [
                                    'label' => Yii::t('frontend', 'General'),
                                    'url' => ['/general'],
                                    'active' => (\Yii::$app->controller->id == 'general')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'API Information'),
                                    'url' => ['/api-information'],
                                    'active' => (\Yii::$app->controller->id == 'api-information')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Coporate Documents'),
                                    'url' => ['/documents'],
                                    'active' => (\Yii::$app->controller->id == 'documents')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'User Management'),
                                    'url' => '#',
                                    'options' => ['class' => 'parent'],
                                    'visible' => ($user_level == User::USER_LEVEL_MERCHANT_USER) ? false : true,
                                    'active' => (Yii::$app->controller->id == 'sub-user'),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'View All'),
                                            'url' => ['/sub-user'],
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'sub-user/index')
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Add New'),
                                            'url' => ['/sub-user/create'],
                                            'visible' => CustomFunction::checkCreatedPermission( $user_id ),
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'sub-user/create')
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'User Role'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id,['user-permission', ]),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/user-permission'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'user-permission/index')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Add New'),
                                                    'url' => ['/user-permission/create'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'user-permission/create')
                                                ],
                                            ]
                                        ],

                                    ]
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Integrations'),
                                    'url' => '#',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(Yii::$app->controller->id,['integrations']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'ERP'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id,['integration/erp']),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => '/integrations/erplist',
                                                    'active' => in_array(Yii::$app->controller->id,['integration/erp/viewall']),
                                                ],
//                                                [
//                                                    'label' => Yii::t('frontend', 'NetSuite'),
//                                                    'url' => '/integrations/netsuite',
//                                                    'active' => in_array(Yii::$app->controller->id,['integration/erp/netsuite']),
//                                                ],
                                            ]
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'POS'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id,['integration/pos']),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => '/integrations/pos-all',
                                                    'active' => in_array(Yii::$app->controller->id,['integration/pos/viewall']),
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Square'),
                                                    'url' => '/square?id=63',
                                                    'active' => in_array(Yii::$app->controller->id,['integration/pos/square']),
                                                ],
                                            ]
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Translations'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => (\Yii::$app->controller->action->uniqueId=='integrations/translation-all') || (\Yii::$app->controller->action->uniqueId=='integrations/smartling'),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => '/integrations/translation-all',
                                                    'active' => (\Yii::$app->controller->action->uniqueId=='integrations/translation-all'),
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Smartling'),
                                                    'url' => '/integrations/smartling',
                                                    'active' => (\Yii::$app->controller->action->uniqueId=='integrations/smartling')
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Channels'),
                                    'url' => '#',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(Yii::$app->controller->id,['channels']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'View All'),
                                            'url' => ['/channels'],
                                            'visible' => ($user_level == User::USER_LEVEL_MERCHANT_USER) ? false : true,
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'channels/index')
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Add New'),
                                            'url' => ['/channels/create'],
                                            'visible' => ($user_level == User::USER_LEVEL_MERCHANT_USER) ? false : true,
                                            'active' => (\Yii::$app->controller->action->uniqueId == 'channels/create')
                                        ],
                                        (count($menu_connected_channels) > 0) ? [
                                            'label' => Yii::t('frontend', 'Connected Channels'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id,['channelsetting', ]),
                                            'items' => $menu_connected_channels
                                        ] : [],
                                    ]
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Fulfillment'),
                                    'url' => '#',
                                    'options' => ['class' => 'parent'],
                                    'active' => in_array(Yii::$app->controller->id, ['fulfillment']),
                                    'items' => [
                                        [
                                            'label' => Yii::t('frontend', 'Carriers'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id, ['carriers']),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/fulfillment/carriers'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'fulfillment/carriers')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Available Carriers'),
                                                    'url' => '#',
                                                    'options' => ['class' => 'parent'],
                                                    'active' => in_array(Yii::$app->controller->id, ['sfexpress']),
                                                    'items' => [
                                                        [
                                                            'label' => Yii::t('frontend', 'SF Express'),
                                                            'url' => '#',
                                                            'options' => ['class' => 'parent'],
                                                            'active' => in_array(Yii::$app->controller->id, ['sfexpress']),
                                                            'items' => [
                                                                [

                                                                    'label' => Yii::t('frontend', 'Authorize'),
                                                                    'url' => ['/sfexpress/index'],
                                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'sfexpress/index')
                                                                ],
                                                                [
                                                                    'label' => Yii::t('frontend', 'Pricing Table'),
                                                                    'url' => ['/sfexpress/price'],
                                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'sfexpress/price')
                                                                ],
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            ]
                                        ],
                                        [
                                            'label' => Yii::t('frontend', 'Software Partners'),
                                            'url' => '#',
                                            'options' => ['class' => 'parent'],
                                            'active' => in_array(Yii::$app->controller->id,['fulfillment/software']),
                                            'items' => [
                                                [
                                                    'label' => Yii::t('frontend', 'View All'),
                                                    'url' => ['/fulfillment/software'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'fulfillment/software')
                                                ],
                                                [
                                                    'label' => Yii::t('frontend', 'Connected Channels'),
                                                    'url' => '#',
                                                    'options' => ['class' => 'parent'],
                                                    'active' => (\Yii::$app->controller->action->uniqueId == 'fulfillment/software'),
                                                    'visible' => count($fulfillmentSoftwareConnectLists) > 0,
                                                    'items' => $fulfillmentSoftwareConnectLists,
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
//                                [
//                                    'label' => Yii::t('frontend', 'Billing'),
//                                    'url' => '#',
//                                    'options' => ['class' => 'parent'],
//                                    'active' => in_array(Yii::$app->controller->id,['page']),
//                                    'items' => [
//                                        [
//                                            'label' => Yii::t('frontend', 'Invoices'),
//                                            'url' => ['/order/invoices'],
//                                            'active' => (\Yii::$app->controller->id == 'order/invoices')
//                                        ],
//                                        [
//                                            'label' => Yii::t('frontend', 'Subscriptions'),
//                                            'url' => ['/user/subscription'],
//                                            'active' => (\Yii::$app->controller->id == 'user/subscription')
//                                        ],
//                                    ]
//                                ],
                                [
                                    'label' => Yii::t('frontend', 'General'),
                                    'options' => ['class' => 'divider']
                                ],
                                [
                                    'label' => Yii::t('frontend', 'System Status'),
                                    'icon' => '<i class="icon mdi mdi-info-outline"></i>',
                                    'url' => ['/system/status'],
                                    'active' => (\Yii::$app->controller->action->uniqueId == 'system-status')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'System Updates'),
                                    'icon' => '<i class="icon mdi mdi-notifications-active"></i>',
                                    'url' => ['/system/updates'],
                                    'active' => (\Yii::$app->controller->action->uniqueId == 'system-update')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Terms & Conditions'),
                                    'icon' => '<i class="icon mdi mdi-assignment-check"></i>',
                                    'url' => ['/terms-conditions'],
                                    'active' => (\Yii::$app->controller->action->uniqueId == 'terms-conditions')
                                ],
                                [
                                    'label' => Yii::t('frontend', 'Help Page'),
                                    'icon' => '<i class="icon mdi mdi-pin-help"></i>',
                                    'url' => ['#'],
                                    'template'=> '<a href="https://intercom.help/elliot" target="_blank">{icon}{label}</a>',

                                ]
                            ]
                        ]) ?>


                    </div>
                </div>

            </div>
<!--            main left menu content end-->

<!--            user progress view start-->
            <?php
                $current_y = date('Y');
                $order_amount_total = Order::find()
                    ->andWhere(['user_id' => Yii::$app->user->identity->id])
                    ->andWhere(['=', 'year(order_date)', $current_y])
                    ->sum('total_amount');
                $annual_revenue = Yii::$app->user->identity->annual_revenue;
                if (!empty($annual_revenue) and Yii::$app->user->identity->annual_revenue != '') {
                    $fraction = $order_amount_total / $annual_revenue;
                    $percentage = $fraction * 100;
                    $percentage = number_format((float) $percentage, 2, ".", '');
                }
                else {
                    $percentage = 0;
                }?>
            <div class="progress-widget">
                <div class="progress-data">
                    <span class="progress-value"><?php echo $percentage; ?>%</span>
                    <span class="name">Revenue Projection</span>
                </div>
                <div class="progress">
                    <div style="width: <?php echo $percentage; ?>%;" class="progress-bar progress-bar-primary"></div>
                </div>
            </div>
<!--            user progress view end-->

        </div>


    </div>

<!--    left side menu end-->

    <?php //if(Yii::$app->session->hasFlash('alert')):?>
        <?php //echo Alert::widget([
            //'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
            //'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
        //])
        ?>
    <?php //endif; ?>
    <?php
        echo Alert::widget();
    ?>

    <div class="be-content">
        <?php
            echo $content;
        ?>
    </div>

    <?php
        echo SpinnerWidget::widget([
                'spinnerStyle' => null
        ]);
    ?>

</div><!-- ./wrapper -->
<?php

$interComAppId = env('INTERCOM_APP_ID');
$interComSetting = [
    "app_id" => $interComAppId,
    "email" => Yii::$app->user->identity->email,
    "phone" => Yii::$app->user->identity->userProfile->phoneno,
    "subdomain" => Yii::$app->user->identity->domain,
    "COMPANYNAME" => Yii::$app->user->identity->company,
    "FIRSTNAME" => Yii::$app->user->identity->userProfile->firstname,
    "LASTNAME" => Yii::$app->user->identity->userProfile->lastname,
    "Subscription Plan" => Yii::$app->user->identity->userProfile->subscription_plan,
    "customer since" => Yii::$app->user->identity->created_at
];
$interComSettingJson = Json::encode($interComSetting);
$interComWidgetUrl = "https://widget.intercom.io/widget/".$interComAppId;
$intercomJs = <<< SCRIPT
        window.intercomSettings = $interComSettingJson;
        
        (function () {
            var w = window;
            var ic = w.Intercom;
            if (typeof ic === "function") {
                ic('reattach_activator');
                ic('update', intercomSettings);
            } else {
                var d = document;
                var i = function () {
                    i.c(arguments)
                };
                i.q = [];
                i.c = function (args) {
                    i.q.push(args)
                };
                w.Intercom = i;
                function l() {
                    var s = d.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = '$interComWidgetUrl';
                    var x = d.getElementsByTagName('script')[0];
                    x.parentNode.insertBefore(s, x);
                }
                if (w.attachEvent) {
                    w.attachEvent('onload', l);
                } else {
                    w.addEventListener('load', l, false);
                }
            }
        })();
SCRIPT;
$this->registerJs($intercomJs, View::POS_END);

?>
<!--for current Action !-->
<?php
/* Get current Action */
$action = Yii::$app->controller->action->id; //name of the current action

$controller_action_name = Yii::$app->controller->id . '/' . $action; //the name of the current controller and action

/* End Get current Action */

?>
<input type="hidden" value="<?= $action; ?>" id="current_action">
<input type="hidden" value="<?= $controller_action_name; ?>" id="current_controller_action">

<?php $this->endContent(); ?>
