<?php

use common\models\User;
use yii\helpers\Html;
use frontend\assets\FrontendAsset;

$bundle = FrontendAsset::register($this);
$this->title = "Profile";

?>

<div class="user-profile">
    <div class="row">
        <div class="col-md-5">
            <div class="user-display ">
                <!--Starts Change cover image !-->
                <div class="user-display-bg texthover">
                    <?php if (!empty(Yii::$app->user->identity->userProfile->getCoverPicture())) { ?>
                        <img src="<?php echo Yii::$app->user->identity->userProfile->getCoverPicture(); ?>"  alt="Profile Background" id="cover_image_dropzone1"  class="custom_drozone_css dropzone">
                        <div class="overlay"><br />
                            <span class="panel-heading profile-panel-heading span-cover-image-css custom_drozone_css dropzone" id="cover_image_dropzone"></span>
                        </div>
                    <?php } else { ?>
                        <img src="<?php echo $this->assetManager->getAssetUrl($bundle, 'img/demo_cover_image.jpg'); ?>"  alt="Profile Background" id="cover_image_dropzone_default"  class="custom_drozone_css dropzone">
                        <div class="overlay"><br />
                            <span class="panel-heading profile-panel-heading span-cover-image-css custom_drozone_css dropzone" id="cover_image_dropzone"></span>
                        </div>
                    <?php } ?>
                </div>
                <!--End Code Change cover image !-->

                <!--Starts Change cover image !-->
                <div class="user-display-bottom " >
                    <?php if (!empty(Yii::$app->user->identity->userProfile->getPhoto())) { ?>
                        <div class="user-display-avatar profile_img_avatar"><img src="<?php echo Yii::$app->user->identity->userProfile->getPhoto() ?>" alt="Avatar" id="profile_image_dropzone1" class="custom_drozone_css dropzone">
                            <div class="overlay1"><br />
                                <span class="panel-heading profile-panel-heading span-profile-image-css custom_drozone_css dropzone" id="profile_image_dropzone"></span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="user-display-avatar profile_img_avatar"><img src="<?php echo $this->assetManager->getAssetUrl($bundle, 'img/avatar-150.png'); ?>" alt="Avatar" id="profile_image_dropzone1" class="custom_drozone_css dropzone">
                            <div class="overlay1"><br />
                                <span class="panel-heading profile-panel-heading span-profile-image-css custom_drozone_css dropzone" id="profile_image_dropzone"></span>
                            </div>
                        </div>

                    <?php } ?>
                    <div class="user-display-info">
                        <div class="name"><?php echo Yii::$app->user->identity->username; ?></div>
                        <div class="nick"><span class="mdi mdi-account"></span><?php echo Yii::$app->user->identity->username; ?></div>
                    </div>
                </div>
                <!--End Code Change cover image !-->
            </div>
            <div class="user-info-list panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <tr>
                                    <td width="35%">First Name</td>
                                    <td width="65%"><a id="profile_first_name" href="javascript:" data-type="text" data-title="Enter Firstname"><?php echo Yii::$app->user->identity->userProfile->firstname; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Last Name</td>
                                    <td width="65%"><a id="profile_last_name" href="javascript:" data-type="text" data-title="Enter Lastname"><?php echo Yii::$app->user->identity->userProfile->lastname; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Email Address</td>
                                    <td width="65%"><a id="profile_email_add" href="javascript:" data-type="text" data-title="Enter Email Address"><?php echo Yii::$app->user->identity->email; ?></a></td>
                                </tr>
                                <tr>
                                    <td>DOB</td>
                                    <td><a id="profile_dob" href="javascript:" data-title="Select Date of birth" data-pk="1" data-template="YYYY/MM/DD" data-viewformat="YYYY/MM/DD" data-format="YYYY/MM/DD" data-value="<?php echo Yii::$app->user->identity->userProfile->dob; ?>" data-type="combodate" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->dob; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
<td><a id="profile_gender" data-title="Select sex"  data-value="<?php echo Yii::$app->user->identity->userProfile->gender; ?>" data-pk="1" data-type="select" href="#" style="color: gray;" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->gender; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Date Acquired</td>
                                    <td width="65%"><a  href="javascript:"><?php echo Yii::$app->user->identity->created_at; ?></a></td>                                    
                                </tr>
                                <tr>
                                    <td width="35%" >Phone Number</td>
                                    <td width="65%"><a id="profile_Phone_no" href="javascript:" data-type="text" data-title="Enter Phone No"><?php echo Yii::$app->user->identity->userProfile->phoneno; ?></a></td>
                                </tr>
                                
                                <tr>
                                    <td width="35%" >Timezone</td>
                                    <td width="65%"><a id="profile_timezone" data-value="<?php echo Yii::$app->user->identity->userProfile->timezone; ?>" href="javascript:" data-type="select" class="editable editable-click" data-title="Select Timezone"><?php echo Yii::$app->user->identity->userProfile->timezone; ?></a></td>
                                </tr>

                            </tbody> 
                        </table>
                        <div class="panel-heading profile-panel-heading"> 
                            <div class="title">Billing Address</div>
                        </div>
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>       
                                <tr >
                                    <td width="35%" >Street Line 1</td>
                                    <td width="65%"><a id="profile_corporate_street1" href="#" data-type="text" data-title="Enter street line 1"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_street1; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%" >Street Line 2</td>
                                    <td width="65%"><a id="profile_corporate_street2" href="#" data-type="text" data-title="Enter street line 2"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_street2; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td><a id="profile_corporate_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_country; ?></a></td>
                                </tr>
                                <tr>
                                    <td>State</td>
                                    <td><a id="profile_corporate_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_state; ?></a></td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><a id="profile_corporate_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_city; ?></a></td>
                                </tr>

                                <tr>
                                    <td>Zip Code</td>
                                    <td><a id="profile_corporate_zip" data-title="Start typing Zipcode.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_zipcode; ?></a></td>
                                </tr>
                            </tbody>
                        </table>   

                        <div class="panel-heading  profile-panel-heading"> 
                            <div class="title">Shipping Address</div>
                        </div>

                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>  
                                <tr>
                                    <td width="35%">Street Line 1</td>
                                    <td width="65%"><a id="profile_ship_street1" href="#" data-type="text" data-title="Enter Street Line 1"><?php echo Yii::$app->user->identity->userProfile->billing_addr_street1; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Street Line 2</td>
                                    <td width="65%"><a id="profile_ship_street2" href="#" data-type="text" data-title="Enter Street Line 2"><?php echo Yii::$app->user->identity->userProfile->billing_addr_street2; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td><a id="profile_ship_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->billing_addr_country; ?></a></td>
                                </tr>
                                <tr>
                                    <td> State</td>
                                    <td><a id="profile_ship_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->billing_addr_state; ?></a></td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><a id="profile_ship_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->billing_addr_city; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Zip Code</td>
                                    <td><a id="profile_ship_zip" data-title="Start typing Zip Code.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->billing_addr_zipcode; ?></a></td>
                                </tr>

                                <tr>
                                    <td><button class="btn btn-space btn-primary" onclick="saveprofile()">Save</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="widget widget-fullwidth widget-small">
                <?php if (Yii::$app->user->identity->level != User::USER_LEVEL_MERCHANT_USER) { ?>
                <div class="widget-chart-container">
                    <!--<div id="bar-chart1" style="height: 180px;"></div>-->
                    <div class="panel-heading"> 
                        <div class="title">Active Users</div>
                    </div>
                    <table id="user_added" class="table table-striped table-hover table-fw-widget">
                        <thead>
                            <tr>
                                <th style="width:37%;">Users</th>
                                <th>Date</th>
                                <th class="center">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $userdata = User::findAll(['parent_id' => Yii::$app->user->identity->id]);
                           
                           
                            if (!empty($userdata)) {
                                foreach ($userdata as $user_value) {

                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="capitalize"><a href="user/update/<?php echo $user_value->id; ?>"><?php echo $user_value->userProfile->firstname . ' ' . $user_value->userProfile->lastname; ?></td>
                                        <td ><?php echo $user_value->created_at; ?></td>
                                        <td class="center">

                                            <?=
                                            Html::a('', ['delete', 'id' => $user_value->id], [
                                                // Url::to(['user/delete', 'id' => $user_value->id]),

                                                'class' => 'mdi mdi-delete',
                                                'data' => [
                                                    'confirm' => 'Are you sure you want to delete this item?',
                                                    'method' => 'post',
                                                ],
                                            ])
                                            ?>
                                            <?=
                                            Html::a('', ['/user/update/', 'id' => $user_value->id], [
                                                // Url::to(['user/delete', 'id' => $user_value->id]),

                                                'class' => 'mdi mdi-edit',
                                                
                                            ])
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                            <a  href="/user/create" class='profile_add_new_member'><button class="btn btn-space btn-primary">Add Team Members</button></a>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
