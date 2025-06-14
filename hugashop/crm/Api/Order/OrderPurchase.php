<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 * Обновляем Оптовую цену в покупках
 * UPDATE s_order_purchase SET cost_price = (SELECT cost_price FROM __product_variant WHERE id = s_order_purchase.variant_id)
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\Helper;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Database;
use HugaShop\Api\DatabaseQuery;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Product\ProductCategory;

class OrderPurchase extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                     ['type' => 'int',                           'extra' => 'AUTO_INCREMENT'],
            'order_id' =>               ['type' => 'int'],
            'product_id' =>             ['type' => 'int'],
            'variant_id' =>             ['type' => 'int'],
            'sku' =>                    ['type' => 'varchar',                       'def' => ''],
            'product_name' =>           ['type' => 'varchar',                       'def' => ''],
            'variant_name' =>           ['type' => 'varchar',                       'def' => ''],
            'price' =>                  ['type' => 'decimal', 'lenght' => 10.2,     'def' => 0.00],
            'cost_price' =>             ['type' => 'decimal', 'lenght' => 10.2,     'def' => 0.00],
            'amount' =>                 ['type' => 'int',                           'def' => 0],
            'position' =>               ['type' => 'int',                           'def' => 0]
        ],
        'join' => [
            Order::class => ['id' => 'order_id'],
            Product::class => ['id' => 'product_id'],
            ProductVariant::class => ['id' => 'variant_id']
        ]
    ];


    /**
     * Выбираем товары в заказе
     * @param array $filter
     * @param array $join = array('image', 'product', 'variant', 'category')
     */
    public static function getPurchases(array $filter = [], array $join = [])
    {

        $alias = self::getAlias();

        $WHERE = '';
        if (isset($filter['order_id'])) {
            if (!empty($filter['order_id'])) {
                $WHERE .= Database::placehold(" AND  `$alias`.order_id in(?@)", (array)$filter['order_id']);
            } else {
                return [];
            }
        }


        $SELECT = self::makeSelect();
        $JOIN = '';

        // JOIN IMAGE
        if (in_array("image", $join)) {
            $SELECT .= Database::placehold(", i.filename as image_filename");
            $JOIN .= Database::placehold(
                " LEFT JOIN 
                    __content_image i 
                ON 
                    i.entity_id=`$alias`.product_id AND 
                    i.entity_name='product' AND 
                    i.position=(SELECT MIN(position) FROM __content_image WHERE entity_id=`$alias`.product_id and entity_name='product')"
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
                ON 
                    p.id=`$alias`.product_id 
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
                pv.stock as variant_stock,
                pv.custom as variant_custom,
                pv.weight as variant_weight
                "
            );
            $JOIN .= Database::placehold(
                " 
                LEFT JOIN 
                    __product_variant pv 
                ON 
                    pv.id=`$alias`.variant_id
                "
            );
        }

        $query =
            "SELECT 
                $SELECT
            FROM 
                __order_purchase `$alias`
                $JOIN
            WHERE 
                1 
                $WHERE
            ORDER BY 
                `$alias`.position";

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
     * Обновляем покупки
     * @param int $id
     * @param $purchase
     */
    public static function updatePurchase(int $id, $purchase)
    {
        $old_purchase = self::getOne($id);

        if (empty($old_purchase)) {
            return false;
        }

        $order = Order::getOrder(intval($old_purchase->order_id));
        if (empty($order)) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($order->closed and !empty($purchase->amount) and isset($old_purchase->variant_id) and isset($purchase->variant_id)) {
            if ($old_purchase->variant_id != $purchase->variant_id) {

                if (!empty($old_purchase->variant_id)) {
                    $query = Database::placehold("UPDATE __product_variant SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount, $old_purchase->variant_id);
                    self::query($query);
                }

                if (!empty($purchase->variant_id)) {
                    $query = Database::placehold("UPDATE __product_variant SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $purchase->amount, $purchase->variant_id);
                    self::query($query);
                }
            } elseif (!empty($purchase->variant_id)) {
                $query = Database::placehold("UPDATE __product_variant SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount - $purchase->amount, $purchase->variant_id);
                self::query($query);
            }
        }

        // Обновляем товары заказа
        return self::update($purchase)->whereId($id)->get();
    }


    /**
     * Добавляем новый вариант товара в заказ
     * @return $purchase_id
     */
    public static function addPurchase(object|array $purchase)
    {

        $purchase = (object) $purchase;

        if (!empty($purchase->product_id)) {

            // Выбираем данные исходного варианта товара
            $variant = ProductVariant::getVariant($purchase->product_id);
            if (empty($variant)) {
                return false;
            }

            // Выбираем данные исходного товара
            $product = Product::getProduct(intval($variant->product_id));
            if (empty($product)) {
                return false;
            }
        }

        if (empty($order = Order::getOrder(intval($purchase->order_id)))) {
            return false;
        }

        if (empty($purchase->product_id) && !empty($variant->product_id)) {
            $purchase->product_id = $variant->product_id;
        }

        if (empty($purchase->product_name) && !empty($product->name)) {
            $purchase->product_name = $product->name;
        }

        if (empty($purchase->sku) && !empty($variant->sku)) {
            $purchase->sku = $variant->sku;
        }

        if (empty($purchase->variant_name) && !empty($variant->name)) {
            $purchase->variant_name = $variant->name;
        }

        if (!isset($purchase->price) && isset($variant->price)) {
            $purchase->price = $variant->price;
        }

        if (!isset($purchase->cost_price) && isset($variant->cost_price)) {
            $purchase->cost_price = $variant->cost_price;
        }

        $purchase->amount = $purchase->amount ?? 1;

        // Если заказ закрыт, нужно обновить склад при добавлении покупки
        if ($order->closed && !empty($purchase->amount) && !empty($variant->id)) {
            $s = -1;
            Product::updateStock($purchase->product_id, $s * $purchase->amount);
        }

        return self::insert($purchase)->get();
    }


    /**
     * Удаление товаров заказа
     * @param int $id
     */
    public static function deletePurchase(int $id)
    {
        $purchase = self::getOne($id);

        if (empty($purchase)) {
            return false;
        }

        $order = Order::getOrder(intval($purchase->order_id));
        if (!$order) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($order->closed && !empty($purchase->amount)) {
            Product::updateStock($purchase->product_id, $purchase->amount);
        }

        return self::delete()->whereId($id)->get();
    }
}
