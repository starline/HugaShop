<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andri Huga
 * @version 2.6
 * 
 */

namespace HugaShop\Api\Warehouse;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Product\ProductVariant;
use Illuminate\Support\Collection;

class WarehousePurchase extends BaseModel
{
    protected $table = 'wh_purchase';

    public static $table_fields = [
        'id'           => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'move_id'      => ['type' => 'int'],
        'product_id'   => ['type' => 'int'],
        'sku'          => ['type' => 'varchar'],
        'product_name' => ['type' => 'varchar'],
        'variant_name' => ['type' => 'varchar'],
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
        $purchases = $query->get();

        if (in_array('image', $join)) {
            foreach ($purchases as $purchase) {
                $purchase->image_filename = $purchase->product
                    ? $purchase->product->images()->where('entity_name', 'product')->orderBy('position')->value('filename')
                    : null;
            }
        }

        return $purchases;
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
            if (!empty($purchase->variant_id) && $old->variant_id != $purchase->variant_id) {

                // забираем со старого варианта
                if ($old->variant_id) {
                    ProductVariant::updateStock($old->variant_id, -$factor * $old->amount);
                }

                // добавляем в новый вариант
                ProductVariant::updateStock($purchase->variant_id, $factor * $purchase->amount);

                // обновляем склад с новым значением поставки
            } elseif (!empty($purchase->variant_id)) {
                ProductVariant::updateStock($old->variant_id, -$factor * ($old->amount - $purchase->amount));
            }
        }

        // Обновляем товары поставки
        self::where('id', $id)->update((array) $purchase);
        return $id;
    }


    /**
     * Добавляем товар в поставку
     * @param $purchase
     */
    public static function addPurchase(object $purchase): int
    {
        $variant = null;
        $product = null;
        if (!empty($purchase->variant_id)) {
            $variant = ProductVariant::find($purchase->variant_id);
            if (!$variant) {
                return 0;
            }
            $product = Product::find($variant->product_id);
            if (!$product) {
                return 0;
            }
        }

        $movement = WarehouseMove::find($purchase->move_id);
        if (!$movement) {
            return 0;
        }

        $purchase->product_id   = $purchase->product_id   ?? $variant?->product_id;
        $purchase->product_name = $purchase->product_name ?? $product?->name;
        $purchase->sku          = $purchase->sku          ?? $variant?->sku;
        $purchase->variant_name = $purchase->variant_name ?? $variant?->name;
        $purchase->price        = $purchase->price        ?? $variant?->price;
        $purchase->cost_price   = $purchase->cost_price   ?? $variant?->cost_price;
        $purchase->amount       = $purchase->amount       ?? 1;

        // Если заказ закрыт, нужно обновить склад при добавлении покупки
        if ($movement->closed && !empty($purchase->amount) && $variant?->id) {
            $factor = in_array($movement->status, [3, 4]) ? -1 : 1;
            ProductVariant::updateStock($variant->id, $factor * $purchase->amount);
        }

        return self::create($purchase)->id;
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
            ProductVariant::updateStock($purchase->variant_id, $factor * $purchase->amount);
        }

        return self::deleteOne($id) > 0;
    }


    /**
     * Выбираем ожидаемые поставки определенного товара
     * $status = 1
     * @param int|array|null $variand_id
     */
    public static function getProductMovements(int|array|null $variant_id): Collection
    {
        if (empty($variant_id)) {
            return collect();
        }

        return self::query()
            ->with(['warehouse_move'])
            ->whereIn('variant_id', (array) $variant_id)
            ->whereHas('warehouse_move', function ($q) {
                $q->where('status', 1);
            })
            ->orderBy('warehouse_move.awaiting_date')
            ->get();
    }
}
