<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Magento Soap API Configuration';
$this->params['breadcrumbs'][] = ['label' => 'Magento', 'url' => ['/stores/magento']];
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
                    <span class="title" >Magento API Setup Instructions - SOAP/XML</span>
                </div>
                <div class="panel-body">
                    <h4>Creating Magento SOAP/XML Role</h4>
                    <ol>
                        <li>In the Magento admin panel choose <b>System</b> > <b>Web Services</b> > <b>SOAP/XMLM - RPC Roles</b></li>
                        <li>Choose <b>Add New Role</b></li>
                        <li>In the <b>Role Info</b> tab create a <b>Role Name</b> and choose <b>Save Role</b></li>
                        <li>In the <b>Role Resources</b> tab change the <b>Resource Access</b> drop down option to <b>ALL</b> and then choose <b>Save Role</b></li>
                    </ol>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento_role.jpg" alt="Magenot soap role img">
                    </div>
                    <h4>Creating Magento SOAP/XML User</h4>
                    <ol>
                        <li>In the Magento admin panel choose <b>System</b> > <b>Web Services</b> > <b>SOAP/XML - RPC Users</b></li>
                        <li>Choose <b>Add New User</b></li>
                        <li>Fill out the Account Information form generated. Both the <b>User Name</b> and the <b>API Key</b> are created by you. You will need both of these when setting up the connection in Magento wizard.</li>
                        <li>Then choose <b>Save User.</b></li>
                        <li>In the <b>User Role</b> tab allocate the User to the Role (you created earlier) and then choose <b>Save User</b> </li>
                    </ol>
                    <div class="magento_image_container">
                        <img src="/img/magento_soap/magento_soap_user.jpg" alt="Magenot soap user img">
                    </div>
                    <br>
                    <br>
                    <div role="alert" class="alert alert-primary">
                        <span class="icon mdi mdi-info-outline"></span>
                        <span>Also you need to install our Magento module to sync your product, customer, order. Click to 
                        <a style="color: #fff;" href="/module/Elliot-module.zip" download><b>Download</b></a> our module.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>   