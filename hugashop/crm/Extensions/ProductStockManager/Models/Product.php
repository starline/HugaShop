<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 */

namespace HugaShop\Extensions\ProductStockManager\Models;

use Illuminate\Support\Arr;
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
        $products_table = $model->getTable();
        $query->select("$products_table.*");

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

        // Предложение по закупке
        $group_purchase = "";
        if (isset($filter['purchase'])) {

            // По-умолчанию берем продажи за 60 дней
            if (empty($filter['date_from'])) {
                $filter['date_from'] = date('Y-m-d', strtotime('-60 days'));
            }

            $SELECT = Database->placehold(", ordpur.sold as sold, IF (whpur.waiting IS NULL, 0, whpur.waiting) as waiting, varnt.stock as stock, MAX(-(varnt.stock - ordpur.sold*2 + IF (whpur.waiting IS NULL, 0, whpur.waiting))) as need, varnt.id as variant_id ");
            $JOIN = Database->placehold(" 
                LEFT JOIN (SELECT product_id, id, stock FROM __product_variant) varnt on varnt.product_id=p.id 
                LEFT JOIN (SELECT variant_id, SUM(op.amount) as sold FROM __order_purchase op LEFT JOIN __order ord on ord.id = op.order_id WHERE ord.date>? AND ord.paid=1 AND ord.closed=1 GROUP BY variant_id) ordpur on ordpur.variant_id=varnt.id 
                LEFT JOIN (SELECT variant_id, SUM(whp.amount) as waiting FROM __wh_purchase whp LEFT JOIN __wh_move whm on whm.id=whp.move_id WHERE whm.status=1 GROUP BY variant_id) whpur on whpur.variant_id=varnt.id 
                ", $filter['date_from']);
            $WHERE = Database->placehold(" AND ordpur.sold is not null AND -(varnt.stock - ordpur.sold*2 + IF (whpur.waiting IS NULL,0,whpur.waiting)) > 0 ");
            $group_purchase = Database->placehold("GROUP BY p.id");
            $ORDER = Database->placehold("need DESC");
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
