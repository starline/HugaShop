<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.3
 *
 * Работа с вариантами товаров
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductVariant extends BaseModel
{
    public static $table_fields = [
        'parent_id' =>        ['type' => 'int'],
        'product_id' =>       ['type' => 'int'],
        'enabled' =>          ['type' => 'tinyint',      'def' => 1],
        'position' =>         ['type' => 'int',          'def' => 0]
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function parent_product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    /**
     * Get all variants for product including parent
     */
    public static function getProductVariants(int $product_id, array $join = []): Collection
    {
        return self::query()
            ->where('product_id', $product_id)
            ->orWhere('parent_id', $product_id)
            ->with($join)
            ->orderBy('position')
            ->get();
    }


    /**
     * Update variants positions for product
     */
    public static function updateProductVariants(int $product_id, array $variants): void {}
}
