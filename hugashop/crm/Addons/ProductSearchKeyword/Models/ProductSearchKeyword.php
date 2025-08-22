<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\ProductSearchKeyword\Models;

use HugaShop\Addons\BaseAddonModel;

final class ProductSearchKeyword extends BaseAddonModel
{
    protected static $table_fields = [
        'id'         => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'name'       => ['type' => 'varchar'],
        'created_at' => ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
    ];

    public static function addKeyword(string $keyword): void
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
