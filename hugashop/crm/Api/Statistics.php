<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 * Выбираем статистику
 *
 */

namespace HugaShop\Api;

use HugaShop\Api\Helper;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Order\OrderPurchase;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;
use HugaShop\Api\Warehouse\WarehouseMove;

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
            $query->where('date', '>', $from_date);
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
        return self::productByMonth(array_keys($products), $type);
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

        $query = OrderPurchase::query()
            ->selectRaw('SUM((order_purchase.price * order_purchase.amount) - order_purchase.price * order_purchase.amount * order.discount / 100) as totalPrice')
            ->selectRaw('SUM((order_purchase.price * order_purchase.amount - order_purchase.cost_price * order_purchase.amount) - order_purchase.price * order_purchase.amount * order.discount / 100) as profitPrice')
            ->selectRaw('SUM(order_purchase.amount) as amount')
            ->selectRaw('MONTH(order.date) as month')
            ->selectRaw('YEAR(order.date) as year')
            ->leftJoin('order', 'order_purchase.order_id', '=', 'order.id')
            ->where('order_purchase.product_id', $product_id)
            ->where('order.closed', 1);

        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date . ' 12:00', 'Y-m-d');
            $query->where('order.date', '>', $from_date);
        }

        return $query->get();
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
            ->select('finance_payment.amount', 'finance_payment.currency_amount', 'finance_payment.currency_rate')
            ->selectRaw('MONTH(finance_payment.date) as month')
            ->selectRaw('YEAR(finance_payment.date) as year')
            ->selectRaw('purse.currency_id as currency_id')
            ->selectRaw('cur.position as pos')
            ->leftJoin('finance_purse as purse', 'purse.id', '=', 'finance_payment.purse_id')
            ->leftJoin('finance_currency as cur', 'cur.id', '=', 'purse.currency_id');

        if (isset($filter['type'])) {
            if ($filter['type'] == 'plus' || $filter['type'] == 1) {
                $filter['type'] = 1;
            } elseif ($filter['type'] == 'minus' || $filter['type'] == 0) {
                $filter['type'] = 0;
            }
            $query->where('finance_payment.type', $filter['type']);
        }

        if (isset($filter['payments_ids'])) {
            $query->whereIn('finance_payment.id', (array)$filter['payments_ids']);
        }

        if (isset($filter['related_payment_id']) && $filter['related_payment_id'] === 'NULL') {
            $query->whereNull('finance_payment.related_payment_id');
        } elseif (!empty($filter['related_payment_id'])) {
            $query->where('finance_payment.related_payment_id', $filter['related_payment_id']);
        }

        if (isset($filter['purse_id'])) {
            $query->whereIn('finance_payment.purse_id', (array)$filter['purse_id']);
        }

        if (isset($filter['category_id']) && !empty($filter['category_id'])) {
            $query->leftJoin('finance_category as fc', 'finance_payment.finance_category_id', '=', 'fc.id');
            $query->where('fc.id', (int)$filter['category_id']);
        }

        $data = $query->orderBy('year')
            ->orderBy('month')
            ->get();

        $finances = [];
        foreach ($data as $item) {
            if ($item->pos != 1) {
                if (!empty($item->currency_amount) && intval($item->currency_rate) !== 1) {
                    $item->amount = $item->currency_amount;
                } else {
                    $item->amount = FinanceCurrency::priceConvert((int)$item->amount, FinanceCurrency::getMainCurrency()->id, false, (int)$item->currency_id);
                }
            }
            $finances[$item->year][$item->month][] = (array)$item;
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
}
