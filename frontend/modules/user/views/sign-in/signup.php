<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use frontend\widgets\SpinnerWidget;
use yii\web\View;

$this->title = Yii::t('frontend', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'be-splash-screen';


?>
<?php
echo SpinnerWidget::widget([
    'title' => 'Your instance is being created.',
]);
?>

<div class="be-wrapper be-login be-signup">
    <div class="be-content be-loading">
        <div class="main-content container-fluid">
            <div class="splash-container sign-up">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading">
                        <img src="<?php echo Yii::getAlias('@frontendUrl') ?>/img/elliot-logo-thumbnail.svg" alt="logo" width="150" height="160" class="logo-img">
                        <!--<span class="splash-description">Global Commerce Unified</span>-->
                    </div>
                    <div class="panel-body">
                        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                        <div class="form-group">
                            <?= $form->field($model, 'company')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="form-group">
                            <?= $form->field($model, 'email') ?>
                        </div>
                        <div class="form-group row signup-password">
                            <div class="col-xs-6">
                                <?= $form->field($model, 'password')->passwordInput() ?>
                            </div>
                            <div class="col-xs-6">
                                <?= $form->field($model, 'confirm_password')->passwordInput() ?>
                            </div>
                        </div>
                        <div class="form-group xs-pt-10">
                            <?= Html::submitButton('Get Access', ['class' => 'btn btn-block btn-primary btn-xl SignUpCustomClass', 'name' => 'signup-button']) ?>

                        </div>

                        <div class="form-group xs-pt-10">
                            <?= $form->field($model, 'acceptTerms')->checkbox(['value' => false])->label('By creating an account, you agree the <a href="javascript:" data-toggle="modal" data-target="#tearmsconditions">terms and conditions</a>.'); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <div class="splash-footer">&copy; <?php echo date('Y');?> Elliot</div>

                <div id="tearmsconditions" tabindex="-1" role="dialog" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                            </div>
                            <div class="modal-body tc-scroll">
                                <div class="text-center">
                                    <!--<div class="text-primary"><span class="modal-main-icon mdi mdi-info-outline"></span></div>-->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading panel-heading-divider">1. CUSTOMER SERVICE<span class="panel-subtitle"></span></div>
                                                <div class="panel-body">
                                                    <p>Elliot will provide the following customer service support to Subscriber related to the Elliot Services for the duration of the Agreement:</p>
                                                    <p>(a) email and telephone support regarding operation and use of the Elliot Services during Elliot’s normal business hours (9 AM to 5 PM PST), and</p>
                                                    <p>(b) programming or other workaround to correct any demonstrated or replicate such errors in the Elliot Services necessary to enable reasonable use of the Elliot Services.  Subscriber should promptly report any errors in the operation of the Elliot Services to Elliot.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading panel-heading-divider">2. SERVICE COMMITMENT<span class="panel-subtitle"></span></div>
                                                <div class="panel-body">
                                                    <p>Elliot will provide the Elliot Services in a manner consistent with general industry standards, which includes reasonable Services interruptions due to Excusable Delays or scheduled maintenance.  The Elliot Services may be temporarily unavailable for scheduled maintenance (regular "Maintenance Window" ? defined as daily between 3 am - 4 am PST) or for unscheduled emergency maintenance, or because of other causes beyond Elliot's reasonable control (collectively referred to as "Excusable Delays").  Except for reasonable interruptions due to Excusable Delays or regularly scheduled maintenance, the Elliot Services shall be available not less than 99.9% ("Minimum Uptime Percentage") of the time, on a monthly basis.</p>

                                                    <p>Elliot will schedule any period of Scheduled Maintenance which requires suspension of all or a major part of the Elliot Services for more than 1 hour Maintenance Window by giving Subscriber at least twelve hour’s notice.</p>

                                                    <p>Emergency Scheduled Maintenance: if an issue exists which merits immediate attention in the interests of several Elliot customers, Elliot may elect to perform Scheduled Maintenance with less notice than specified above.</p>

                                                    <p>If Subscriber has more than one account with Elliot, Scheduled Maintenance for separate installations may occur at different times.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading panel-heading-divider">3. REMEDIES<span class="panel-subtitle"></span></div>
                                                <div class="panel-body">
                                                    <p>3.1  In the event the Uptime Percentage in a calendar month falls below the Minimum Uptime Percentage set forth in Section 1 above, Subscriber will be entitled to a credit against future Elliot Services fees ("Service Credit") as set forth in the below table:</p>


                                                    <div class="panel panel-default panel-table">
                                                        <div class="panel-body">
                                                            <table class="table table-condensed table-striped">
                                                                <thead>
                                                                <tr>
                                                                    <th>Monthly Uptime Percentage</th>
                                                                    <th>Service Credit Percentage</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td>Less than 99.9%</td>
                                                                    <td>10% of monthly subscription fee credited back to the Subscriber's account.</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>


                                                    <p>3.2  Elliot will apply any Service Credits only against future Elliot Services. Service Credits will not entitle Subscriber to any refund or other payment from Elliot. Service Credits may not be transferred or applied to any other account. If Subscriber has multiple accounts, then the Monthly Uptime Percentage for each account will be calculated separately for the purposes of determining eligibility for a Service Credit, and the Service Credit for each account will be calculated as a proportion of the Elliot Services fees applicable to that account.
                                                    </p>
                                                    <p>3.3    To receive a Service Credit, Subscriber must submit a claim by emailing their Elliot Account Manager within 30 days of the end of the month in which the Minimum Uptime Percentage was not met. To be eligible, the Service Credit request must be received by Elliot by the end of the second billing cycle after which the incident occurred and must include:</p>
                                                    <ul>
                                                        <li>The words "SLA Credit Request" in the subject line;</li>
                                                        <li>The dates and times of each unavailability incident that Subscriber is claiming;</li>
                                                        <li>The affected Elliot account; and</li>
                                                        <li>Logs and other material that document the errors and corroborate Subscriber's claimed outage (any confidential or sensitive information in these logs should be removed or replaced with asterisks). Elliot reserves the right to withhold any Service Credit if Elliot cannot verify the downtime or Subscriber cannot show that Subscriber was adversely affected in any way as a result of the downtime.</li>
                                                    </ul>
                                                    <p>3.4  If the Uptime Percentage of such request is confirmed by Elliot and is less than the Minimum Uptime Percentage, then Elliot will issue the applicable Service Credit to Subscriber's account. Subscriber's failure to provide the requested and other information as required above will disqualify Subscriber from receiving any Service Credit.</p>
                                                    <p>3.5  If Subscriber requests a Service Credit under this Section 3, then Subscriber may not also request any other credit from Elliot with respect to the same period of time under any contract or applicable policy that may be in effect from time to time, if any.</p>
                                                    <p>3.6  This Section 3 states Subscriber's sole and exclusive remedy for Elliot's failure to meet the Minimum Uptime Percentage.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading panel-heading-divider">4. OTHER EXCLUSIONS.<span class="panel-subtitle"></span></div>
                                                <div class="panel-body">
                                                    <p>The Minimum Uptime Percentage does not apply to any unavailability, suspension or termination of Elliot Services performance issues ("Other Exclusions"): (a) caused by factors outside of Elliot's reasonable control, including any force majeure event or Internet access or related problems beyond the demarcation point of the Elliot Site, (b) that result from any actions or inactions of Subscriber or any third party, (c) that result from Subscriber's software or other technology and/or third party equipment, software, applications or other technology (other than third party equipment within Elliot's direct control); or (d) arising from Elliot's suspension and termination of Subscriber's right to use the Elliot Services in accordance with the Agreement and the Terms of Service. If Availability is impacted by factors other than those used in Elliot's Uptime Percentage calculation, then Elliot may issue a Service Credit considering such factors at Elliot's sole discretion.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading panel-heading-divider">5. TECHNICAL SUPPORT AND PROBLEM RESOLUTION.<span class="panel-subtitle"></span></div>
                                                <div class="panel-body">
                                                    <p>Elliot will provide Subscriber with technical support for the Elliot Services pursuant as set forth in Exhibit A attached hereto.</p>

                                                    <div class="panel panel-default panel-table">
                                                        <div class="panel-body">
                                                            <table class="table table-condensed table-striped">
                                                                <thead>
                                                                <tr>
                                                                    <th>Incident Urgency</th>
                                                                    <th>Definition</th>
                                                                    <th>Contact Time SLAs</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td>P1 – Critical</td>
                                                                    <td>A complete outage of Elliot Services</td>
                                                                    <td>Initial Contact: < 1 hour<br>Management Escalation: Immediate</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>P2 – High</td>
                                                                    <td>Subscriber can access Elliot Services, however one or more significant features are unavailable such as the ability to fulfill orders thru Subscribers ERP or fulfillment software</td>
                                                                    <td>Initial Contact: < 1 hour<br>Management Escalation: Immediate</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>P3 – Low</td>
                                                                    <td>Other error that does not prevent Subscriber from accessing a significant feature of the Elliot Services</td>
                                                                    <td>Initial Contact: < 24 hours<br>Management Escalation: < 5 business days</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <p>For P1 and P2 incidents, Subscriber will initiate contact with Elliot Support via telephone (+1-424-361-2710), and indicate the probable category of the incident. For P3 incidents, Subscriber may contact Elliot Support via telephone, email (cs@helloiamelliot.com) or chat.</p>

                                                    <p>Subscriber acknowledges that not all P3 errors will require a workaround. Elliot may, in its reasonable discretion, respond to a P3 error by making the error a feature request.</p>

                                                    <p>A status update will be communicated to Subscriber on a schedule agreed upon by Subscriber and Elliot.</p>
                                                    <ul>
                                                        <li>Subscriber receives a workaround or information that resolves the issue and agrees the issue is resolved.</li>
                                                        <li>Subscriber has not responded to Elliot after a workaround or information was provided to Subscriber.</li>
                                                        <li>Subscriber has not responded to Elliot after Elliot requested additional information.</li>
                                                        <li>The ticket will be closed 10 business days after the final email has been sent to Subscriber's email address.</li>
                                                    </ul>

                                                    <p>Elliot may from time to time update Elliot contact information online, and may introduce additional escalation layers as appropriate based on changes in Elliot's support organization, online or in notice to Subscriber (email is acceptable).</p>
                                                    <p>Subscriber's contacts for support purposes are as listed on the cover page of this Program Document. Subscriber may change its support contact persons upon notice to Elliot.</p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="xs-mt-50">
                                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in company_name_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close company_name_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='company_name_header_error_msg'>Error</h3>
                    <p id="company_name_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default company_name_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<?php
$signUpJs = <<< SCRIPT
        $(document).ready(function(){
            $(".SignUpCustomClass").click(function (event) {
                //event.preventDefault();
                var signCompany = $("#signupform-company").val();
                var signEmail = $("#signupform-email").val();
                var signPwd = $("#signupform-password").val();
                var signPwd2 = $("#signupform-confirm_password").val();
                
                var regular = /[a-z]|[0-9]/gi;
                signCompany = signCompany.replace(regular, '');
                if(signCompany != ''){
                    $("#company_name_header_error_msg").html("Company Name invalid!");
                    $('.company_name_error').modal('show');
                    return;
                }
                
                if ( signCompany.length > 0 && signEmail.length > 0 && signPwd.length > 0 && signPwd2 > 0 ) {
                    jQuery('body').addClass('be-loading-active');
                    jQuery('body').addClass('be-loading');
                    jQuery('.be-loader-modal-text').css('display', 'block');
                    jQuery('.be-spinner').css('display', 'block');
                }

            });
        
        });
SCRIPT;

$this->registerJs($signUpJs, View::POS_READY);

?>
