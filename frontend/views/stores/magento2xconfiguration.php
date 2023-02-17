<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Magento 2x Integration API';
$this->params['breadcrumbs'][] = ['label' => 'Magento2', 'url' => ['/stores/magento2']];
$this->params['breadcrumbs'][] = 'Instructions';


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
        <div class="col-xs-12 col-md-7 col-md-offset-2">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-heading panel-heading-divider">
                    <span class="title" >Magento 2x Integration API Instructions</span>
                </div>
                <div class="panel-body">
                    <ol>
                        <li>In the Magento admin panel choose <b>System</b> > <b>Extensions</b> > <b>Integrations</b></li>
                        <li>Click <b>Add New Integration</b></li>
                        <li>Enter <b>Name</b> and <b>Email</b></li>
                        <li>Leave blank for <b>Callback URL</b> and <b>Identity link URL</b></li>
                        <li>Fill your admin Current user for Verification</li>
                        <li>In the <b>API</b> tab change the <b>Resource Access</b> drop down option to <b>ALL</b> and then choose <b>Save</b></li>
                    </ol>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento2/magento2x_configration.jpg" alt="Magenot soap role img">
                    </div>
                    <br>
                    <br>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento2/magento2x_configration_api.jpg" alt="Magenot soap role img">
                    </div>
                    <h4>Then activate your Integration</h4>
                    <ol>
                        <li>To activate Click <b>Activate</b> button from grid</li>
                        <li>Then click on <b>Allow</b> button to approve the access the api</li>
                        <li>Then you will get all credentials <e>eg</e>('Consumer Key', 'Consumer Secret', 'Access Token', 'Access Token Secret')</li>
                        <li>Your will need only Access Token and Shop url When setting up the connection in Magento 2x wizard</li>
                        <li>Then click on <b>Done</b>. To complete the setup</li>
                    </ol>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento2/magento2x_configration_activate.jpg" alt="Magenot soap user img">
                    </div>
                    <br>
                    <br>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento2/magento2x_configration_access_token.jpg" alt="Magenot soap user img">
                    </div>
                    <br>
                    <br>
                    <div role="alert" class="alert alert-primary alert-dismissible">
                        <span class="icon mdi mdi-info-outline"></span>
                        Also you need to install our Magento module to sync your product, customer, order. Click to 
                        <a style="color: #fff;" href="/module/Elliot_module_magento2.zip" download><b>Download</b></a> our module.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>   