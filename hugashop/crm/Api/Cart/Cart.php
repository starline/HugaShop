<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.8
 *
 */

namespace HugaShop\Api\Cart;

use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Database;
use HugaShop\Api\User\User;
use HugaShop\Api\Order\Order;
use HugaShop\Api\DatabaseQuery;

class Cart extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
            'token' =>              ['type' => 'varchar'],
            'user_id' =>            ['type' => 'int'],
            'order_id' =>           ['type' => 'int',           'access' => false],
            'created' =>            ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
            'session_start' =>      ['type' => 'datetime',      'access' => false],
            'checkout_init' =>      ['type' => 'datetime',      'access' => false],
            'ordered' =>            ['type' => 'datetime',      'access' => false],
            'ip' =>                 ['type' => 'varchar',       'access' => false],
            'user_agent' =>         ['type' => 'varchar',       'access' => false],
            'referral' =>           ['type' => 'varchar',       'access' => false,  'length' => 900],
            'language' =>           ['type' => 'varchar',       'access' => false]
        ],
        'join' => [
            Order::class => ['id' => 'order_id'],
            User::class => ['id' => 'user_id']
        ]
    ];

    private static $cookie_cart = 'CART';
    private static $current_cart;


    /**
     * Get current cart
     */
    public static function getCurrentCart()
    {
        if (!empty(self::$current_cart)) {
            return self::$current_cart;
        }

        $cart = Cart::getCart(null, ['total']);

        // Join authorized and NOautorized user's carts
        if (!empty($cart->id) and empty($cart->user_id) and User::isLoggedIn()) {

            // TODO: Если корзины нет, но есть старая незаконченная корзина, выбераем ее

            // Проверим есть ли корзина у пользователя
            if (!empty($user_cart = Cart::getCart(['user_id' => User::authUser('id'), 'ordered' => false]))) {
                if (!empty($user_purchases = CartPurchase::getCartPurchases(['cart_id' => $user_cart->id]))) {
                    foreach ($user_purchases as $purch) {
                        CartPurchase::addCartPurchase($purch->variant_id, $purch->amount);
                    }
                    Cart::deleteCart($user_cart->id);
                }
            }

            Cart::updateCart($cart->id, ['user_id' => User::authUser('id')]);
            $cart = Cart::getCart(null, ['total']);
        }

        return self::$current_cart = $cart;
    }


    /**
     * Get cart with purchases
     * @param int|array $id
     * @param array $join
     */
    public static function getCart(int|array|null $cart_id = null, array $join = [])
    {

        $alias = self::getAlias();

        $WHERE = '';
        if (!empty($cart_id)) {
            if (is_array($cart_id)) {

                if (!empty($cart_id['user_id'])) {
                    $WHERE .= Database::placehold(" AND `$alias`.user_id=? ", intval($cart_id['user_id']));
                }

                if ($cart_id['ordered'] === false) {
                    $WHERE .= Database::placehold(" AND `$alias`.ordered=? ", null);
                }
            } else {
                $WHERE .= Database::placehold(" AND `$alias`.id=? ", intval($cart_id));
            }
        }

        // Берем из сессии
        elseif (!empty($cart_token = Request::getSession(self::$cookie_cart))) {
            $WHERE .= Database::placehold(" AND `$alias`.token=? ", $cart_token);
        }

        // Берем из Cookie
        elseif (!empty($cart_token = Request::getCookie(self::$cookie_cart))) {
            $WHERE .= Database::placehold(" AND `$alias`.token=? ", $cart_token);
        } else {
            return false;
        }


        // TODO: возвращает сроку с NULL параметрами из-за SUM(cp.amount)

        // JOIN AMOUNT
        // SUM(cp.amount) - отдает одну строку
        $SELECT = self::makeSelect();
        $JOIN = '';
        if (in_array("total", $join)) {
            $SELECT .= Database::placehold(", SUM(cp.amount) as purchases_count, SUM(pv.price*cp.amount) as purchases_price");
            $JOIN .= Database::placehold(" LEFT JOIN __cart_purchase cp ON cp.cart_id = `$alias`.id AND cp.disabled=0");
            $JOIN .= Database::placehold(" LEFT JOIN __product_variant pv ON pv.id = cp.variant_id");
        }

        // Get cart
        // @link https://dev.mysql.com/doc/refman/8.4/en/group-by-handling.html
        $query =
            "SELECT 
                $SELECT
			FROM 
				__cart `$alias`
                $JOIN
            WHERE 
                1 
			    $WHERE
            GROUP BY 
                `$alias`.id
			LIMIT 
				1";

        return Cart::query($query)->result();


        /*
            // Пользовательская скидка
            $cart->discount = 0;
            if (!empty(User::authUser('id'))) {
                $cart->discount = $user->group->discount;
            }

            $cart->total_price *= (100 - $cart->discount) / 100;

            // Скидка по купону
            if (!empty(Request::getSession('coupon_code'))) {
                $cart->coupon = UserCoupon::getCoupon(Request::getSession('coupon_code'));
                if ($cart->coupon && $cart->coupon->valid && $cart->total_price >= $cart->coupon->min_order_price) {
                    if ($cart->coupon->type == 'absolute') {

                        // Абсолютная скидка не более суммы заказа
                        $cart->coupon_discount = $cart->total_price > $cart->coupon->value ? $cart->coupon->value : $cart->total_price;
                        $cart->total_price = max(0, $cart->total_price - $cart->coupon->value);
                    } else {
                        $cart->coupon_discount = $cart->total_price * ($cart->coupon->value) / 100;
                        $cart->total_price = $cart->total_price - $cart->coupon_discount;
                    }
                } else {
                    Request::deleteSession('coupon_code');
                }
            }
        }*/
    }


    /**
     * Get cart info
     * @param array $filter
     */
    public static function getCartInfo(array $filter)
    {
        $cart = Cart::getOne($filter);

        if (!empty($cart->user_agent)) {
            $cart->user_agent = Helper::getUserAgentInfo($cart->user_agent);
        }

        if (!empty($cart->referral) and !empty($gets = @unserialize($cart->referral))) {
            $cart->referral = Cart::getReferral($gets);
        }

        return $cart;
    }


    /**
     * Add cart
     * @param object $cart
     */
    public static function addCart(object $cart = new \stdClass())
    {

        // Define Logined User
        if (empty($cart->user_id) and !empty(User::authUser('id'))) {
            $cart->user_id = User::authUser('id');
        }

        $cart->created =        date("Y-m-d H:i:s"); # Example: 2021-06-09 14:17:25
        $cart->token =          Helper::makeToken(uniqid(), 16);
        $cart->ip =             $_SERVER['REMOTE_ADDR'];
        $cart->user_agent =     $_SERVER['HTTP_USER_AGENT']; # Browser
        $cart->language =       Request::post('language', 'string') ?? null;
        $cart->session_start =  Request::getSession('session_start') ?? null;
        $cart->referral =       Request::getSession('referral') ?? null;

        Request::setSession(self::$cookie_cart, $cart->token);
        Request::setCookie(self::$cookie_cart, $cart->token, 360);

        // Save to DB
        return self::insert($cart)->getInsertId();
    }


    /**
     * Clear Cart
     * @param int $id
     */
    public static function deleteCart(int $id): bool
    {
        Cart::deleteOne($id);

        // Delete purchases
        return CartPurchase::deletePurchase($id);
    }


    /**
     * Clean cart
     */
    public static function cleanCart()
    {
        Request::deleteSession('session_start');
        Request::deleteSession('coupon_code');
        Request::deleteSession(self::$cookie_cart);
        Request::deleteCookie(self::$cookie_cart);
    }


    /**
     * Update Cart
     * @param int $id
     * @param  object|array $cart
     */
    public static function updateCart(int $id, object|array $cart): bool
    {

        if (empty((array)$cart)) {
            return false;
        }

        return Cart::updateOne($id, $cart);
    }


    /**
     * Catch Cart Session
     */
    public static function catchCartSession()
    {
        if (empty(Request::getSession('session_start'))) {
            Request::setSession('session_start', date('Y-m-d H:i:s'));
        }

        if (!empty($gets = Request::gets())) {

            // Except
            foreach ($gets as $get_key => $get_value) {
                if (!in_array($get_key, ['page', 'variant', 'sort'])) {
                    Request::setSession('referral', serialize(Request::gets()));
                    break;
                }
            }
        }
    }


    /**
     * Get referral
     * @param array $gets
     */
    public static function getReferral(array $gets)
    {

        $referral_name = '';
        foreach ($gets as $get_key => $get_value) {
            if (in_array($get_key, ['fbclid'])) {
                $referral_name = 'Facebook';
                break;
            }

            if (in_array($get_key, ['gclid', 'gbraid', 'wbraid'])) {
                $referral_name = 'Google Ads';
                break;
            }

            if (in_array($get_key, ['srsltid'])) {
                $referral_name = 'Google Shopping';
                break;
            }
        }
        return $referral_name;
    }
}
