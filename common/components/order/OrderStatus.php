<?php
namespace common\components\order;


class OrderStatus
{
    /**
     * @var string
     */
    const CANCEL = 'Cancel';
    /**
     * @var string
     */
    const PENDING = 'Pending';
    /**
     * @var string
     */
    const REFUNDED = 'Refunded';
    /**
     * @var string
     */
    const COMPLETED = 'Completed';
    /**
     * @var string
     */
    const IN_TRANSIT = 'In Transit';
    /**
     * @var string
     */
    const ON_HOLD = 'On Hold';


}