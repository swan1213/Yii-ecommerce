<?php
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\UserConnection;
use common\models\Customer;
use frontend\controllers\ChannelsController;


if (empty($lates_engagements)) : ?>
<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">No data available in table.</td> </tr>
<?php else :
        $loop = 0;
        foreach ($lates_engagements as $latest) :
        ?>
        <!-- For Orders!--->
        <?php if (array_key_exists("order_id", $latest)): ?>
            <?php
            $order_channel_store = Order::find()->where(['id' => $latest['order_id']])->one();
            $channel_name = $order_channel_store->userConnection->connection->name;
            $store_img = $order_channel_store->userConnection->connection->getConnectionImage();
            ?>
            <tr>
                <td style="width:37%;"><a href="order/view?id=<?php echo $latest['order_id']; ?>"><?= $order_channel_store->connection_order_id; ?></a></td>
                <td style="width:36%;"><?= "Order"; ?></td>
                <td><img class="ch_img" src="<?php echo $store_img; ?>" width="50" height="50" alt="<?php echo $channel_name;?>"></td>

            </tr>
        <?php endif; ?>
        <!-- For Products!--->
        <?php if (array_key_exists("id", $latest)): ?>
            <?php
            $product_channel_store = Product::find()->where(['id' => $latest['id']])->one();
            
            if(empty($product_channel_store)){
                continue;
            }
            $channel_name = '';
            $store_img = '';
            $product_connections = $product_channel_store->productConnections;
            if (!empty($product_connections)) {
                $product_connection = $product_connections[0];
                $channel_name = $product_connection->userConnection->connection->name;
                $store_img = $product_connection->userConnection->connection->getConnectionImage();
            }
            ?>
            <tr>
                <td style="width:37%;" class="captialize"><a href="/product/view?id=<?php echo $latest['id']; ?>"><?= $latest['name']; ?></td>
                <td style="width:36%;"><?= "Product"; ?></td>
                <td><img class="ch_img" src="<?php echo $store_img; ?>" width="50" height="50" alt="<?php echo $channel_name;?>"></td>

            </tr>
        <?php endif; ?>
        <!-- For Customers!--->
        <?php if (array_key_exists("customer_id", $latest)): ?>
            <?php
            $customer_channel_store = Customer::find()->where(['id' => $latest['customer_id']])->one();
            $channel_name = $customer_channel_store->userConnection->connection->name;

            $store_img = $customer_channel_store->userConnection->connection->getConnectionImage();
            ?>
            <tr>
                <td style="width:37%;" class="captialize"><a href="/people/view?id=<?php echo $latest['customer_id']; ?>"><?= $latest['first_name'] . ' ' . $latest['last_name']; ?></a></td>
                <td style="width:36%;"><?= "Customer"; ?></td>
                <td><img class="ch_img" src="<?php echo $store_img; ?>" width="50" height="50" alt="<?php echo $channel_name;?>"></td>
            </tr>
        <?php endif;
            $loop ++;
            if ($loop == 5)
                break;
        endforeach; ?>
<?php endif; ?>