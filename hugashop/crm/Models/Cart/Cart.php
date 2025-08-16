<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 5.1
 *
 */

namespace HugaShop\Models\Cart;

use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\BaseModel;
use HugaShop\Models\User\User;
use HugaShop\Models\Order\Order;

class Cart extends BaseModel
{

    private static $cookie_cart = 'CART';
    private static $current_cart;

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'token' =>              ['type' => 'varchar'],
        'user_id' =>            ['type' => 'int'],
        'order_id' =>           ['type' => 'int',       'access' => false],
        'created' =>            ['type' => 'datetime',  'def' => 'CURRENT_TIMESTAMP'],
        'session_start' =>      ['type' => 'datetime',  'access' => false],
        'checkout_init' =>      ['type' => 'datetime',  'access' => false],
        'ordered' =>            ['type' => 'datetime',  'access' => false],
        'ip' =>                 ['type' => 'varchar',   'access' => false],
        'user_agent' =>         ['type' => 'varchar',   'access' => false],
        'referral' =>           ['type' => 'varchar',   'access' => false, 'length' => 900],
        'language' =>           ['type' => 'varchar',   'access' => false],
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchases()
    {
        return $this->hasMany(CartPurchase::class, 'cart_id');
    }


    /**
     * Get current cart
     */
    public static function getCurrentCart()
    {
        if (!empty(self::$current_cart)) {
            return self::$current_cart;
        }

        $cart = Cart::getCart(join: ['total']);

        // Join authorized and NOautorized user's carts
        if (!empty($cart->id) and empty($cart->user_id) and User::isLoggedIn()) {

            // TODO: Если корзины нет, но есть старая незаконченная корзина, выбераем ее

            // Проверим есть ли корзина у пользователя
            if (!empty($user_cart = Cart::getCart(['user_id' => User::authUser('id'), 'ordered' => false]))) {
                if (!empty($user_purchases = CartPurchase::getCartPurchases(['cart_id' => $user_cart->id]))) {
                    foreach ($user_purchases as $purch) {
                        CartPurchase::addCartPurchase($purch->product_id, $purch->amount);
                    }
                    Cart::deleteCart($user_cart->id);
                }
            }

            Cart::updateCart($cart->id, ['user_id' => User::authUser('id')]);
            $cart = Cart::getCart(join: ['total']);
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
        $query = self::query();

        if (!empty($cart_id)) {
            if (is_array($cart_id)) {
                if (!empty($cart_id['user_id'])) {
                    $query->where('user_id', intval($cart_id['user_id']));
                }
                if (array_key_exists('ordered', $cart_id) && $cart_id['ordered'] === false) {
                    $query->whereNull('ordered');
                }
            } else {
                $query->where('id', intval($cart_id));
            }
        } elseif ($cart_token = Request::getSession(self::$cookie_cart)) {
            $query->where('token', $cart_token);
        } elseif ($cart_token = Request::getCookie(self::$cookie_cart)) {
            $query->where('token', $cart_token);
        } else {
            return null;
        }

        if (in_array('total', $join)) {
            $query->with(['purchases' => function ($query) {
                $query->where('disabled', 0)->with('product');
            }]);
        }
        if (in_array('user', $join)) {
            $query->with('user');
        }
        if (in_array('order', $join)) {
            $query->with('order');
        }

        $cart = $query->first();

        if (in_array('total', $join) && !empty($cart)) {
            $cart->purchases_count = $cart->purchases->sum('amount');
            $cart->purchases_price = $cart->purchases->sum(fn($purchase) => $purchase->product->price * $purchase->amount);
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
        $cart->language =       Request::post('language', 'string');
        $cart->session_start =  Request::getSession('session_start') ?? null;
        $cart->referral =       Request::getSession('referral') ?? null;

        Request::setSession(self::$cookie_cart, $cart->token);
        Request::setCookie(self::$cookie_cart, $cart->token, 360);

        return Cart::createOne($cart)->id;
    }


    /**
     * Clear Cart
     * @param int $id
     */
    public static function deleteCart(int $id): bool
    {
        self::deleteOne($id);
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
        return self::updateOne($id, $cart);
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

            // Except some GET params
            foreach ($gets as $get_key => $p) {
                if (!in_array($get_key, ['page', 'variant', 'sort'])) {
                    Request::setSession('referral', serialize(Request::gets()));
                    break;
                }
            }
        }
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
