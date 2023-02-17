<?php


use common\models\GeneralCategory;
use common\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\CurrencySymbol;
use common\models\Connection;
use common\models\CurrencyConversion;

$this->title = 'General';
$username = Yii::$app->user->identity->username;
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
<?php
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]);

$user = Yii::$app->user->identity;
$conversion_rate = 1;
if (isset($user->currency) and $user->currency != 'USD') {
    $conversion_rate = CurrencyConversion::getCurrencyConversionRate('USD', $user->currency);
}
$conversion_rate = number_format((float) $conversion_rate, 2, '.', '');
//$conversion_rate = number_format($conversion_rate,'2','.',',');
$price =  Yii::$app->user->identity->annual_revenue * $conversion_rate;
$order_prices = Yii::$app->user->identity->annual_order_target * $conversion_rate;

$selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
if (isset($selected_currency) and ! empty($selected_currency)) {
    $currency_symbol = $selected_currency['symbol'];
}

$category = 'Empty';
$selected_category = GeneralCategory::find()->where(['id' => $user->general_category_id])->one();
if (!empty($selected_category)) {
    $category = $selected_category->name;
}
?>
    </ol>
</div>



<div class="main-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>

<!--                                <tr>-->
<!--                                    <td width="35%">Store Name</td>-->
<!--                                    <td width="65%" class="captialize"><a  href="javascript:" id="general_Storename">--><?php //echo Yii::$app->user->identity->company; ?><!--</a></td>-->
<!--                                </tr>-->
<!--                                -->
<!--                                <tr>-->
<!--                                    <td width="35%">Store ID</td>-->
<!--                                    <td width="65%"><a  href="javascript:" id="general_StoreID">CTID10000456--><?php //echo Yii::$app->user->identity->id; ?><!--</a></td>-->
<!--                                </tr>-->
                                <!--for Account Owner!-->
                                <tr>
                                    <td>Account Owner</td>
                                    <td><a id="AccountOwner" data-title="Account Owner" data-placement="right" data-pk="1" data-type="text" href="#" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->email; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%" >Category</td>
                                    <td width="65%"><a id="general_category" href="javascript:" class="editable editable-click editable-empty" data-type="select" data-value="<?php echo $category ?>" data-title="Select Category"><?php echo $category ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%" >Default Currency</td>
                                    <td width="65%"><a id="general_CurrencyPreference" href="javascript:" class="editable editable-click editable-empty" data-type="select" data-value="<?php echo (!empty(Yii::$app->user->identity->currency)) ?  Yii::$app->user->identity->currency:'USD' ?>" data-title="Enter Default Currency"><?php echo (!empty(Yii::$app->user->identity->currency)) ?  Yii::$app->user->identity->currency:'USD' ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%" >Annual Revenue Target</td>
                                    <td width="65%"><a id="annual_revenue" data-title="Annual Revenue Target" data-placement="right" data-pk="1" data-type="text" href="javascript:" class="editable editable-click editable-empty" ><?php echo number_format($price,0,'.',','); ?></a></td>
                                </tr>

                            </tbody>
                        </table>
                        <!--for HeadingCorporate Address!-->
                        <div class="panel-heading profile-panel-heading"> 
                            <div class="title">Corporate Address</div>
                        </div>
                        <!--End for Heading Corporate Address!-->
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <!--for Corporate Street Line 1!-->
                                <tr>
                                    <td width="35%" >Street Line 1</td>
                                    <td width="65%" class="captialize"><a id="general_corporate_street1" href="#" data-type="text" class="editable-empty" data-title="Enter street line 1"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_street1; ?></a></td>
                                </tr>
                                <!--End for Corporate Street Line 2!-->
                                <tr>
                                    <td width="35%" >Street Line 2</td>
                                    <td width="65%" class="captialize"><a id="general_corporate_street2" href="#" data-type="text"  class="editable-empty" data-title="Enter street line 2"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_street2; ?></a></td>
                                </tr>
                                <!--for Corporate Country!-->
                                <tr>
                                    <td>Country</td>
                                    <td class="captialize"><a id="general_corporate_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_country; ?></a></td>
                                </tr>
                                <!--for Corporate State!-->
                                <tr>
                                    <td>State</td>
                                    <td class="captialize"><a id="general_corporate_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_state; ?></a></td>
                                </tr>
                                <!--for Corporate City!-->
                                <tr>
                                    <td>City</td>
                                    <td><a id="general_corporate_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_city; ?></a></td>
                                </tr>
                                <!--for Corporate Zip Code!-->
                                <tr>
                                    <td>Zip Code</td>
                                    <td><a id="general_corporate_zip" data-title="Start typing Zipcode.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->corporate_addr_zipcode; ?></a></td>
                                </tr>
								<tr>
                                    <td width="35%" >Phone Number</td>
                                    <td width="65%" class="captialize"><a id="general_phone_number" href="#" data-type="text"  class="editable-empty" data-title="Phone Number"><?php echo Yii::$app->user->identity->userProfile->phoneno; ?></a></td>
                                </tr>
								<tr>
                                    <td width="35%" >Company Name</td>
                                    <td width="65%" class="captialize"><a id="general_company" href="#" data-type="text"  class="editable-empty" data-title="Company Name"><?php echo Yii::$app->user->identity->company; ?></a></td>
                                </tr>
								
                            </tbody>
                        </table>
                        <!--for Heading Subscription Billing Address!-->
                        <div class="panel-heading  profile-panel-heading"> 
                            <div class="title">Subscription Billing Address</div>
                        </div>
                        <!--End for Heading Subscription Billing Address!-->

                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <!--for Subscription Billing Address Street Line 1!-->
                                <tr>
                                    <td width="35%" >Street Line 1</td>
                                    <td width="65%" class="captialize"><a id="subscription_billing_street1" class="editable-empty"  href="#" data-type="text" data-title="Enter street line 1"><?php echo Yii::$app->user->identity->userProfile->billing_addr_street1; ?></a></td>
                                </tr>
                                <!--Subscription Billing Address Street Line 2!-->
                                <tr>
                                    <td width="35%" >Street Line 2</td>
                                    <td width="65%" class="captialize"><a id="subscription_billing_street2" href="#" class="editable-empty" data-type="text" data-title="Enter street line 2"><?php echo Yii::$app->user->identity->userProfile->billing_addr_street2; ?></a></td>
                                </tr>
                                <!--Subscription Billing Address Country!-->
                                <tr>
                                    <td>Country</td>
                                    <td class="captialize"><a id="subscription_billing_country" data-title="Start typing Country.."  data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->billing_addr_country; ?></a></td>
                                </tr>
                                <!--Subscription Billing Address State!-->
                                <tr>
                                    <td>State</td>
                                    <td class="captialize"><a id="subscription_billing_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->billing_addr_state; ?></a></td>
                                </tr>
                                <!--Subscription Billing Address City!-->
                                <tr>
                                    <td>City</td>
                                    <td class="captialize"><a id="subscription_billing_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->billing_addr_city; ?></a></td>
                                </tr>
                                <!--Subscription Billing Address Zip Code!-->
                                <tr>
                                    <td>Zip Code</td>
                                    <td><a id="subscription_billing_zip" data-title="Start typing Zipcode.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo Yii::$app->user->identity->userProfile->billing_addr_zipcode; ?></a></td>
                                </tr>
                                <!--For Tax Rate!-->
                                <tr>
                                    <td width="35%" >Tax Rate</td>
                                    <td width="65%"><a id="general_TaxRate" href="javascript:" class="editable editable-click editable-empty" data-type="text" data-title="Enter Tax Rate"><?php echo Yii::$app->user->identity->userProfile->tax_rate; ?></a></td>
                                </tr>
                                <!--For Default Language!-->
                                <tr>
                                    <td>Default Language </td>
                                    <td class="captialize"><a id="general_Language" data-title="Start typing Language.." data-value="<?php echo ucwords(Yii::$app->user->identity->userProfile->language); ?>"data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click editable-empty"><?php echo ucwords(Yii::$app->user->identity->userProfile->language); ?></a></td>
                                </tr>
                                <!--For Default Weight Preference!-->
                                <tr>
                                    <td width="35%" >Default Weight Preference</td>
                                    <td width="65%"><a id="general_WeightPreference" href="javascript:" class="editable editable-click editable-empty" data-type="select" data-value="<?php echo Yii::$app->user->identity->userProfile->weight_preference; ?>" data-title="Enter Default Weight Preference"><?php echo Yii::$app->user->identity->userProfile->weight_preference; ?></a></td>
                                </tr>
                                <!--For Timezone!-->
<!--                                <tr>
                                    <td>Timezone</td>
                                    <td><a id="general_Timezone" data-title="Select Timezone"  data-value="<?php //echo Yii::$app->user->identity->timezone; ?>" data-pk="1" data-type="select" href="#" style="color: gray;" class="editable editable-click" data-source="get-timezone" data-name="general_Timezone"><?php //echo Yii::$app->user->identity->timezone; ?></a></td>
                                </tr>-->
                                <tr>
                                    <td><button class="btn btn-space btn-primary" onclick="savegeneralinfo()">Save</button></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
