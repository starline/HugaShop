<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;

class ProductTranslation extends BaseModel
{

    public static $table_fields = [
        'id' =>                 ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'product_id' =>         ['type' => 'int'],
        'language_code' =>      ['type' => 'varchar'],
        'name' =>               ['type' => 'varchar'],
        'meta_title' =>         ['type' => 'varchar'],
        'meta_description' =>   ['type' => 'varchar'],
        'annotation' =>         ['type' => 'varchar'],
        'body' =>               ['type' => 'text'],
        'variant_name' =>       ['type' => 'varchar']
    ];

    public static $table_keys = [
        'unique_translation' => ['product_id', 'language_code']
    ];
}
