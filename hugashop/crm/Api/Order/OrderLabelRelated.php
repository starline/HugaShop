<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Pivot model for order and label relation
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\BaseModel;

class OrderLabelRelated extends BaseModel
{
    public static $table_fields = [
        'order_id' => ['type' => 'int', 'req' => true],
        'label_id' => ['type' => 'int', 'req' => true],
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function label()
    {
        return $this->belongsTo(OrderLabel::class, 'label_id');
    }
}
