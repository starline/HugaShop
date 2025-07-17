<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Models;

use HugaShop\Models\BaseModel;

class ProductSearchKeyword extends BaseModel
{
    protected static $table_fields = [
        'id' =>         ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'name' =>       ['type' => 'varchar'],
        'created_at' => ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
    ];

    public static function logKeyword(string $keyword): void
    {
        if (trim($keyword) === '') {
            return;
        }
        self::createOne([
            'name' => trim($keyword),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
