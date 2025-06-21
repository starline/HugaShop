<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Models\Order;

use HugaShop\Models\BaseModel;

class OrderPaymentDelivery extends BaseModel
{
    protected $table = 'order_delivery_payment';

    protected static $table_fields = [
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
