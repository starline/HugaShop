<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;

class ProductRelated extends BaseModel
{

    protected $table = 'product_product_related';

    public static $table_fields = [
        'product_id' =>         ['type' => 'int',           'req' => true],
        'related_id' =>         ['type' => 'int',           'req' => true],
        'position' =>           ['type' => 'int',            'def' => 0]
    ];


    public function related()
    {
        return $this->belongsTo(Product::class, 'related_id');
    }

    /**
     * Выбираем связанные товары
     * @param int $product_id
     * @param bool $count
     */
    public static function getRelatedProducts(int $product_id, bool $count = false)
    {
        $query = self::where('product_id', $product_id);

        if ($count) {
            $query->limit($count);
        }

        $query->orderBy('position');
        return $query->get()->keyBy('related_id');
    }


    /**
     * Добавляем связанный товар
     * @param int $product_id
     * @param int $related_id
     * @param int $position
     */
    public static function addRelatedProduct(int $product_id, int $related_id, ?int $position = 0)
    {
        $record = self::create([
            'product_id' => $product_id,
            'related_id' => $related_id,
            'position' => $position,
        ]);

        return $record->id ?? 0;
    }


    /**
     * Удаление связанного товара
     * @param int $product_id
     * @param int $related_id
     */
    public static function deleteRelatedProduct(int $product_id, int $related_id)
    {
        return self::where('product_id', $product_id)
            ->where('related_id', $related_id)
            ->delete();
    }


    /**
     * Удаляем все связанные товары
     * @param int $product_id
     */
    public static function deleteAllRelatedProducts(int $product_id)
    {
        return self::where('product_id', $product_id)->delete();
    }
}
