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
        'main_product_id' =>        ['type' => 'int'],
        'product_id' =>       ['type' => 'int'],
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function main_product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
