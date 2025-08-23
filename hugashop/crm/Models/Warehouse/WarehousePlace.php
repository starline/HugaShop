<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Models\Warehouse;

use HugaShop\Models\BaseModel;

class WarehousePlace extends BaseModel
{

    protected $table = 'wh_place';
    public $timestamps = true;
    protected static $table_fields = [
        'id'       => ['type' => 'int',         'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar',     'req' => true],
        'enabled'  => ['type' => 'tinyint',     'def' => 0],
        'comment'  => ['type' => 'varchar'],
        'position' => ['type' => 'int',         'def' => 0],
    ];

    protected static $table_keys = [
        'position'      => ['column' => ['position', 'id'],    'type' => 'index']
    ];


    /**
     * Delete
     * @param int|array $id
     */
    public static function deleteById(int|array $id): int
    {

        // TODO: Удалить если нет товаров на складе. Иначе ошибка

        return self::deleteOne($id);
    }
}
