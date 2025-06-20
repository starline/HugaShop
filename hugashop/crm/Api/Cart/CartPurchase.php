<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.1
 *
 */

namespace HugaShop\Api\Cart;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;

class CartPurchase extends BaseModel
{
    public static $table_fields = [
        'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'cart_id' =>            ['type' => 'int'],
        'product_id' =>         ['type' => 'int'],
        'amount' =>             ['type' => 'int',       'def' => 0],
        'created' =>            ['type' => 'datetime',  'def' => 'CURRENT_TIMESTAMP'],
        'disabled' =>           ['type' => 'tinyint',   'def' => 0],
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    /**
     * Get cart purchases
     * @param array $filter
     * @param array $join = ['product.image', 'product', 'product.category']
     */
    public static function getCartPurchases(array $filter = [], array $join = [])
    {
        $query = self::query();

        if (isset($filter['cart_id'])) {
            if (!empty($filter['cart_id'])) {
                $query->whereIn('cart_id', (array)$filter['cart_id']);
            } else {
                return [];
            }
        }

        if (isset($filter['disabled'])) {
            $query->where('disabled', $filter['disabled']);
        }

        if ($join) {
            $query->with($join);
        }

        return $query->get();
    }


    /**
     * Add product to cart
     * @param int $product_id
     * @param int|null $amount
     */
    public static function addCartPurchase(int $product_id, ?int $amount = 1)
    {
        $product = Product::getOne($product_id);

        // Check is product available
        if (empty($product) || ($product->stock == 0 and !$product->custom)) {
            return false;
        }

        $amount = max(1, $amount);
        $new_purchase = true;

        // Get Cart
        if (!empty($cart = Cart::getCart())) {
            $cart_id = $cart->id;

            $purchases = CartPurchase::getCartPurchases(['cart_id' => $cart_id]);
            foreach ($purchases as $purchase) {
                if ($purchase->product_id == $product->id) {
                    $amount = max(1, $amount + $purchase->amount);
                    $new_purchase = false;
                }
            }
        }

        // Create cart
        else {
            $cart_id = Cart::addCart();
        }

        // Не дадим больше чем на складе, если не под заказ
        if (empty($product->custom)) {
            $amount = min($amount, $product->stock);
        }

        if (empty($cart_id)) {
            return false;
        }

        $data = [
            'cart_id' => $cart_id,
            'product_id' => $product->id,
            'amount' => $amount,
            'created' => date('Y-m-d H:i:s'),
        ];

        if ($new_purchase === true) {
            return self::create($data)->id;
        }

        return self::updatePurchase($cart_id, $product->id, ['amount' => $amount, 'disabled' => 0]);
    }


    /**
     * Update
     * @param int|null $cart_id
     * @param int $product_id
     * @param array|object $purchase
     */
    public static function updatePurchase(int|null $cart_id, int $product_id, array|object $purchase)
    {

        // Get cart
        if (is_null($cart_id)) {
            if (empty($cart = Cart::getCart())) {
                return false;
            }

            $cart_id = $cart->id;
        }

        if (isset($purchase->amount)) {

            // Выберем товар из базы, заодно убедившись в его существовании
            $product = Product::getOne($product_id);
            if (empty($product) || ($product->stock == 0 and !$product->custom)) {
                return false;
            }


            $purchase->amount = max(1, $purchase->amount);

            // Не дадим больше чем на складе, если не под заказ
            if (empty($product->custom)) {
                $purchase->amount = min($purchase->amount, $product->stock);
            }
        }

        return self::query()
            ->where('cart_id', $cart_id)
            ->where('product_id', $product_id)
            ->update((array)$purchase);
    }


    /**
     * Удаление товара из корзины
     * @param int $cart_id
     * @param int $product_id
     */
    public static function deletePurchase(?int $cart_id = null, ?int $product_id = null)
    {
        // Get cart
        if (is_null($cart_id)) {
            if (empty($cart = Cart::getCart())) {
                return false;
            }

            $cart_id = $cart->id;
        }

        $query = self::query();

        if (!empty($product_id)) {
            $query->where('product_id', $product_id);
        }

        if (!empty($cart_id)) {
            $query->where('cart_id', $cart_id);
        }

        return $query->delete();
    }
}
