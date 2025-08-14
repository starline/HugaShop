<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
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

        if (Arr::has($filter, 'top')) {
            if (empty($filter['date_from'])) {
                $filter['date_from'] = date('Y-m-d', strtotime('-30 days'));
            }
            $SELECT = Database->placehold(", ordpur.profit as profit, ordpur.sold as sold");
            $JOIN = Database->placehold(" LEFT JOIN (SELECT SUM((op.price-op.cost_price)*op.amount) as profit, product_id, SUM(op.amount) as sold FROM __order_purchase op LEFT JOIN __order ord on ord.id = op.order_id WHERE ord.date>? AND ord.paid=1 AND ord.closed=1 GROUP BY product_id) ordpur on ordpur.product_id=p.id ", $filter['date_from']);
            $WHERE = Database->placehold(" AND ordpur.profit IS NOT NULL ");
            $ORDER = Database->placehold("ordpur.profit DESC");
        }

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

        $query->orderByDesc('position');

        if (!empty($filter['limit'])) {
            $limit = max(1, (int)$filter['limit']);
            $page  = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $model->runWithInitTable(fn() => $query->get()->keyBy('id'));
    }


    /**
     * Count
     */
    public static function countProducts(array $filter = [], array $join = [])
    {
        return self::getProducts($filter, $join, count: true);
    }
}
