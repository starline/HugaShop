<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */


namespace HugaShop\Api\Warehouse;

use HugaShop\Api\BaseModel;

class WarehouseProduct extends BaseModel
{
    protected $table = 'wh_product';

    public static $table_fields = [
        'id'         => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'move_id'    => ['type' => 'int'],
        'product_id' => ['type' => 'int'],
        'place_id'   => ['type' => 'int'],
        'variant_id' => ['type' => 'int'],
        'cost_price' => ['type' => 'decimal', 'def'  => 0.00],
        'amount'     => ['type' => 'int',     'def'  => 0],
    ];

    public static function updateStock(array $purchase)
    {
        return self::updateOrCreate(
            ['variant_id' => $purchase['variant_id'], 'place_id' => $purchase['place_id']],
            $purchase
        );
    }
}
