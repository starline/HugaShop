<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Api\Warehouse;

use HugaShop\Api\BaseModel;

class WarehousePlace extends BaseModel
{
    protected $table = 'wh_place';

    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'req' => true],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
        'comment'  => ['type' => 'varchar'],
        'date'     => ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
        'position' => ['type' => 'int',     'def' => 0],
    ];


    /**
     * Delete
     * @param int|array $id
     */
    public static function deleteById(int|array $id): int
    {

        // TODO: Удалить если нет товаров на складе. Иначе ошибка

        return self::whereId($id)->delete();
    }
}
