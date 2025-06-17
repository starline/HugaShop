<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 * Работа с вариантами товаров
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;

class ProductVariant extends BaseModel
{
    public static $table_fields = [
        'parent_id' =>        ['type' => 'int'],
        'product_id' =>       ['type' => 'int'],
        'enabled' =>            ['type' => 'tinyint',      'def' => 1],
        'position' =>           ['type' => 'int',          'def' => 0]
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function parent_product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
