<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 * Работаем со складом, закупками, поставками, списанием
 *
 */

namespace HugaShop\Models\Warehouse;

use HugaShop\Models\Image;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\User\User;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Warehouse\WarehousePlaceProduct;
use Illuminate\Support\Collection;
use HugaShop\Models\Finance\FinancePayment;
use HugaShop\Models\Finance\FinancePaymentContractor;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WarehouseMove extends BaseModel
{

    protected $table = 'wh_move';
    protected static $table_fields = [
        'id'            => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'date'          => ['type' => 'datetime', 'def'   => 'CURRENT_TIMESTAMP', 'access' => false],
        'modified'      => ['type' => 'datetime', 'access' => false],
        'place_id'      => ['type' => 'int',      'access' => ['warehouse_add', 'warehouse_edit']],
        'awaiting_date' => ['type' => 'date',     'access' => ['warehouse_add', 'warehouse_edit']],
        'manager_id'    => ['type' => 'int',      'access' => false],
        'note'          => ['type' => 'varchar'],
        'note_logist'   => ['type' => 'varchar',  'access' => ['warehouse_add', 'warehouse_edit']],
        'status'        => ['type' => 'tinyint',  'def' => 0, 'access' => false],
        'closed'        => ['type' => 'tinyint',  'def' => 0, 'access' => false],
    ];

    protected static $table_indexes = [
        'user_id' => ['column' => ['status'], 'type' => 'index']
    ];

    public function purchases()
    {
        return $this->hasMany(WarehousePurchase::class, 'move_id')->orderBy('position');
    }

    public function place()
    {
        return $this->belongsTo(WarehousePlace::class, 'place_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', 'warehouse')
            ->orderBy('position');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancePayment::class,
            FinancePaymentContractor::class,
            'entity_id',
            'payment_id'
        )->wherePivot('entity_name', 'wh_movement');
    }

    /**
     * Выбрать список поставок
     * join: purchases.product.image |images
     * @param array $filter
     * @param $count
     */
    public static function getMovements(array $filter = [], array $join = [], bool $count = false): Collection|int
    {
        $query = self::query();

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        } else {
            $query->whereNotIn('status', [3, 4]);
        }

        if (isset($filter['id'])) {
            $query->whereIn('id', (array) $filter['id']);
        }

        if (isset($filter['modified_since'])) {
            $query->where('modified', '>', $filter['modified_since']);
        }

        if (isset($filter['product_id'])) {
            $query->whereHas('purchases', function ($q) use ($filter) {
                $q->whereIn('product_id', (array) $filter['product_id']);
            });
        }

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('note', 'like', "%$word%")
                        ->orWhere('id', 'like', "%$word%");
                });
            }
        }

        // Выбираем кол-во
        if ($count) {
            return $query->count();
        }

        if (!empty($join)) {
            $query->with($join);
        }

        $query->orderBy('id', 'desc');

        if (isset($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int) $filter['limit']);
            $page  = max(1, (int) ($filter['page'] ?? 1));
            $query->skip(($page - 1) * $limit)->take($limit);
        }

        return $query->get()->keyBy('id');
    }


    /**
     * Выбираем кол-во поставок
     * @param array $filter
     */
    public static function countMovements(array $filter = []): int
    {
        return self::getMovements($filter, count: true);
    }


    /**
     * Подсчитываем общую себестоимость, розничную стоимость и количество
     * товаров в выбранных перемещениях
     * @param array $filter
     * @return object
     */
    public static function getMovementsTotals(array $filter = []): object
    {
        $query = self::query();

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['product_id'])) {
            $query->whereHas('purchases', function ($q) use ($filter) {
                $q->whereIn('product_id', (array) $filter['product_id']);
            });
        }

        $movements = $query->with('purchases')->get();

        $totals = [
            'cost_price'     => 0.0,
            'retail_price'   => 0.0,
            'product_amount' => 0,
        ];

        foreach ($movements as $move) {
            foreach ($move->purchases as $purchase) {
                $totals['cost_price']       += $purchase->cost_price * $purchase->amount;
                $totals['retail_price']     += $purchase->price * $purchase->amount;
                $totals['product_amount']   += $purchase->amount;
            }
        }

        return (object) $totals;
    }


    /**
     * Выбрать определенную поставку.
     * join: payments.category | purchases.product.image | purchases.warehouse_move |images
     * @param int $id
     */
    public static function getMovement(int $id, $join = []): ?self
    {
        $query = self::query();

        if (!empty($join)) {
            $query->with($join);
        }

        return $query->find($id);
    }


    /**
     * Обновление поставки
     * @param int $id
     * @param array|object $movement
     */
    public static function updateMove(int|array $id, array|object $movement): int
    {
        if (empty($movement)) {
            return 0;
        }

        $movement = (object) $movement;
        if (!empty($movement->awaiting_date)) {
            $movement->awaiting_date = Helper::dateConvert($movement->awaiting_date . ' 12:00', 'Y-m-d');
        }

        $current = self::find(is_array($id) ? $id['id'] : $id);
        if ($current && in_array($current->status, [2, 3]) && isset($movement->place_id)) {
            unset($movement->place_id);
        }

        $movement->modified = date('Y-m-d H:i:s');

        return self::updateOne($id, $movement);
    }


    /**
     * Удаляем поставку
     * @param int $id
     */
    public static function deleteMovement(int $id): bool
    {
        $move = WarehouseMove::getMovement($id);
        if ($move->status == 4) { // Отменен
            WarehousePurchase::where('move_id', $id)->delete();
            return self::deleteOne($id);
        }
        return false;
    }


    /**
     * Добавляем поставку
     * @param $movement
     */
    public static function addMovement(object $movement)
    {
        if (empty($movement->date)) {
            $movement->date = date('Y-m-d H:i:s');
        }

        if (!empty($movement->awaiting_date)) {
            $movement->awaiting_date = Helper::dateConvert($movement->awaiting_date . ' 12:00', 'Y-m-d');
        }

        // Закрепляем за менеджером
        $movement->manager_id = User::authUser('id');

        return self::createOne($movement);
    }


    /**
     * Фиксируем поставку/списание (выполнен)
     * $subtract (вычесть) = true - при списании
     * @param int $move_id
     * @param $subtract
     */
    public static function close(int $move_id, bool $subtract = false)
    {
        $movement = self::find($move_id);
        if (!$movement) {
            return false;
        }

        // Если списание/поставка товаров
        $factor = $subtract ? -1 : 1;

        if (!$movement->closed) {
            foreach (WarehousePurchase::getList(['move_id' => $movement->id]) as $purchase) {
                if ($purchase->amount) {
                    Product::changeAmount($purchase->product_id, $factor * $purchase->amount);
                    WarehousePlaceProduct::changeAmount($purchase->product_id, $movement->place_id, $factor * $purchase->amount);
                }
            }
            $movement->update(['closed' => 1]);
        }

        return $movement->id;
    }


    /**
     * Переводим поставку в открытый (новый, ожидаем)
     * @param int $move_id
     */
    public static function open(int $move_id)
    {
        $movement = self::find($move_id);
        if (!$movement) {
            return false;
        }

        // Если поставка был списан(3)|отменен(4), меняем знак
        $factor = in_array($movement->status, [3, 4]) ? -1 : 1;

        // Если поставка была как "выполнен/closed", отнимаем || добавляем товар на склад
        if ($movement->closed) {
            foreach (WarehousePurchase::getList(['move_id' => $movement->id]) as $purchase) {
                if ($purchase->amount) {
                    Product::changeAmount($purchase->product_id, - ($factor) * $purchase->amount);
                    WarehousePlaceProduct::changeAmount($purchase->product_id, $movement->place_id, - ($factor) * $purchase->amount);
                }
            }
            $movement->update(['closed' => 0]);
        }

        return $movement->id;
    }
}
