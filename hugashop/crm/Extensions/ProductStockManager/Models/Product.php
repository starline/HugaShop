<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 */

namespace HugaShop\Extensions\ProductStockManager\Models;

use Illuminate\Support\Arr;
use HugaShop\Services\Config;
use Illuminate\Database\Capsule\Manager as DB;
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

        // Best sellers
        if (Arr::has($filter, 'top')) {
            $date_from = $filter['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $ordersScope = function ($q) use ($date_from) {
                $q->where('date', '>', $date_from)
                    ->where('paid', 1)
                    ->where('closed', 1);
            };

            $query->withSum([
                'order_purchases as profit' => fn($q) => $q->whereHas('order', $ordersScope),
            ], DB::raw('(price - cost_price) * amount'))
                ->withSum([
                    'order_purchases as sold' => fn($q) => $q->whereHas('order', $ordersScope),
                ], 'amount')
                ->having('profit', '>', 0)
                ->orderByDesc('profit');
        }

        // Proposals for purchase
        if (isset($filter['purchase'])) {
            $date_from   = $filter['date_from'] ?? date('Y-m-d', strtotime('-60 days'));
            $ordersScope = function ($q) use ($date_from) {
                $q->where('date', '>', $date_from)
                    ->where('paid', 1)
                    ->where('closed', 1);
            };

            $base = $query->withSum([
                'order_purchases as sold' => fn($q) => $q->whereHas('order', $ordersScope),
            ], 'amount')
                ->with('movements')
                ->withSum('movements as waiting', 'amount');

            // Оборачиваем в подзапрос и считаем need по алиасам
            $sub_alias = 'p';
            $sub_prefix_alias = $model->getConnection()->getTablePrefix() . 'p';
            $query->fromSub($base, $sub_alias)
                ->select("$sub_alias.*")
                ->selectRaw("COALESCE($sub_prefix_alias.sold, 0) * 2 - COALESCE($sub_prefix_alias.stock, 0) - COALESCE($sub_prefix_alias.waiting, 0) AS need")
                ->having('need', '>', 0)
                ->orderByDesc('need');
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
