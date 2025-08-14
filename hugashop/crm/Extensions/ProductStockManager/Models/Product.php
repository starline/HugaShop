<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\ProductStockManager\Models;

use HugaShop\Models\Product\Product as ProductModel;

final class Product extends ProductModel
{
    /**
     * Get Products
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {
        $model = static::getModel();
        $query = $model->newQuery();

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', trim($filter['keyword']));
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%$kw%")
                      ->orWhere('sku', 'like', "%$kw%")
                      ->orWhere('variant_name', 'like', "%$kw%");
                }
            });
        }

        if ($count) {
            return $query->count();
        }

        if (!empty($join)) {
            $query->with($join);
        }

        $query->orderByDesc('id');

        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page  = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $model->runWithInitTable(fn () => $query->get()->keyBy('id'));
    }

    /**
     * Count
     */
    public static function countProducts(array $filter = [])
    {
        return self::getProducts($filter, count: true);
    }
}
