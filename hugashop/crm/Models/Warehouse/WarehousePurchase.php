<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andri Huga
 * @version 2.9
 * 
 */

namespace HugaShop\Models\Warehouse;

use HugaShop\Models\BaseModel;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Warehouse\WarehouseProduct;
use Illuminate\Support\Collection;

class WarehousePurchase extends BaseModel
{
    protected $table = 'wh_purchase';

    protected static $table_fields = [
        'id'           => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'move_id'      => ['type' => 'int'],
        'product_id'   => ['type' => 'int'],
        'product_name' => ['type' => 'varchar'],
        'variant_name' => ['type' => 'varchar'],
        'sku'          => ['type' => 'varchar'],
        'price'        => ['type' => 'decimal',  'def'  => 0.00],
        'cost_price'   => ['type' => 'decimal',  'def'  => 0.00],
        'amount'       => ['type' => 'int',      'def'  => 0],
        'position'     => ['type' => 'int',      'def'  => 0],
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse_move()
    {
        return $this->belongsTo(WarehouseMove::class, 'move_id', 'id');
    }

    /**
     * Выбираем товары в поставке
     * @param array $filter
     * @param array $join = array('image', 'product')
     */
    public static function getPurchases(array $filter = [], array $join = []): Collection
    {
        $query = self::query();

        if (isset($filter['move_id'])) {
            $query->whereIn('move_id', (array) $filter['move_id']);
        }

        $with = [];
        if (in_array('product', $join)) {
            $with[] = 'product';
            $with[] = 'product.image';
        }
        if ($with) {
            $query->with($with);
        }

        $query->orderBy('position');
        return $query->get();
    }


    /**
     * Обновляем товары в поставке
     * @param int $id
     */
    public static function updatePurchase(int $id, array|object $purchase): int
    {
        $purchase = (object) $purchase;
        $old = self::find($id);
        if (!$old) {
            return 0;
        }

        $movement = WarehouseMove::getMovement($old->move_id);
        if (!$movement) {
            return 0;
        }

        // Если поставка закрыта, нужно обновить склад при изменении КОЛ-ВА закупки
        if ($movement->closed && !empty($purchase->amount)) {
            $factor = in_array($movement->status, [3, 4]) ? -1 : 1;

            // если сменили вариант товара
            if (!empty($purchase->product_id) && $old->product_id != $purchase->product_id) {

                // забираем со старого варианта
                if ($old->product_id) {
                    Product::changeAmount($old->product_id, -$factor * $old->amount);
                    WarehouseProduct::changeAmount($old->product_id, $movement->place_id, -$factor * $old->amount);
                }

                // добавляем в новый вариант
                Product::changeAmount($purchase->product_id, $factor * $purchase->amount);
                WarehouseProduct::changeAmount($purchase->product_id, $movement->place_id, $factor * $purchase->amount);

                // обновляем склад с новым значением поставки
            } elseif (!empty($purchase->product_id)) {
                Product::changeAmount($old->product_id, -$factor * ($old->amount - $purchase->amount));
                WarehouseProduct::changeAmount($old->product_id, $movement->place_id, -$factor * ($old->amount - $purchase->amount));
            }
        }

        // Обновляем товары поставки
        WarehousePurchase::updateOne($id, $purchase);
        return $id;
    }


    /**
     * Add purchase to move. Get datas from products such as (name, sku, price, cost_price)
     * @param $purchase
     */
    public static function addPurchase(array|object $purchase)
    {

        if (is_array($purchase)) {
            $purchase = (object) $purchase;
        }

        $product = Product::getOne((int) $purchase->product_id);
        if (!$product) {
            return 0;
        }

        $movement = WarehouseMove::find($purchase->move_id);
        if (!$movement) {
            return 0;
        }

        $purchase->product_id   = $product->id;
        $purchase->product_name = $product->name;
        $purchase->variant_name = $product->variant_name;
        $purchase->sku          = $product->sku;
        $purchase->price        = $purchase->price        ?? $product->price;
        $purchase->cost_price   = $purchase->cost_price   ?? $product->cost_price;
        $purchase->amount       = $purchase->amount       ?? 1;

        // Если заказ закрыт, нужно обновить склад при добавлении покупки
        if ($movement->closed && !empty($purchase->amount)) {
            $factor = in_array($movement->status, [3, 4]) ? -1 : 1;
            Product::changeAmount($product->id, $factor * $purchase->amount);
            WarehouseProduct::changeAmount($product->id, $movement->place_id, $factor * $purchase->amount);
        }

        return WarehousePurchase::createOne($purchase);
    }


    /**
     * Удаляем товар из поставки
     * @param int $id
     */
    public static function deletePurchase(int $id): bool
    {
        $purchase = self::find($id);
        if (!$purchase) {
            return false;
        }

        $movement = WarehouseMove::getMovement((int) $purchase->move_id);
        if (!$movement) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($movement->closed && !empty($purchase->amount)) {

            // Если списание, прибавляем на складе
            // Если поставка, отнимаем со склада
            $factor = in_array($movement->status, [3, 4]) ? 1 : -1;
            Product::changeAmount($purchase->product_id, $factor * $purchase->amount);
            WarehouseProduct::changeAmount($purchase->product_id, $movement->place_id, $factor * $purchase->amount);
        }

        return self::deleteOne($id) > 0;
    }


    /**
     * Get purchases for specific product with optional join
     */
    public static function getProductPurchases(array $filter = [], array $join = [], bool $count = false): Collection|int
    {
        $query = self::query();

        if (empty($filter['product_id'])) {
            return $count ? 0 : collect();
        }
        $query->whereIn('product_id', (array) $filter['product_id']);

        if (isset($filter['status'])) {
            $query->whereHas('warehouse_move', function ($q) use ($filter) {
                $q->where('status', $filter['status']);
            });
        }

        if ($count) {
            return $query->count();
        }

        $with = [];
        if (in_array('warehouse_move', $join) || in_array('warehouse_move.place', $join)) {
            $with[] = 'warehouse_move';
            if (in_array('warehouse_move.place', $join)) {
                $with[] = 'warehouse_move.place';
            }
        }
        if (in_array('product', $join)) {
            $with[] = 'product';
            $with[] = 'product.image';
        }
        if ($with) {
            $query->with($with);
        }

        $query->orderByDesc('id');

        if (isset($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int) $filter['limit']);
            $page = max(1, (int) ($filter['page'] ?? 1));
            $query->skip(($page - 1) * $limit)->take($limit);
        }

        return $query->get();
    }

    /**
     * Count product purchases
     */
    public static function countProductPurchases(array $filter = []): int
    {
        return self::getProductPurchases($filter, count: true);
    }
}
