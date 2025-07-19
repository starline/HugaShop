<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\ProductSearchKeyword\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class ProductSearchKeyword extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'         => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'name'       => ['type' => 'varchar'],
        'created_at' => ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
    ];

    public static function logKeyword(string $keyword): void
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return;
        }
        self::createOne([
            'name'       => $keyword,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
