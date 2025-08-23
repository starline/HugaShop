<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Models\Warehouse;

use HugaShop\Models\BaseModel;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Warehouse\WarehousePlace;

class WarehousePlaceProduct extends BaseModel
{

    protected $table = 'wh_place_product';
    public $timestamps = true;
    protected static $table_fields = [
        'id'            => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'product_id'    => ['type' => 'int'],
        'external_sku'  => ['type' => 'varchar'],
        'place_id'      => ['type' => 'int'],
        'cost_price'    => ['type' => 'decimal', 'def'  => 0.00],
        'price'         => ['type' => 'decimal', 'def'  => 0.00],
        'amount'        => ['type' => 'int',     'def'  => 0]
    ];
    
    protected static $table_keys = [
        'product_id'    => ['column' => ['product_id'],             'type' => 'index'],
        'place_id'      => ['column' => ['place_id'],               'type' => 'index']
    ];

    public function place()
    {
        return $this->belongsTo(WarehousePlace::class, 'place_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


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
