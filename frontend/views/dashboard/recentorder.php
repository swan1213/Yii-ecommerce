<?php use common\models\CurrencyConversion;
use common\models\CurrencySymbol;

if (empty($orders_data_REcent)): ?>
<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">No data available in table.</td> </tr>
    <?php
else :
    usort($orders_data_REcent, function($a, $b) {
        $t1 = strtotime($a->order_date);
        $t2 = strtotime($b->order_date);
        return $t2 - $t1;
    });
    foreach ($orders_data_REcent as $orders_data_value) :
        $channel_abb_id = isset($orders_data_value->connection_order_id) ? $orders_data_value->connection_order_id : "";
        $customer_id = $orders_data_value->customer->id;
        $firstname = $orders_data_value->customer->first_name;
        $lname = $orders_data_value->customer->last_name;
        $order_amount = isset($orders_data_value->total_amount) ? $orders_data_value->total_amount : 0;
        $order_value = number_format((float) $order_amount, 2, '.', '');
        $date_order = date('M-d-Y', strtotime($orders_data_value->order_date));
        $order_status = $orders_data_value->status;
        $label = '';
        if ($order_status == 'Completed') :
            $label = 'label-success';
        endif;

        if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') :
            $label = 'label-danger';
        endif;

        if ($order_status == 'In Transit' || $order_status == 'On Hold'):
            $label = 'label-primary';
        endif;

        if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold'):
            $label = 'label-warning';
        endif;
        if ($order_status == 'Shipped' || $order_status == 'Partially Shipped'):
            $label = 'label-primary';
        endif;


        $conversion_rate = 1;
        if (isset($user->currency) and $user->currency != 'USD') {
//            $username = Yii::$app->params['xe_account_id'];
//            $password = Yii::$app->params['xe_account_api_key'];
//            $URL = Yii::$app->params['xe_base_url'] . 'convert_from.json/?from=USD&to=' . $user->currency . '&amount=1';
//
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $URL);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
//            $result = curl_exec($ch);
//            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
//            curl_close($ch);
//            $result = json_decode($result, true);
//            if (isset($result) and ! empty($result) and isset($result['to']) and isset($result['to'][0]) and isset($result['to'][0]['quotecurrency'])) {
//                $conversion_rate = $result['to'][0]['mid'];
//                $conversion_rate = number_format((float) $conversion_rate, 2, '.', '');
//                $order_value = $order_value * $conversion_rate;
//                $order_value = number_format((float) $order_value, 2, '.', '');
//            }

            $conversion_rate = CurrencyConversion::getCurrencyConversionRate('USD', $user->currency);
            $order_value = $order_value * $conversion_rate;
            $order_value = number_format((float) $order_value, 2, '.', '');

        }

        $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
        if (isset($selected_currency) and ! empty($selected_currency)) {
            $currency_symbol = $selected_currency['symbol'];
        }
        ?>
        <tr>
            <td class="captialize"><a href="/people/view?id=<?=$customer_id?>"><?= $firstname . ' ' . $lname; ?></a></td>
            <td class="number12" style="text-align:left;"><?php echo $currency_symbol ?><?= number_format($order_value, 2); ?></td>
        <!--         <td><? //=  $date_order; ?></td> -->
            <td><span class="label  <?= $label; ?>"><?= $order_status; ?></span></td>
             <td><a href="/order/view?id=<?php echo $orders_data_value->id; ?>">View Order</a></td>
        </tr>
        <?php
    endforeach;
endif;