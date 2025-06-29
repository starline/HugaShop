<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */


namespace HugaShop\Models\Warehouse;

use HugaShop\Models\BaseModel;

class WarehouseProduct extends BaseModel
{
    protected $table = 'wh_place_product';

    protected static $table_fields = [
        'id'         => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'product_id' => ['type' => 'int'],
        'place_id'   => ['type' => 'int'],
        'cost_price' => ['type' => 'decimal', 'def'  => 0.00],
        'amount'     => ['type' => 'int',     'def'  => 0],
    ];


    /**
     * Increment or decrement stock count for a product on specific place
     */
    public static function changeAmount(int $product_id, int $place_id, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        $item = self::firstOrCreate([
            'product_id' => $product_id,
            'place_id'   => $place_id,
        ], [
            'cost_price' => 0,
            'amount'     => 0,
        ]);

        $item->increment('amount', $amount);
    }
}
