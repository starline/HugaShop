<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.8
 *
 * Get statistic data
 *
 */

namespace HugaShop\Services;

use HugaShop\Services\Helper;
use HugaShop\Models\Cart\Cart;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Finance\FinancePayment;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Models\Warehouse\WarehouseMove;
use HugaShop\Models\Product\ProductPriceHistory;

class Statistics
{
    /**
     * Выводим статистику Заказов по Дням/Месяцам
     * @param $price_type - totalPrice/profitPrice/amount
     * @param  $date_type
     * @param $from_date
     * @param $to_date
     * @param $filters
     */
    public static function ordersSum($price_type, $date_type = "byMonth", $from_date = null, $to_date = null, $filters = [])
    {

        if (empty($price_type)) {
            return false;
        }

        $query = Order::query();

        if ($price_type === 'totalPrice') {
            $query->selectRaw('SUM(total_price) as total_price');
        } elseif ($price_type === 'profitPrice') {
            $query->selectRaw('SUM(profit_price) as total_price');
        } elseif ($price_type === 'amount') {
            $query->selectRaw('COUNT(id) as total_price');
        } else {
            return false;
        }

        $query->selectRaw('MONTH(date) as month')
            ->selectRaw('YEAR(date) as year')
            ->where('closed', 1);

        if ($date_type === 'byDay') {
            $query->selectRaw('DAY(date) as day');
            $query->groupBy('day');
        }

        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date . ' 12:00', 'Y-m-d');
            $query->where('date', '>=', $from_date);
        }

        if (!empty($to_date)) {
            $to_date = Helper::dateConvert($to_date . ' 12:00', 'Y-m-d');
            $query->where('date', '<=', $to_date);
        }

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        $data = $query->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $results = [];
        foreach ($data as $d) {
            $res = [
                'month' => $d->month,
                'year'  => $d->year,
                'y'     => $d->total_price,
            ];
            if ($date_type === 'byDay') {
                $res['day'] = $d->day;
            }
            $results[] = $res;
        }

        return $results;
    }


    /**
     * Выводим статистку перемещений на складе продукта по мемяцам
     * @param $product_id
     * @param $type
     */
    public static function productWarehouseMovemetByMonth($product_id, $type)
    {
        $query = WarehouseMove::query()
            ->selectRaw('MONTH(awaiting_date) as month')
            ->selectRaw('YEAR(awaiting_date) as year')
            ->where('closed', 1)
            ->whereHas('purchases', function ($q) use ($product_id) {
                $q->where('product_id', $product_id);
            })
            ->withSum([
                'purchases as amount' => function ($q) use ($product_id) {
                    $q->where('product_id', $product_id);
                }
            ], 'amount');

        if ($type === 'add') {
            $query->where('status', 2);
        } elseif ($type === 'delete') {
            $query->where('status', 3);
        }

        $data = $query
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $grouped = [];
        foreach ($data as $d) {
            $key = $d->year . '-' . $d->month;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'month' => (int) $d->month,
                    'year'  => (int) $d->year,
                    'y'     => 0,
                ];
            }
            $grouped[$key]['y'] += (int) $d->amount;
        }

        return array_values($grouped);
    }


    /**
     * Выводим статистику продаж категории продуктов по мемяцам
     * @param int $category_id
     * @param string $type
     */
    public static function productsCategoryByMonth(int $category_id, string $type)
    {
        if (empty($category_id)) {
            return false;
        }

        // Выбрать все товары категории
        $category = ProductCategory::getCategoryById($category_id);
        $filter['category_id'] = $category->children; # id  всех дочерних категорий
        $filter['limit'] = 'all';

        $products = Product::getProducts($filter);

        // Извлекаем product_id из объектов
        $product_ids = collect($products)->pluck('id')->all();

        return self::productByMonth($product_ids, $type);
    }


    /**
     * Выводим статистку продаж продукта по мемяцам
     * @param array|int $product_id - ID or Array(id, id, ...) or srting(id, id, ...) of prodict
     * @param string $type
     */
    public static function productByMonth(array|int $product_id, string $type)
    {

        $query = OrderPurchase::query()
            ->with('order')
            ->whereHas('order', function ($q) {
                $q->where('closed', 1);
            });

        if (!is_int($product_id)) {
            if (!is_array($product_id)) {
                $product_id = explode(',', $product_id);
            }
            $query->whereIn('product_id', $product_id);
        } else {
            $query->where('product_id', $product_id);
        }

        $purchases = $query->get();

        $data = [];
        foreach ($purchases as $purchase) {
            if (!$purchase->order) {
                continue;
            }

            $month = (int)date('n', strtotime($purchase->order->date));
            $year  = (int)date('Y', strtotime($purchase->order->date));
            $key   = sprintf('%04d-%02d', $year, $month);

            if (!isset($data[$key])) {
                $data[$key] = [
                    'month' => $month,
                    'year'  => $year,
                    'y'     => 0,
                ];
            }

            if ($type === 'totalPrice') {
                $data[$key]['y'] += ($purchase->price - $purchase->price * $purchase->order->discount / 100) * $purchase->amount;
            } elseif ($type === 'profitPrice') {
                $data[$key]['y'] += ($purchase->price - $purchase->price * $purchase->order->discount / 100 - $purchase->cost_price) * $purchase->amount;
            } elseif ($type === 'amount') {
                $data[$key]['y'] += $purchase->amount;
            } else {
                return false;
            }
        }

        usort($data, function ($a, $b) {
            if ($a['year'] == $b['year']) {
                return $a['month'] <=> $b['month'];
            }
            return $a['year'] <=> $b['year'];
        });

        return array_values($data);
    }


    /**
     * Выводим статистку продаж продукта за период
     * @param $product_id
     * @param $from_date
     * @param $to_date
     */
    public static function productByDate($product_id, $from_date = null, $to_date = null)
    {
        $query = Order::query()
            ->with(['purchases' => function ($q) use ($product_id) {
                $q->where('product_id', $product_id);
            }])
            ->where('closed', 1);

        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date . ' 12:00', 'Y-m-d');
            $query->where('date', '>=', $from_date);
        }

        if (!empty($to_date)) {
            $to_date = Helper::dateConvert($to_date . ' 12:00', 'Y-m-d');
            $query->where('date', '<=', $to_date);
        }

        $orders = $query->get();

        $totalPrice  = 0;
        $profitPrice = 0;
        $amount      = 0;

        foreach ($orders as $order) {
            foreach ($order->purchases as $purchase) {
                $totalPrice  += ($purchase->price - $purchase->price * $order->discount / 100) * $purchase->amount;
                $profitPrice += ($purchase->price - $purchase->price * $order->discount / 100 - $purchase->cost_price) * $purchase->amount;
                $amount      += $purchase->amount;
            }
        }

        return (object) [
            'totalPrice'  => $totalPrice,
            'profitPrice' => $profitPrice,
            'amount'      => $amount,
        ];
    }

    /**
     * Выводим статистку обработаных заказов Менеджера по месяцам
     */
    public static function managerOrdersByMonth(int $manager_id, string $type)
    {

        $query = Order::query()
            ->where('manager_id', $manager_id)
            ->where('closed', 1);

        if (!empty($type) && $type === 'totalPrice') {
            $query->selectRaw('SUM(interest_price) as total_price');
        } elseif (!empty($type) && $type === 'amount') {
            $query->selectRaw('COUNT(*) as total_price');
        } else {
            return false;
        }

        $query->selectRaw('MONTH(date) as month')
            ->selectRaw('YEAR(date) as year');

        $data = $query->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $results = [];
        foreach ($data as $d) {
            $results[] = [
                'month' => $d->month,
                'year'  => $d->year,
                'y'     => $d->total_price,
            ];
        }

        return $results;
    }


    /**
     * Выводим общий график финансовых платежей
     * @param array $filter
     */
    public static function financeByMonth(array $filter)
    {

        $query = FinancePayment::query()
            ->select('purse_id', 'amount', 'currency_amount', 'currency_rate')
            ->selectRaw('MONTH(date) as month')
            ->selectRaw('YEAR(date) as year')
            ->with('purse.currency');

        if (isset($filter['type'])) {
            if ($filter['type'] == 'plus' || $filter['type'] == 1) {
                $filter['type'] = 1;
            } elseif ($filter['type'] == 'minus' || $filter['type'] == 0) {
                $filter['type'] = 0;
            }
            $query->where('type', $filter['type']);
        }

        if (isset($filter['payments_ids'])) {
            $query->whereIn('id', (array)$filter['payments_ids']);
        }

        if (isset($filter['related_payment_id']) && $filter['related_payment_id'] === 'NULL') {
            $query->whereNull('related_payment_id');
        } elseif (!empty($filter['related_payment_id'])) {
            $query->where('related_payment_id', $filter['related_payment_id']);
        }

        if (isset($filter['purse_id'])) {
            $query->whereIn('purse_id', (array)$filter['purse_id']);
        }

        if (isset($filter['category_id']) && !empty($filter['category_id'])) {
            $query->whereHas('category', function ($q) use ($filter) {
                $q->where('id', (int) $filter['category_id']);
            });
        }

        if (!empty($filter['fromDate'])) {
            $from_date = Helper::dateConvert($filter['fromDate'], 'Y-m-d');
            $query->where('date', '>=', $from_date);
        }

        if (!empty($filter['toDate'])) {
            $to_date = Helper::dateConvert($filter['toDate'], 'Y-m-d');
            $query->where('date', '<=', $to_date);
        }

        $data = $query->orderBy('year')
            ->orderBy('month')
            ->get();

        $finances = [];
        foreach ($data as $item) {
            $currency = $item->purse?->currency;
            if ($currency && $currency->position != 1) {
                if (!empty($item->currency_amount) && intval($item->currency_rate) !== 1) {
                    $item->amount = $item->currency_amount;
                } else {
                    $item->amount = FinanceCurrency::priceConvert(
                        (int) $item->amount,
                        FinanceCurrency::getMainCurrency()->id,
                        false,
                        (int) $currency->id
                    );
                }
            }
            $finances[$item->year][$item->month][] = $item->toArray();
        }

        $results = array();
        foreach ($finances as $key_y => $year) {
            foreach ($year as $key_m => $month) {
                $info = array();
                $info['month'] = $key_m;
                $info['year'] = $key_y;
                $info['y'] = array_sum(array_column($month, 'amount'));
                $results[] = $info;
            }
        }

        return $results;
    }


    /**
     * Возвращает историю изменения цены товара по дням
     */
    public static function productPriceHistoryByDay(int $product_id, ?string $type = null)
    {
        if (empty($product_id)) {
            return [];
        }

        $records = ProductPriceHistory::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('MAX(price) as price')
            ->selectRaw('MAX(cost_price) as cost_price')
            ->where('product_id', $product_id)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if (in_array($type, ['price', 'costPrice'], true)) {
            $results = [];
            foreach ($records as $rec) {
                $dt = new \DateTime($rec->date);
                $results[] = [
                    'day'   => (int) $dt->format('d'),
                    'month' => (int) $dt->format('m'),
                    'year'  => (int) $dt->format('Y'),
                    'y'     => ($type === 'price') ? (float) $rec->price : (float) $rec->cost_price,
                ];
            }

            return $results;
        }

        $result = [];
        foreach ($records as $rec) {
            $result[] = [
                'date' => $rec->date,
                'price' => (float) $rec->price,
                'cost_price' => (float) $rec->cost_price,
            ];
        }

        return $result;
    }


    /**
     * Возвращает историю корзин по дням
     *
     * @param string|null $from_date
     * @param string|null $to_date
     */
    public static function cartsByDay(?string $from_date = null, ?string $to_date = null, ?string $type = null): array
    {
        $query = Cart::query()->with('order');

        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date, 'Y-m-d');
            $query->where('created', '>=', $from_date);
        }

        if (!empty($to_date)) {
            $to_date = Helper::dateConvert($to_date, 'Y-m-d');
            $query->where('created', '<=', $to_date);
        }

        $records = $query->get();

        $grouped = [];
        foreach ($records as $cart) {
            $date = date('Y-m-d', strtotime($cart->created));

            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'date'    => $date,
                    'carts'   => 0,
                    'ordered' => 0,
                    'paid'    => 0,
                ];
            }

            $grouped[$date]['carts']++;
            if (!empty($cart->order_id)) {
                $grouped[$date]['ordered']++;
            }
            if ($cart->order && $cart->order->paid) {
                $grouped[$date]['paid']++;
            }
        }

        ksort($grouped);

        // If type is specified, convert to generic structure compatible with
        // common.js::getChartData
        if (in_array($type, ['carts', 'ordered', 'paid'], true)) {
            $results = [];
            foreach ($grouped as $date => $stats) {
                $dt = new \DateTime($date);
                $results[] = [
                    'day'   => (int) $dt->format('d'),
                    'month' => (int) $dt->format('m'),
                    'year'  => (int) $dt->format('Y'),
                    'y'     => (int) $stats[$type],
                ];
            }

            return $results;
        }

        return array_values($grouped);
    }


    /**
     * Возвращает историю корзин по месяцам
     */
    public static function cartsByMonth(?string $from_date = null, ?string $to_date = null, ?string $type = null): array
    {
        $query = Cart::query()->with('order')
            ->selectRaw('MONTH(created) as month')
            ->selectRaw('YEAR(created) as year');

        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date, 'Y-m-d');
            $query->where('created', '>=', $from_date);
        }

        if (!empty($to_date)) {
            $to_date = Helper::dateConvert($to_date, 'Y-m-d');
            $query->where('created', '<=', $to_date);
        }

        if (in_array($type, ['carts', 'ordered', 'paid'], true)) {
            $query->selectRaw('COUNT(*) as total');

            if ($type === 'ordered') {
                $query->whereNotNull('order_id');
            } elseif ($type === 'paid') {
                $query->whereHas('order', fn($q) => $q->where('paid', 1));
            }

            $records = $query->groupBy('month', 'year')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $results = [];
            foreach ($records as $rec) {
                $results[] = [
                    'month' => (int) $rec->month,
                    'year'  => (int) $rec->year,
                    'y'     => (int) $rec->total,
                ];
            }

            return $results;
        }

        $query->selectRaw('COUNT(*) as carts')
            ->selectRaw('SUM(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) as ordered')
            ->selectRaw('SUM(CASE WHEN orders.paid = 1 THEN 1 ELSE 0 END) as paid')
            ->leftJoin('orders', 'orders.id', '=', 'carts.order_id');

        $records = $query->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $results = [];
        foreach ($records as $rec) {
            $results[] = [
                'month'   => (int) $rec->month,
                'year'    => (int) $rec->year,
                'carts'   => (int) $rec->carts,
                'ordered' => (int) $rec->ordered,
                'paid'    => (int) $rec->paid,
            ];
        }

        return $results;
    }
}
