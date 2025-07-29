<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;

class ProductRelated extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'product_id' =>         ['type' => 'int',           'req' => true],
        'related_id' =>         ['type' => 'int',           'req' => true],
        'position' =>           ['type' => 'int',           'def' => 0]
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'related_id');
    }

    public function parent_product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    /**
     * Выбираем связанные товары
     * @param int $product_id
     * @param int $limit
     */
    public static function getRelatedProducts(int $product_id, int $limit = 0)
    {
        $query = self::where('product_id', $product_id);
        if ($limit) {
            $query->limit($limit);
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
        return self::createOne([
            'product_id' => $product_id,
            'related_id' => $related_id,
            'position' => $position,
        ]);
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
