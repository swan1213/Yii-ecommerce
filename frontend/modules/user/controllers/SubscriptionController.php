<?php

namespace frontend\modules\user\controllers;

use yii\filters\AccessControl;
use frontend\components\BaseController;

class SubscriptionController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    /**
     * Displays User Subscription Page.
     *
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionPayment() {

        $request = Yii::$app->request;
        /* get current user login id */
        $user_id = Yii::$app->user->identity->id;
        $user_domain = Yii::$app->user->identity->domain_name;
        //use for base directory//
        $basedir = Yii::getAlias('@basedir');
        //use for baseurl//
        $baseurl = Yii::getAlias('@baseurl');
        $name = $request->post('name');

        Stripe::setApiKey(env('STRIPE_API_KEY'));

        try {
            //Create Customer
            $customer = \Stripe\Customer::create(array(
                'email' => $request->post('stripeEmail'),
                'source' => $request->post('stripeToken'),
                'plan' => $request->post('plan_name'),
                'customer' => '12345',
                "metadata" => array("order_id" => "6735")
            ));

            //Charge Payment
            $charge = \Stripe\Charge::create(array(
                "amount" => $request->post('amount'), // amount in cents, again
                "currency" => "usd",
                "customer" => $customer->id,
                "description" => $request->post('email'),
                "metadata" => array("order_id" => "6735")
            ));

            $stripe_payment_Status = $charge->status;
            $stripe_plan_id = $charge->id;
            if ($stripe_payment_Status == 'succeeded') {

                /* Starts Save Billing Invoice */
                $billing_invoice_model = new BillingInvoice();
                $billing_invoice_model->elliot_user_id = $user_id;
                $billing_invoice_model->stripe_id = $stripe_plan_id;
                $billing_invoice_model->customer_email = $customer->email;
                $billing_invoice_model->invoice_name = $request->post('plan_name');
                $billing_invoice_model->amount = $request->post('amount');
                $billing_invoice_model->status = $stripe_payment_Status;
                $billing_invoice_model->created_at = date('Y-m-d h:i:s', time());
                $billing_invoice_model->save(false);
                /* End Save Billing Invoice */


                $user_data = User::find()->where(['id' => $user_id])->one();
                $user_data->trial_period_status = CustomFunction::trial_status_deactivate;
                $user_data->subscription_plan_id = $request->post('plan_name');
                $user_data->plan_status = CustomFunction::plan_status_activate;

                //change user status acc to subscription plan
                $user_data_main = User::find()->where(['id' => $user_id])->one();
                $user_data_main->trial_period_status = CustomFunction::trial_status_deactivate;
                $user_data_main->subscription_plan_id = $request->post('plan_name');
                $user_data_main->plan_status = CustomFunction::plan_status_activate;

                $user_data_main->save(false);

                $get_big_id_users = User::find()->select('domain_name,company_name,email')->where(['id' => $user_id])->one();
                $config_one = yii\helpers\ArrayHelper::merge(
                    require($_SERVER['DOCUMENT_ROOT'] . '/common/config/main.php'), require($_SERVER['DOCUMENT_ROOT'] . '/common/users_config/' . $get_big_id_users->domain_name . '/main-local.php'), require($_SERVER['DOCUMENT_ROOT'] . '/backend/config/main.php'), require($_SERVER['DOCUMENT_ROOT'] . '/backend/config/main-local.php'));


                if ($user_data->save(false)) {
                    Yii::$app->session->setFlash('success', 'Success! ' . $request->post('plan_name') . ' Subscription Plan has been activated in both.');


                    /* get current user login id */

                    $url = Yii::$app->params['PROTOCOL'] . $user_domain . '.' . Yii::$app->params['DOMAIN_NAME'] . 'user-subscription';
                    // $url = 'user-subscription';
                    return Yii::$app->getResponse()->redirect($url);
                } else {
                    Yii::$app->session->setFlash('danger', 'Error! Subscription plan is not subscripe please contact Support.');
                }
            }
        } catch (Exception $e) {

            echo "Customer Creation Failed";
            error_log("unable to sign up customer:" . $_POST['stripeEmail'] .
                ", error:" . $e->getMessage());
        }
    }

}