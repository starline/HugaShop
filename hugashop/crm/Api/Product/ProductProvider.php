<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;

class ProductProvider extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',       'req' => true],
        'description' =>            ['type' => 'varchar'],
        'no_restore_price' =>       ['type' => 'tinyint',       'def' => 0]
    ];
}
