<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\BaseModel;

class OrderPaymentDelivery extends BaseModel
{
    protected $table = 'order_delivery_payment';

    public static $table_fields = [
        'delivery_id' =>        ['type' => 'int', 'req' => true],
        'payment_method_id' =>  ['type' => 'int', 'req' => true],
    ];

    public function delivery()
    {
        return $this->belongsTo(OrderDelivery::class, 'delivery_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(OrderPayment::class, 'payment_method_id');
    }
}
