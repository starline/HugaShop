<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 * 
 */

namespace HugaShop\Extensions\ProductFilling\Models;

use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\Product as ProductModel;
use HugaShop\Extensions\ProductFilling\Models\ProductFilling;

final class Product extends ProductModel
{


    public function fillings()
    {
        return $this->hasMany(ProductFilling::class, 'product_id');
    }

    public function mainFilling()
    {
        return $this->hasOne(ProductFilling::class, 'product_id')
            ->where('language_code', Language::getMain()->code);
    }


    /**
     * Get Products
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {

        $model = static::getModel();
        $query = $model->newQuery();
        $products_table = $model->getTable();

        $main_lang = Language::getMain()->code;
        $query->select("$products_table.*");
        $query->selectSub(
            ProductFilling::query()
                ->select('percent')
                ->whereColumn('product_id', "$products_table.id")
                ->where('language_code', $main_lang)
                ->limit(1),
            'percent'
        );


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

        if (isset($filter['filling'])) {
            $query->having('percent', '<=', $filter['filling']);
        }

        if ($count) {
            return $query->count();
        }

        if (!empty($join)) {
            $query->with($join);
        }

        $query->orderBy('percent');

        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $model->runWithInitTable(function () use ($query) {
            return $query->get()->keyBy('id');
        });
    }


    /**
     * Count
     */
    public static function countProducts(array $filter = [], array $join = [])
    {
        return self::getProducts($filter, $join, count: true);
    }
}
