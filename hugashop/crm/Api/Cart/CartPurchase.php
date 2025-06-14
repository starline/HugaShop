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
use HugaShop\Api\Database;
use HugaShop\Api\DatabaseQuery;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Product\ProductCategory;

class CartPurchase extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
            'cart_id' =>            ['type' => 'int'],
            'product_id' =>         ['type' => 'int'],
            'variant_id' =>         ['type' => 'int'],
            'amount' =>             ['type' => 'int',           'def' => 0],
            'created' =>            ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
            'disabled' =>           ['type' => 'tinyint',       'def' => 0]
        ],
        'join' => [
            Cart::class => ['id' => 'cart_id'],
            Product::class => ['id' => 'product_id'],
            ProductVariant::class => ['id' => 'variant_id']
        ]
    ];


    /**
     * Get cart purchases
     * @param array $filter
     * @param array $join = ['image', 'product', 'variant', 'category']
     */
    public static function getCartPurchases(array $filter = [], array $join = [])
    {

        $alias = self::getAlias();
        $WHERE = '';
        if (isset($filter['cart_id'])) {
            if (!empty($filter['cart_id'])) {
                $WHERE .= Database::placehold(" AND `$alias`.cart_id in(?@)", (array)$filter['cart_id']);
            } else {
                return [];
            }
        }

        if (isset($filter['disabled'])) {
            $WHERE .= Database::placehold(" AND `$alias`.disabled=?", $filter['disabled']);
        }

        // JOIN IMAGE
        $SELECT = self::makeSelect();
        $JOIN = '';
        if (in_array("image", $join)) {
            $SELECT .= Database::placehold(", i.filename as image_filename");
            $JOIN .= Database::placehold(
                "
                LEFT JOIN 
                    __content_image i 
                ON 
                    i.entity_id=`$alias`.product_id AND 
                    i.entity_name='product' 
                    AND i.position=(SELECT MIN(position) FROM __content_image WHERE entity_id=`$alias`.product_id and entity_name='product')
                "
            );
        }

        // JOIN PRODUCT
        if (in_array("product", $join) || in_array("category", $join)) {
            $SELECT .= Database::placehold(
                ", 
                p.name as product_name,
                p.url as product_url
                "
            );
            $JOIN .= Database::placehold(
                "
                LEFT JOIN 
                    __product p 
                ON p.id=`$alias`.product_id 
                "
            );
        }


        // JOIN CATEGORY
        if (in_array("category", $join)) {
            $SELECT .= Database::placehold(
                ", 
                pc.name as category_name,
                pc.id as category_id,
                pc.url as category_url
                "
            );
            $JOIN .= Database::placehold(
                "
                LEFT JOIN
                    __product_category pc 
                ON  pc.id=p.category_id
                "
            );
        }


        // JOIN VARIANT
        if (in_array("variant", $join)) {
            $SELECT .= Database::placehold(
                ",
                pv.name as variant_name, 
                pv.sku as variant_sku, 
                pv.price as variant_price, 
                pv.cost_price as variant_cost_price, 
                pv.stock as variant_stock,
                pv.custom as variant_custom,
                pv.weight as variant_weight
                "
            );
            $JOIN .= Database::placehold(
                "
                 LEFT JOIN 
                    __product_variant pv 
                ON pv.id=`$alias`.variant_id
                "
            );
        }


        // Get cart product
        $query =
            "SELECT  
                $SELECT
            FROM 
                __cart_purchase `$alias`
                $JOIN
            WHERE 
                1 
                $WHERE
            ORDER BY 
                `$alias`.cart_id";

        $purchases = self::query($query)->results();
        $purchases_normalized = Helper::normalizeObjectData($purchases);

        // Get category data
        if (in_array("category", $join)) {
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

        $cart_product = new \stdClass();
        $cart_product->cart_id = $cart_id;
        $cart_product->product_id = $variant->product_id;
        $cart_product->variant_id = $variant->id;
        $cart_product->amount = $amount;
        $cart_product->created = date("Y-m-d H:i:s"); # current date

        // Add product variant to cart
        if ($new_purchase === true) {
            return CartPurchase::insert($cart_product)->getInsertId();
        }

        // Update exist
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

        return self::update($purchase)->where('cart_id=?', $cart_id)->where('variant_id=?', $variant_id)->get();
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

        $query = self::delete();

        if (!empty($variant_id)) {
            $query->where('variant_id=?', $variant_id);
        }

        if (!empty($cart_id)) {
            $query->where('cart_id=?', $cart_id);
        }

        return $query->get();
    }
}
