<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 * Работаем со складом, закупками, поставками, списанием
 *
 */

namespace HugaShop\Api\Warehouse;

use HugaShop\Api\Image;
use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use HugaShop\Api\User\User;
use HugaShop\Api\Product\Product;
use Illuminate\Support\Collection;
use HugaShop\Api\Product\ProductVariant;

class WarehouseMove extends BaseModel
{
    protected $table = 'wh_move';

    public static $table_fields = [
        'id'            => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'date'          => ['type' => 'datetime', 'def'   => 'CURRENT_TIMESTAMP', 'access' => false],
        'modified'      => ['type' => 'datetime', 'access' => false],
        'place_id'      => ['type' => 'int',      'access' => ['warehouse_add', 'warehouse_edit'], 'req' => true],
        'awaiting_date' => ['type' => 'date',     'access' => ['warehouse_add', 'warehouse_edit']],
        'manager_id'    => ['type' => 'int',      'access' => false],
        'note'          => ['type' => 'varchar'],
        'note_logist'   => ['type' => 'varchar',  'access' => ['warehouse_add', 'warehouse_edit']],
        'status'        => ['type' => 'tinyint',  'def' => 0, 'access' => false],
        'closed'        => ['type' => 'tinyint',  'def' => 0, 'access' => false],
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

    /**
     * Выбрать список поставок
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

        if (in_array('images', $join)) {
            $with[] = 'images';
        }
        if (in_array('purchases', $join)) {
            $with[] = 'purchases';
            $with[] = 'purchases.product';
            $with[] = 'purchases.product.image'; # добавляем фото товара
        }

        if (!empty($with)) {
            $query->with($with);
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
     * Выбрать определенную поставку
     * @param int $id
     */
    public static function getMovement(int $id, $join = []): ?self
    {
        $query = self::query();

        if (in_array('images', $join)) {
            $with[] = 'images';
        }
        if (in_array('purchases', $join)) {
            $with[] = 'purchases';
            $with[] = 'purchases.product';
            $with[] = 'purchases.product.image';
        }

        if (!empty($with)) {
            $query->with($with);
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
        $movement->modified = date('Y-m-d H:i:s');

        return self::updateOne($id, $movement);
    }


    /**
     * Удаляем поставку
     * @param int $id
     */
    public static function deleteMovement(int $id): bool
    {
        WarehousePurchase::where('move_id', $id)->delete();
        return self::deleteOne($id) > 0;
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

        return self::create($movement);
    }


    /**
     * Фиксируем поставку/списание (выполнен)
     * $subtract (вычесть)  = true - при списании
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
            foreach (WarehousePurchase::where('move_id', $movement->id)->get() as $purchase) {
                if ($purchase->amount) {
                    Product::updateStock($purchase->product_id, $factor * $purchase->amount);
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

        // Если заказ был списан(3)|отменен(4), меняем знак
        $factor = in_array($movement->status, [3, 4]) ? -1 : 1;

        // Если заказ был как "выполнен/closed", отнимаем||добавляем товар на склад
        if ($movement->closed) {
            foreach (WarehousePurchase::where('move_id', $movement->id)->get() as $purchase) {
                if ($purchase->amount) {
                    Product::updateStock($purchase->prodyct_id, -$factor * $purchase->amount);
                }
            }
            $movement->update(['closed' => 0]);
        }

        return $movement->id;
    }
}
