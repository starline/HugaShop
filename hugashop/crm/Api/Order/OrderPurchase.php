<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.6
 * 
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\BaseModel;
use Illuminate\Support\Collection;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Product\ProductCategory;

class OrderPurchase extends BaseModel
{
    public static $table_fields = [
        'id'           => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'order_id'     => ['type' => 'int'],
        'product_id'   => ['type' => 'int'],
        'sku'          => ['type' => 'varchar', 'def'  => ''],
        'product_name' => ['type' => 'varchar', 'def'  => ''],
        'variant_name' => ['type' => 'varchar', 'def'  => ''],
        'price'        => ['type' => 'decimal', 'def'  => 0.00],
        'cost_price'   => ['type' => 'decimal', 'def'  => 0.00],
        'amount'       => ['type' => 'int',     'def'  => 0],
        'position'     => ['type' => 'int',     'def'  => 0],
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    /**
     * Выбираем товары в заказе
     *
     * @param array $filter
     * @param array $join
     */
    public static function getPurchases(array $filter = [], array $join = []): Collection
    {
        $query = self::query();

        if (isset($filter['order_id'])) {
            if (!empty($filter['order_id'])) {
                $query->whereIn('order_id', (array) $filter['order_id']);
            } else {
                return collect();
            }
        }

        $with = [];
        if (in_array('product', $join) || in_array('product.category', $join) || in_array('product.image', $join)) {
            $with[] = 'product';
            if (in_array('category', $join)) $with[] = 'product.image';
            if (in_array('category', $join)) $with[] = 'product.category';
        }
        if (in_array('order', $join)) {
            $with[] = 'order';
        }
        if (!empty($with)) {
            $query->with($with);
        }

        $query->orderBy('position');
        $purchases = $query->get();

        if (in_array('image', $join)) {
            foreach ($purchases as $purchase) {
                $purchase->image_filename = $purchase->product
                    ? $purchase->product->images()->where('entity_name', 'product')->orderBy('position')->value('filename')
                    : null;
            }
        }

        if (in_array('category', $join)) {
            foreach ($purchases as $purchase) {
                $categoryId = $purchase->product->category_id ?? null;
                if (!empty($categoryId)) {
                    $purchase->category = ProductCategory::getCategoryById($categoryId);
                }
            }
        }

        return $purchases;
    }


    /**
     * Обновляем покупки
     */
    public static function updatePurchase(int $id, array|object $purchase): int
    {
        $purchase = (object) $purchase;
        $old = self::find($id);
        if (!$old) {
            return 0;
        }

        $order = Order::getOrder((int) $old->order_id);
        if (!$order) {
            return 0;
        }

        if ($order->closed && !empty($purchase->amount) && isset($old->product_id) && isset($purchase->product_id)) {
            if ($old->product_id != $purchase->product_id) {
                if (!empty($old->product_id)) {
                    Product::where('id', $old->product_id)->whereNotNull('stock')->increment('stock', $old->amount);
                }
                if (!empty($purchase->product_id)) {
                    Product::where('id', $purchase->product_id)->whereNotNull('stock')->decrement('stock', $purchase->amount);
                }
            } elseif (!empty($purchase->product_id)) {
                Product::where('id', $purchase->product_id)->whereNotNull('stock')->increment('stock', $old->amount - $purchase->amount);
            }
        }

        self::updateOne($id, $purchase);
        return $id;
    }

    /**
     * Добавляем новый вариант товара в заказ
     */
    public static function addPurchase(array|object $purchase)
    {
        $purchase = (object) $purchase;

        $product = Product::getProduct((int) $purchase->product_id);
        if (empty($product)) {
            return 0;
        }

        $order = Order::getOrder((int) $purchase->order_id);
        if (!$order) {
            return 0;
        }

        $purchase->product_id   = $product->id;
        $purchase->product_name = $product->name;
        $purchase->sku          = $product->sku;
        $purchase->variant_name = $product->variant_name;
        $purchase->price        = $purchase->price        ?? $product->price;
        $purchase->cost_price   = $purchase->cost_price   ?? $product->cost_price;
        $purchase->amount       = $purchase->amount       ?? 1;

        if ($order->closed && !empty($purchase->amount) && !empty($purchase->product_id)) {
            Product::updateStock($purchase->product_id, -$purchase->amount);
        }

        return static::create($purchase);
    }

    /**
     * Удаление товаров заказа
     */
    public static function deletePurchase(int $id): bool
    {
        $purchase = self::find($id);
        if (!$purchase) {
            return false;
        }

        $order = Order::getOrder((int) $purchase->order_id);
        if (!$order) {
            return false;
        }

        if ($order->closed && !empty($purchase->amount)) {
            Product::updateStock($purchase->product_id, $purchase->amount);
        }

        return self::deleteOne($id) > 0;
    }
}
