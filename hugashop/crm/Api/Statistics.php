<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 * Выбираем статистику
 *
 */

namespace HugaShop\Api;

use HugaShop\Api\Database;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;

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

        $from_date_filter = '';
        $select_byDday = '';
        $group_byDay = '';
        $filter_orders = '';

        if (empty($price_type)) {
            return false;
        }

        // Выбираем выручку
        if ($price_type == "totalPrice") {
            $sum = " SUM(o.total_price) AS total_price";
        }

        // Выбираем прибыль
        elseif ($price_type == "profitPrice") {
            $sum = " SUM(o.profit_price) AS total_price";
        }

        // Выбираем кол-во заказов
        elseif ($price_type == "amount") {
            $sum = " COUNT(o.id) AS total_price";
        } else {
            return false;
        }

        // Определяем временной диапазон 2020-12-10 13:42:43
        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date . ' 12:00', 'Y-m-d');
            $from_date_filter = Database::placehold(' AND o.date > ?', $from_date);
        }

        if ($date_type == "byDay") {
            $select_byDday =  Database::placehold(' DAY(o.date) AS day, ');
            $group_byDay = Database::placehold(' day, ');
        }

        foreach ($filters as $key => $filter) {
            $filter_orders = Database::placehold(' AND o.' . $key . ' = ?', $filter);
        }


        $query = Database::placehold(
            "SELECT 
				$sum, 
				$select_byDday
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__order o 
			WHERE 
				o.closed 
				$from_date_filter
				$filter_orders
			GROUP BY
				$group_byDay
				month,
				year
			ORDER BY
				year, month"
        );

        $data = DatabaseQuery::query($query)->results();

        $results = [];
        foreach ($data as $d) {

            if ($date_type == "byDay") {
                $result['day'] = $d->day;
            }

            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
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

        $where_type = '';

        $sum = " SUM(whp.amount) AS amount";
        $where_product = Database::placehold(" AND whp.product_id = ?", $product_id);

        if ($type == "add") {
            $where_type = Database::placehold(" AND whm.status = ?", 2);
        } elseif ($type == "delete") {
            $where_type = Database::placehold(" AND whm.status = ?", 3);
        }

        $query = Database::placehold(
            "SELECT 
				$sum,
				MONTH(whm.awaiting_date) as month, 
				YEAR(whm.awaiting_date) as year 
			FROM 
				__wh_purchase as whp
				LEFT JOIN __wh_move whm ON whp.move_id = whm.id
			WHERE 
                whm.closed 
				$where_product
				$where_type 
			GROUP BY 
				month, year
			ORDER BY
				year, month"
        );


        $data = DatabaseQuery::query($query)->results();

        $results = [];
        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->amount;
            $results[] = $result;
        }

        return $results;
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

        //  если переданы id нескольких продуктов
        if (!is_int($product_id)) {
            if (!is_array($product_id)) {
                $product_id = explode(",", $product_id);
            }
            $where_product = Database::placehold(" AND op.product_id in(?@)", $product_id);
        } else {
            $where_product = Database::placehold(" AND op.product_id = ?", $product_id);
        }

        // Вычитаем скидку в % . Так как это единый процент для всех товаров заказа
        if (!empty($type) and $type == "totalPrice") {
            $sum = " SUM((op.price - op.price * o.discount / 100) * op.amount) AS total_price";
        } elseif (!empty($type) and $type == "profitPrice") {
            $sum = " SUM((op.price - op.price * o.discount / 100 - op.cost_price) * op.amount) AS total_price";
        } elseif (!empty($type) and $type == "amount") {
            $sum = " SUM(op.amount) AS total_price";
        } else {
            return false;
        }

        $query = Database::placehold(
            "SELECT 
				$sum,
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__order_purchase op 
				LEFT JOIN __order o ON op.order_id = o.id
			WHERE 
                o.closed
				$where_product 
			GROUP BY 
				month, year
			ORDER BY
				year, month"
        );

        $data = DatabaseQuery::query($query)->results();

        $results = [];
        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим статистку продаж продукта за период
     * @param $product_id
     * @param $from_date
     * @param $to_date
     */
    public static function productByDate($product_id, $from_date = null, $to_date = null)
    {

        $where_date = "";

        $sum_totalPrice = " SUM((op.price * op.amount) - op.price * op.amount * o.discount / 100) AS totalPrice";
        $sum_profitPrice = " SUM((op.price * op.amount - op.cost_price * op.amount) - op.price * op.amount * o.discount / 100) AS profitPrice";
        $sum_amount = " SUM(op.amount) AS amount";

        // Определяем временной диапазон 2020-12-10 13:42:43
        if (!empty($from_date)) {
            $from_date = Helper::dateConvert($from_date . ' 12:00', 'Y-m-d');
            $where_date = Database::placehold(' AND o.date > ?', $from_date);
        }

        $query = Database::placehold(
            "SELECT 
				$sum_totalPrice, 
				$sum_profitPrice, 
				$sum_amount, 
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__order_purchase op 
				LEFT JOIN __order o ON op.order_id = o.id
			WHERE 
				op.product_id = $product_id 
                AND o.closed 
				$where_date"
        );

        return DatabaseQuery::query($query)->results();
    }


    /**
     * Выводим статистку обработаных заказов Менеджера по месяцам
     */
    public static function managerOrdersByMonth(int $manager_id, string $type)
    {

        if (!empty($type) and $type == "totalPrice") {
            $sum = " SUM(o.interest_price) AS total_price";
        } elseif (!empty($type) and $type == "amount") {
            $sum = " COUNT(*) AS total_price";
        } else {
            return false;
        }

        $query = Database::placehold(
            "SELECT 
				$sum,
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__order o 
			WHERE 
				o.manager_id=$manager_id AND 
				o.closed 
			GROUP BY 
				month, year
			ORDER BY
				year, month"
        );

        $data = DatabaseQuery::query($query)->results();

        $results = [];
        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим общий график финансовых платежей
     * @param array $filter
     */
    public static function financeByMonth(array $filter)
    {

        // Определяем тип платежа
        $where_type = "";
        if (isset($filter['type'])) {
            if ($filter['type'] == "plus" || $filter['type'] == 1) {
                $filter['type'] = 1;
            } elseif ($filter['type'] == "minus" || $filter['type'] == 0) {
                $filter['type'] = 0;
            }

            $where_type = Database::placehold(' AND fp.type=?', $filter['type']);
        }

        $where_payments_ids = "";
        if (isset($filter['payments_ids'])) {
            $where_payments_ids = Database::placehold(' AND fp.id in(?@)', (array)$filter['payments_ids']);
        }

        // Исключаем Все переводы $related_payment_id = null
        $where_related_payment_id = "";
        if (isset($filter['related_payment_id']) and $filter['related_payment_id'] == "NULL") {
            $where_related_payment_id = Database::placehold(' AND fp.related_payment_id is NULL');

            // Если отличное от NULL - это перевод с кошелка на кошелек
        } elseif (!empty($filter['related_payment_id'])) {
            $where_related_payment_id = Database::placehold(' AND fp.related_payment_id=?', $filter['related_payment_id']);
        }

        $where_purse_id = "";
        if (isset($filter['purse_id'])) {
            $where_purse_id =  Database::placehold(' AND fp.purse_id in(?@)', (array)$filter['purse_id']);
        }

        $where_category_id = "";
        $category_join = "";
        if (isset($filter['category_id']) && !empty($filter['category_id'])) {
            $where_category_id =  Database::placehold(' AND fc.id = ? ', (int)$filter['category_id']);
            $category_join  = Database::placehold(' LEFT JOIN __finance_category fc ON fp.finance_category_id = fc.id');
        }

        $query = Database::placehold(
            "SELECT 
				fp.amount,
				fp.currency_amount,
                fp.currency_rate,
				MONTH(fp.date) as month,
				YEAR(fp.date) as year,
				purse.currency_id as currency_id,
				cur.position as pos
			FROM 
				__finance_payment fp
				LEFT JOIN __finance_purse as purse ON purse.id = fp.purse_id
				LEFT JOIN __finance_currency as cur ON cur.id = currency_id
				$category_join 
			WHERE
				1
				$where_type 
				$where_purse_id
				$where_related_payment_id
				$where_category_id
				$where_payments_ids
			ORDER BY
				year, month"
        );

        $data = DatabaseQuery::query($query)->results();

        $finances = array();
        foreach ($data as $item) {
            $item = (array) $item;

            // Пропускаем первую валюты - это базовая
            if ($item['pos'] != 1) {
                if (!empty($item['currency_amount']) and intval($item['currency_rate']) !== 1) {
                    $item['amount'] = $item['currency_amount'];
                } else {
                    $item['amount'] = FinanceCurrency::priceConvert((int)$item['amount'], FinanceCurrency::getMainCurrency()->id, false, (int)$item['currency_id']);
                }
            }
            $finances[$item['year']][$item['month']][] = $item;
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
