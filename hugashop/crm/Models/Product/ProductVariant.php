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

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;
use HugaShop\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductVariant extends BaseModel
{
    protected static $table_fields = [
        'id' =>               ['type' => 'int', 'extra' => 'AUTO_INCREMENT'],
        'parent_id' =>        ['type' => 'int'],
        'product_id' =>       ['type' => 'int'],
        'position' =>         ['type' => 'int',          'def' => 0]
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
     * Get all variants for product including parent
     */
    public static function getVariants(int $product_id, array $join = []): Collection
    {
        // Сначала находим parent_id этого товара
        $parent_id = self::query()
            ->where('product_id', $product_id)
            ->value('parent_id') ?? $product_id;

        return Product::getListTranslate(['variants.parent_id' => $parent_id], order: 'position', join: $join);
    }


    /**
     * Update variants positions for product
     */
    public static function updateVariants(int $current_product_id, array $variants): void
    {
        if (empty($current_product_id)) {
            return;
        }

        // If Deleted all
        if (empty($variants) || (count($variants) === 1 and (int) $variants[0] === $current_product_id)) {
            self::where('parent_id', $current_product_id)
                ->orWhere('product_id', $current_product_id)
                ->delete();
            return;
        }

        // Check if parent product exists in variants
        if (!in_array($current_product_id, $variants)) {
            array_unshift($variants, $current_product_id);
        }

        if (empty($variants)) {
            return;
        }

        // First variant is a parent
        $parent_id = (int) ($variants[0] ?? $current_product_id);
        $keep_ids = [];

        foreach ($variants as $position => $product_id) {

            if (empty($product_id)) {
                continue;
            }

            $keep_ids[] = $product_id;

            self::updateOrCreate(
                [
                    'parent_id'  => $parent_id,
                    'product_id' => $product_id,
                ],
                [
                    'parent_id'  => $parent_id,
                    'product_id' => $product_id,
                    'position'   => $position,
                ]
            );
        }


        // Удаляем старые связи внутри текущей группы
        self::where('parent_id', $parent_id)
            ->whereNotIn('product_id', $keep_ids)
            ->delete();

        // Удаляем связи, где product_id присутствует, но привязан к чужому parent
        self::whereIn('product_id', $keep_ids)
            ->where('parent_id', '!=', $parent_id)
            ->delete();
    }


    public static function deleteVariant(int $product_id)
    {

        // TODO если он был родительским вариантов, переназначить родителя группы на следующий товар по списку, если он есть.

        self::where('product_id', $product_id)
            ->orWhere('parent_id', $product_id)
            ->delete();
    }
}
