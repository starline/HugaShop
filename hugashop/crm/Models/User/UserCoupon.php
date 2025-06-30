<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;
use HugaShop\Services\Request;

class UserCoupon extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'code' =>               ['type' => 'varchar',       'req' => true],
        'expire' =>             ['type' => 'datetime'],
        'type' =>               ['type' => 'varchar',       'lenght' => 20,     'def' => 'absolute'],
        'value' =>              ['type' => 'decimal',       'lenght' => 10.2,   'def' => 0.00],
        'min_order_price' =>    ['type' => 'decimal',       'lenght' => 10.2,   'def' => 0.00],
        'single' =>             ['type' => 'tinyint',       'def' => 0],
        'usages' =>             ['type' => 'int',           'def' => 0]
    ];

    public static $table_keys = [
        'code' => ['code']
    ];

    /**
     * Функция возвращает купон по его id или code
     * @param int|string $id - id или code купона
     */
    public static function getCoupon(int|string $id)
    {
        $now = date("Y-m-d H:i:s");
        $query = UserCoupon::query()
            ->selectRaw('*, ((DATE(?) <= DATE(expire) OR expire IS NULL) AND (usages = 0 OR NOT single)) AS valid', [$now]);

        // Определяем, искать по code или по id
        if (is_string($id)) {
            $query->where('code', $id);
        } else {
            $query->where('id', intval($id));
        }

        return $query->first();
    }


    /**
     * Функция возвращает массив купонов, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function getCoupons(array $filter = [], $count = false)
    {
        $query = UserCoupon::query();
        $now = date("Y-m-d H:i:s");

        // Фильтр по id
        if (!empty($filter['id'])) {
            $query->whereIn('id', (array) $filter['id']);
        }

        // Фильтр по валидности
        if (isset($filter['valid'])) {
            $valid_query = '((DATE(?) <= DATE(expire) OR expire IS NULL) AND (usages = 0 OR NOT single))';
            if ($filter['valid']) {
                $query->whereRaw($valid_query, [$now]);
            } else {
                $query->whereRaw("NOT $valid_query", [$now]);
            }
        }

        // Поиск по ключевым словам
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw !== '') {
                    $query->where('code', 'like', '%' . $kw . '%');
                }
            }
        }


        // Count
        if ($count === true) {
            return $query->count();
        }

        // Select
        else {

            // Добавляем вычисляемое поле `valid`
            $query->select('*')
                ->selectRaw(
                    '((DATE(?) <= DATE(expire) OR expire IS NULL) AND (usages = 0 OR NOT single)) AS valid',
                    [$now]
                );

            // Сортировка
            $query->orderByDesc('valid')->orderByDesc('id');

            // Лимит и пагинация
            if (isset($filter['limit']) && $filter['limit'] !== 'all') {
                $limit = max(1, intval($filter['limit']));
                $page = isset($filter['page']) ? max(1, intval($filter['page'])) : 1;
                $query->skip(($page - 1) * $limit)->take($limit);
            }

            return $query->get();
        }
    }


    /**
     * Функция вычисляет количество, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function countCoupons(array $filter = [])
    {
        return UserCoupon::getCoupons($filter, count: true);
    }


    /**
     * Apply Coupon
     * @param string $coupon_code
     */
    public static function applyCoupon(string $coupon_code): void
    {
        $coupon = UserCoupon::getCoupon($coupon_code);
        if ($coupon && $coupon->valid) {
            Request::setSession('coupon_code', $coupon->code);
        } else {
            Request::deleteSession('coupon_code');
        }
    }
}
