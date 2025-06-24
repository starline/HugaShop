<?php

/**
 * HugaShop - Sell anything
 *
 * Products filling percent per language
 */

namespace HugaShop\Extensions\Productsfilling\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class Productsfilling extends BaseExtensionModel
{
    public $timestamps = true;
    public const CREATED_AT = 'updated';
    public const UPDATED_AT = 'updated';

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'product_id' =>     ['type' => 'int'],
        'language_code' =>  ['type' => 'varchar'],
        'percent' =>        ['type' => 'int',      'def' => 0],
        'updated' =>        ['type' => 'datetime'],
    ];
}
