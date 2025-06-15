<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 */

namespace HugaShop\Api\Cart;

use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Product\ProductCategory;

class CartPurchase extends BaseModel
{
    public static $table_fields = [
        'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'cart_id' =>            ['type' => 'int'],
        'product_id' =>         ['type' => 'int'],
        'variant_id' =>         ['type' => 'int'],
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

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }


    /**
     * Get cart purchases
     * @param array $filter
     * @param array $join = ['image', 'product', 'variant', 'category']
     */
    public static function getCartPurchases(array $filter = [], array $join = [])
    {
        $query = self::query()->select('cart_purchase.*');

        if (isset($filter['cart_id'])) {
            if (!empty($filter['cart_id'])) {
                $query->whereIn('cart_purchase.cart_id', (array)$filter['cart_id']);
            } else {
                return [];
            }
        }

        if (isset($filter['disabled'])) {
            $query->where('cart_purchase.disabled', $filter['disabled']);
        }

        if (in_array('image', $join)) {
            $query->leftJoin('content_image as i', function ($join) {
                $join->on('i.entity_id', '=', 'cart_purchase.product_id')
                    ->where('i.entity_name', 'product')
                    ->whereRaw('(i.position = (SELECT MIN(position) FROM content_image WHERE entity_id=cart_purchase.product_id and entity_name="product"))');
            })->addSelect('i.filename as image_filename');
        }

        if (in_array('product', $join) || in_array('category', $join)) {
            $query->leftJoin('product as p', 'p.id', '=', 'cart_purchase.product_id')
                ->addSelect(['p.name as product_name', 'p.url as product_url']);
        }

        if (in_array('category', $join)) {
            $query->leftJoin('product_category as pc', 'pc.id', '=', 'p.category_id')
                ->addSelect(['pc.name as category_name', 'pc.id as category_id', 'pc.url as category_url']);
        }

        if (in_array('variant', $join)) {
            $query->leftJoin('product_variant as pv', 'pv.id', '=', 'cart_purchase.variant_id')
                ->addSelect([
                    'pv.name as variant_name',
                    'pv.sku as variant_sku',
                    'pv.price as variant_price',
                    'pv.cost_price as variant_cost_price',
                    'pv.stock as variant_stock',
                    'pv.custom as variant_custom',
                    'pv.weight as variant_weight',
                ]);
        }

        $query->orderBy('cart_purchase.cart_id');

        $purchases = $query->get();
        $purchases_normalized = Helper::normalizeObjectData($purchases->toArray());

        if (in_array('category', $join)) {
            foreach ($purchases_normalized as $purchase) {
                if (!empty($purchase->category->id)) {
                    $purchase->category = ProductCategory::getCategoryById($purchase->category->id);
                }
            }
        }

        return $purchases_normalized;
    }


    /**
     * Add product variant to cart
     * @param int $variant_id
     * @param int|null $amount
     */
    public static function addCartPurchase(int $variant_id, ?int $amount = 1)
    {
        $variant = ProductVariant::getVariant($variant_id);

        // Check is variant available
        if (empty($variant) || ($variant->stock == 0 and !$variant->custom)) {
            return false;
        }

        $amount = max(1, $amount);
        $new_purchase = true;

        // Get Cart
        if (!empty($cart = Cart::getCart())) {
            $cart_id = $cart->id;

            $purchases = self::getCartPurchases(['cart_id' => $cart_id]);
            foreach ($purchases as $purchase) {
                if ($purchase->variant_id == $variant->id) {
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
        if (empty($variant->custom)) {
            $amount = min($amount, $variant->stock);
        }

        if (empty($cart_id)) {
            return false;
        }

        $data = [
            'cart_id' => $cart_id,
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'amount' => $amount,
            'created' => date('Y-m-d H:i:s'),
        ];

        if ($new_purchase === true) {
            return self::create($data)->id;
        }

        return self::updatePurchase($cart_id, $variant->id, ['amount' => $amount, 'disabled' => 0]);
    }


    /**
     * Update
     * @param int|null $cart_id
     * @param int $variant_id
     * @param array|object $purchase
     */
    public static function updatePurchase(int|null $cart_id, int $variant_id, array|object $purchase)
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
            $variant = ProductVariant::getVariant($variant_id);
            if (empty($variant) || ($variant->stock == 0 and !$variant->custom)) {
                return false;
            }


            $purchase->amount = max(1, $purchase->amount);

            // Не дадим больше чем на складе, если не под заказ
            if (empty($variant->custom)) {
                $purchase->amount = min($purchase->amount, $variant->stock);
            }
        }

        return self::query()
            ->where('cart_id', $cart_id)
            ->where('variant_id', $variant_id)
            ->update((array)$purchase);
    }


    /**
     * Удаление товара из корзины
     * @param int $cart_id
     * @param int $variant_id
     */
    public static function deletePurchase(?int $cart_id = null, ?int $variant_id = null)
    {
        // Get cart
        if (is_null($cart_id)) {
            if (empty($cart = Cart::getCart())) {
                return false;
            }

            $cart_id = $cart->id;
        }

        $query = self::query();

        if (!empty($variant_id)) {
            $query->where('variant_id', $variant_id);
        }

        if (!empty($cart_id)) {
            $query->where('cart_id', $cart_id);
        }

        return $query->delete();
    }
}
