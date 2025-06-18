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
     * Get parent id for product. If product has no parent, returns 0
     */
    public static function getParentId(int $product_id): int
    {
        return (int)(self::where('product_id', $product_id)->value('parent_id'));
    }


    /**
     * Get variants list for parent product
     */
    public static function getVariants(int $product_id, array $join = []): Collection
    {
        $variants = self::where('parent_id', $product_id)
            ->orderBy('position')
            ->get();

        return $variants->map(function ($variant) use ($join) {
            $product = Product::getProduct($variant->product_id, $join);
            if ($product) {
                $product->position = $variant->position;
            }
            return $product;
        })->filter();
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
    public static function updateProductVariants(int $product_id, array $variants): void
    {
        // Determine parent id
        $parent_id = self::getParentId($product_id);
        if (!$parent_id && !empty($variants)) {
            $parent_id = $product_id;
        }

        if (!$parent_id) {
            return;
        }

        // prepare ids ordered by position
        $ids = [];
        foreach ($variants as $data) {
            $v_id = $data['id'] ?? $data['product_id'] ?? null;
            if ($v_id === null) {
                continue;
            }
            $pos = intval($data['position'] ?? 0);
            $ids[$pos] = (int)$v_id;
        }
        ksort($ids);
        $ids = array_values($ids);

        // if only parent remains -> delete relations
        if (empty($ids)) {
            self::where('parent_id', $parent_id)->delete();
            if ($parent_id !== $product_id) {
                self::where('product_id', $product_id)->delete();
            }
            return;
        }

        self::where('parent_id', $parent_id)->delete();
        $pos = 0;
        foreach ($ids as $v_id) {
            self::create([
                'parent_id' => $parent_id,
                'product_id' => $v_id,
                'position' => $pos++,
            ]);
        }
    }


    /**
     * Remove product from variants table
     */
    public static function deleteVariant(int $product_id)
    {
        self::where('product_id', $product_id)->delete();
        self::where('parent_id', $product_id)->delete();
    }
}
