<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 * 
 */

namespace HugaShop\Extensions\ProductFilling\Models;

use HugaShop\Models\Product\Product as ProductBase;
use HugaShop\Extensions\ProductFilling\Models\ProductFilling;

final class Product extends ProductBase
{


    public function fillings()
    {
        return $this->hasMany(ProductFilling::class, 'product_id');
    }


    /**
     * Get Products 
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {

        $model = static::getModel();
        $query = $model->newQuery();


        if (isset($filter['category_id'])) {
            $query->whereIn('category_id', (array)$filter['category_id']);
        }

        // Keyword search
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

        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $model->runWithInitTable(function () use ($query) {
            return $query->get()->keyBy('id');
        });
    }
}
