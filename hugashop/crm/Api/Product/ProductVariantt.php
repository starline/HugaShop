<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 * Работа с вариантами товаров
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;

class ProductVariantt extends BaseModel
{

    protected $guarded = [];
    public $timestamps = false;

    public static $table_fields = [
        'parent_id' =>          ['type' => 'int'],
        'product_id' =>         ['type' => 'int'],
        'enabled' =>            ['type' => 'tinyint',      'def' => 1],
        'position' =>           ['type' => 'int',          'def' => 0]
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function parent_product()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Функция возвращает варианты товара
     * @param int $filter
     * @param array $join
     */
    public static function getByParentId(int $parent_id, array $join = [])
    {
        $variants_proxy = self::query()->where('parent_id', '=', $parent_id)->pluck('product_id')->toArray();
        return Product::whereIn($variants_proxy)->all();
    }
}
